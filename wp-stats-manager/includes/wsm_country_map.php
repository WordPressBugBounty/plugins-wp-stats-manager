<?php
/**
 * "Visitors by Country" map (Traffic > Summary): countries shaded by their number of visitors, a ranked list
 * and a range switcher (Today / 7 days / 30 days / All time / Online now).
 * Borders come from js/wsm-world.js (Natural Earth, no tile server), drawn with Leaflet (js/leaflet.js).
 * Same component as Visitor Statistics Pro.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array key => array('label', 'days', 'cmp')
 */
function wsmCountryMapRanges()
{
    return array(
        'today' => array('label' => __('Today', 'wp-stats-manager'), 'days' => 1, 'cmp' => __('vs. the same time yesterday', 'wp-stats-manager')),
        '7' => array('label' => __('7 days', 'wp-stats-manager'), 'days' => 7, 'cmp' => __('vs. the previous 7 days', 'wp-stats-manager')),
        '30' => array('label' => __('30 days', 'wp-stats-manager'), 'days' => 30, 'cmp' => __('vs. the previous 30 days', 'wp-stats-manager')),
        'all' => array('label' => __('All time', 'wp-stats-manager'), 'days' => 0, 'cmp' => ''),
        'online' => array('label' => __('Online now', 'wp-stats-manager'), 'days' => 0, 'cmp' => ''),
    );
}

/**
 * Illustrative SAMPLE data of the locked map (a Pro feature in this version), per range. Never the site's
 * real figures: the blur over the map can be removed in the browser.
 *
 * @return array range => {range, cmp, total, prev_total, countries: [{c, n, v, p, la, lo}]}
 */
function wsmCountryMapSampleData()
{
    $base = array(
        array('US', 'United States', 412, 37.1, -95.7),
        array('GB', 'United Kingdom', 268, 54.0, -2.0),
        array('DE', 'Germany', 231, 51.2, 10.4),
        array('IN', 'India', 197, 21.0, 78.0),
        array('FR', 'France', 146, 46.6, 2.2),
        array('CA', 'Canada', 121, 56.1, -106.3),
        array('BR', 'Brazil', 98, -14.2, -51.9),
        array('AU', 'Australia', 74, -25.3, 133.8),
        array('EG', 'Egypt', 52, 26.8, 30.8),
        array('JP', 'Japan', 41, 36.2, 138.3),
    );
    $scale = array('today' => 0.08, '7' => 0.55, '30' => 2.2, 'all' => 9.5, 'online' => 0.012);
    $ranges = wsmCountryMapRanges();
    $out = array();
    foreach ($scale as $range => $factor) {
        $countries = array();
        $total = 0;
        $prevTotal = 0;
        $hasPrev = $range !== 'all' && $range !== 'online';
        foreach ($base as $i => $row) {
            $v = max(1, (int) round($row[2] * $factor));
            $p = max(0, (int) round($v * (0.7 + ($i % 4) * 0.15)));
            $total += $v;
            $prevTotal += $p;
            $countries[] = array('c' => $row[0], 'n' => $row[1], 'v' => $v, 'p' => $hasPrev ? $p : null, 'la' => $row[3], 'lo' => $row[4]);
        }
        $out[$range] = array('range' => $range, 'cmp' => $ranges[$range]['cmp'], 'total' => $total, 'prev_total' => $hasPrev ? $prevTotal : null, 'countries' => $countries);
    }
    return $out;
}

/**
 * A date-time of the site's timezone converted to UTC, the timezone of the log tables. Filtering the raw
 * column with it (instead of CONVERT_TZ(column) >= ...) lets MySQL use the time indexes.
 */
function wsmCountryMapLocalToUtc($localDateTime)
{
    try {
        try {
            $zone = new DateTimeZone(wsmGetTimezoneString());
        } catch (Exception $e) {
            $zone = new DateTimeZone(WSM_TIMEZONE);
        }
        $date = new DateTime($localDateTime, $zone);
        $date->setTimezone(new DateTimeZone('UTC'));
        return $date->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        return $localDateTime;
    }
}

/**
 * Visitors per country for a range, with the previous period for comparison. A visitor is one row of the
 * visit table (one per visitor per day), the unit of every "Visitors" number of the plugin. Visits whose
 * country is unknown are not counted. Cached for a minute (30 seconds for "Online now").
 *
 * @return array {range, cmp, total, prev_total, countries: [{c, n, v, p, la, lo}]} sorted by visitors.
 */
function wsmGetCountryMapData($range)
{
    global $wpdb;
    $ranges = wsmCountryMapRanges();
    $range = isset($ranges[$range]) ? (string) $range : 'today';
    $cacheKey = WSM_PREFIX . '_cmap_' . $range;
    $cached = get_transient($cacheKey);
    if (is_array($cached)) {
        return $cached;
    }

    // Same table prefix as wsmDatabase, which writes the visit log.
    $p = $wpdb->base_prefix . WSM_PREFIX;
    $counts = array(); // countryId => array(current, previous)
    $coords = array(); // countryId => array(lat, lng), for the dots of very small countries
    $hasPrev = false;
    $coordsSql = 'AVG(NULLIF(latitude, 0)) AS la, AVG(NULLIF(longitude, 0)) AS lo';

    if ($range === 'online') {
        $since = wsmCountryMapLocalToUtc(wsmGetDateByInterval('-' . WSM_ONLINE_SESSION . ' minutes'));
        $rows = $wpdb->get_results($wpdb->prepare("SELECT countryId AS id, COUNT(*) AS cur, {$coordsSql} FROM {$p}_logUniqueVisit WHERE visitLastActionTime >= %s AND countryId > 0 GROUP BY countryId", $since), ARRAY_A);
        foreach ((array) $rows as $row) {
            $counts[(int) $row['id']] = array((int) $row['cur'], 0);
            $coords[(int) $row['id']] = array((float) $row['la'], (float) $row['lo']);
        }
    } elseif ($range === 'all') {
        // Days already archived: the daily per-country report (the raw log may have been purged);
        // the days after it: the visit log. Without archived days this is the whole visit log.
        $liveFromUtc = '';
        $lastReported = $wpdb->get_var("SELECT MAX(date) FROM {$p}_datewise_report WHERE country > 0");
        if ($lastReported && wsmValidateDateTime($lastReported)) {
            $rows = $wpdb->get_results($wpdb->prepare("SELECT country AS id, SUM(total_visitors) AS cur FROM {$p}_datewise_report WHERE country > 0 AND date <= %s GROUP BY country", $lastReported), ARRAY_A);
            foreach ((array) $rows as $row) {
                $counts[(int) $row['id']] = array((int) $row['cur'], 0);
            }
            $liveFromUtc = wsmCountryMapLocalToUtc(date('Y-m-d 00:00:00', strtotime($lastReported . ' +1 day')));
        }
        $sql = "SELECT countryId AS id, COUNT(*) AS cur FROM {$p}_logUniqueVisit WHERE countryId > 0";
        $sql = $liveFromUtc !== '' ? $wpdb->prepare($sql . ' AND firstActionVisitTime >= %s GROUP BY countryId', $liveFromUtc) : $sql . ' GROUP BY countryId';
        foreach ((array) $wpdb->get_results($sql, ARRAY_A) as $row) {
            $id = (int) $row['id'];
            $counts[$id] = array((isset($counts[$id]) ? $counts[$id][0] : 0) + (int) $row['cur'], 0);
        }
        $rows = $wpdb->get_results($wpdb->prepare("SELECT countryId AS id, {$coordsSql} FROM {$p}_logUniqueVisit WHERE firstActionVisitTime >= %s AND countryId > 0 GROUP BY countryId", wsmCountryMapLocalToUtc(wsmGetDateByInterval('-30 days', 'Y-m-d 00:00:00'))), ARRAY_A);
        foreach ((array) $rows as $row) {
            $coords[(int) $row['id']] = array((float) $row['la'], (float) $row['lo']);
        }
    } else {
        // Current period: from local midnight (days - 1) days ago until now. Previous period: the same span
        // shifted back by "days" (so "today so far" is compared with yesterday up to the same time).
        $days = (int) $ranges[$range]['days'];
        $today = wsmGetCurrentDateByTimeZone('Y-m-d');
        $nowLocal = wsmGetCurrentDateByTimeZone('Y-m-d H:i:s');
        $fromLocal = date('Y-m-d 00:00:00', strtotime($today . ' -' . ($days - 1) . ' days'));
        $prevFromLocal = date('Y-m-d H:i:s', strtotime($fromLocal . ' -' . $days . ' days'));
        $prevToLocal = date('Y-m-d H:i:s', strtotime($nowLocal . ' -' . $days . ' days'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT countryId AS id, SUM(firstActionVisitTime >= %s) AS cur, SUM(firstActionVisitTime < %s) AS prev, {$coordsSql}
             FROM {$p}_logUniqueVisit WHERE firstActionVisitTime >= %s AND countryId > 0 GROUP BY countryId",
            wsmCountryMapLocalToUtc($fromLocal),
            wsmCountryMapLocalToUtc($prevToLocal),
            wsmCountryMapLocalToUtc($prevFromLocal)
        ), ARRAY_A);
        foreach ((array) $rows as $row) {
            $counts[(int) $row['id']] = array((int) $row['cur'], (int) $row['prev']);
            $coords[(int) $row['id']] = array((float) $row['la'], (float) $row['lo']);
        }
        $hasPrev = true;
    }

    $countries = array();
    $total = 0;
    $prevTotal = 0;
    if ($counts) {
        $ids = implode(',', array_map('intval', array_keys($counts)));
        $info = $wpdb->get_results("SELECT id, name, alpha2Code FROM {$p}_countries WHERE id IN ({$ids})", ARRAY_A);
        foreach ((array) $info as $country) {
            list($current, $previous) = $counts[(int) $country['id']];
            $prevTotal += $previous;
            if ($current <= 0) {
                continue;
            }
            $total += $current;
            $coord = isset($coords[(int) $country['id']]) ? $coords[(int) $country['id']] : array(0, 0);
            $countries[] = array(
                'c' => strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $country['alpha2Code'])),
                // The seeded names use HTML entities (e.g. "&Aring;land Islands"); the script escapes plain text.
                'n' => wp_strip_all_tags(html_entity_decode((string) $country['name'], ENT_QUOTES, 'UTF-8')),
                'v' => $current,
                'p' => $hasPrev ? $previous : null,
                'la' => round($coord[0], 3),
                'lo' => round($coord[1], 3),
            );
        }
        usort($countries, 'wsmCompareCountryMapRows');
    }

    $data = array(
        'range' => $range,
        'cmp' => $ranges[$range]['cmp'],
        'total' => $total,
        'prev_total' => $hasPrev ? $prevTotal : null,
        'countries' => $countries,
    );
    set_transient($cacheKey, $data, $range === 'online' ? 30 : 60);
    return $data;
}

function wsmCompareCountryMapRows($a, $b)
{
    return $b['v'] - $a['v'];
}

/**
 * Registers the map scripts (Leaflet + borders + component). Called from wsm_commonScript() and, as a
 * fallback, when the map is rendered.
 */
function wsmRegisterCountryMapAssets()
{
    if (wp_script_is(WSM_PREFIX . '_country_map_js', 'registered')) {
        return;
    }
    wp_register_style(WSM_PREFIX . '_leaflet_css', WSM_URL . 'css/leaflet.css', array(), '1.4.0');
    wp_register_script(WSM_PREFIX . '_leaflet_js', WSM_URL . 'js/leaflet.js', array(), '1.4.0', true);
    // The borders file is 170 KB: only enqueued when the map is rendered.
    wp_register_script(WSM_PREFIX . '_world_js', WSM_URL . 'js/wsm-world.js', array(), '1.0.0', true);
    wp_register_script(WSM_PREFIX . '_country_map_js', WSM_URL . 'js/wsm-country-map.js', array('jquery', WSM_PREFIX . '_leaflet_js', WSM_PREFIX . '_world_js'), (string) @filemtime(WSM_DIR . 'js/wsm-country-map.js'), true);
}

/**
 * Styles of the component, printed once per page.
 */
function wsmCountryMapStyles()
{
    static $done = false;
    if ($done) {
        return '';
    }
    $done = true;
    return '<style>
.wsm-cmap{--cm-ink:#101828;--cm-ink2:#475467;--cm-ink3:#6b7486;--cm-sunken:#f4f6f9;--cm-accent:#2271b1;font-size:13px;color:var(--cm-ink);padding:4px 2px}
.wsm-cmap *{box-sizing:border-box}
.wsm-cmap-hd{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:10px 16px;margin:0 0 14px}
.wsm-cmap-seg{display:inline-flex;flex-wrap:wrap;padding:3px;border-radius:9px;background:var(--cm-sunken)}
.wsm-cmap-seg button{border:0;background:transparent;color:var(--cm-ink2);font-size:12px;font-weight:600;line-height:1.2;padding:6px 11px;border-radius:7px;cursor:pointer}
.wsm-cmap-seg button:hover{color:var(--cm-ink)}
.wsm-cmap-seg button[aria-pressed="true"]{background:#fff;color:var(--cm-ink);box-shadow:0 1px 3px rgba(16,24,40,.14)}
.wsm-cmap-seg button:focus{outline:none}.wsm-cmap-seg button:focus-visible{outline:2px solid var(--cm-accent);outline-offset:2px}
.wsm-cmap-live{display:inline-block;width:6px;height:6px;margin-right:5px;border-radius:50%;background:#12b76a;vertical-align:1px}
.wsm-cmap-body{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:18px;align-items:start}
.wsm-cmap-mapwrap{position:relative;border-radius:10px;overflow:hidden;background:#f3f6fa}
.wsm-cmap-map{width:100%;background:#f3f6fa}
.wsm-cmap .leaflet-container{font-family:inherit;background:#f3f6fa}
.wsm-cmap.is-loading .wsm-cmap-mapwrap,.wsm-cmap.is-loading .wsm-cmap-list{opacity:.45;transition:opacity .15s}
.wsm-cmap-scale{position:absolute;left:12px;bottom:12px;z-index:500;padding:7px 10px;border-radius:8px;background:rgba(255,255,255,.92);color:var(--cm-ink2);font-size:11px;pointer-events:none}
.wsm-cmap-scale .sw{display:flex;margin:4px 0 2px}.wsm-cmap-scale .sw i{width:26px;height:8px}
.wsm-cmap-scale .lb{display:flex;justify-content:space-between;font-variant-numeric:tabular-nums}
.leaflet-tooltip.wsm-cmap-tip{padding:8px 11px;border:0;border-radius:8px;background:#101828;color:#fff;font-size:12px;line-height:1.5;box-shadow:0 8px 20px rgba(16,24,40,.25)}
.leaflet-tooltip.wsm-cmap-tip:before{display:none}
.wsm-cmap-tip b{font-size:13px}.wsm-cmap-tip .up{color:#6ce9a6}.wsm-cmap-tip .down{color:#fda29b}.wsm-cmap-tip .flat{color:#d0d5dd}
.wsm-cmap-side h3{display:flex;justify-content:space-between;margin:0 0 10px;padding:0;color:var(--cm-ink3);font-size:11.5px;font-weight:600;letter-spacing:.04em;text-transform:uppercase}
.wsm-cmap-list{list-style:none;margin:0;padding:0;display:grid;gap:3px}
.wsm-cmap-list.is-all{max-height:420px;overflow-y:auto;padding-right:4px}
.wsm-cmap-list li{display:grid;grid-template-columns:20px minmax(0,1fr) auto 56px;grid-template-areas:"f n v c" "f bar bar p";align-items:center;column-gap:8px;row-gap:4px;margin:0;padding:7px 8px;border-radius:8px;cursor:default}
.wsm-cmap-list li:hover,.wsm-cmap-list li.is-on{background:#f0f6fc}
.wsm-cmap-list .f{grid-area:f;align-self:start;margin-top:3px;line-height:0}
.wsm-cmap-list .n{grid-area:n;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:500}
.wsm-cmap-list .v{grid-area:v;font-weight:700;font-variant-numeric:tabular-nums}
.wsm-cmap-list .c{grid-area:c;text-align:right;font-size:11.5px;font-weight:600;white-space:nowrap}
.wsm-cmap-list .bar{grid-area:bar;height:5px;border-radius:3px;background:#eef1f5;overflow:hidden}
.wsm-cmap-list .bar i{display:block;height:100%;border-radius:3px;background:var(--cm-accent)}
.wsm-cmap-list .p{grid-area:p;text-align:right;color:var(--cm-ink3);font-size:11.5px;font-variant-numeric:tabular-nums}
.wsm-cmap-list li.wsm-cmap-empty{display:block;padding:18px 8px;color:var(--cm-ink3);text-align:center}
.wsm-cmap .up{color:#067647}.wsm-cmap .down{color:#b42318}.wsm-cmap .flat{color:#98a2b3}.wsm-cmap .new{color:#2271b1}
.wsm-cmap-more{margin-top:8px;padding:0;border:0;background:none;color:var(--cm-accent);font-size:12px;font-weight:600;cursor:pointer}
.wsm-cmap-more[hidden]{display:none}
@media (max-width:900px){.wsm-cmap-body{grid-template-columns:minmax(0,1fr)}}
@media (max-width:600px){.wsm-cmap-scale{left:8px;bottom:8px;padding:4px 7px;font-size:10px}.wsm-cmap-scale .sw i{width:14px;height:6px}}
</style>';
}

/**
 * The map component (data of the first range embedded, other ranges loaded over AJAX).
 */
function wsmRenderCountryMap($range = 'today', $height = 430)
{
    $ranges = wsmCountryMapRanges();
    $range = isset($ranges[$range]) ? $range : 'today';
    wsmRegisterCountryMapAssets();
    wp_enqueue_style(WSM_PREFIX . '_leaflet_css');
    wp_enqueue_script(WSM_PREFIX . '_country_map_js');

    // Pro feature in this version: the map is drawn with illustrative SAMPLE data under a blur and an
    // upgrade overlay. No real figure is put in the page (the blur can be removed in the browser).
    $samples = wsmCountryMapSampleData();
    $config = array(
        'data' => $samples[$range],
        'samples' => $samples,
        'locked' => true,
        'ajax' => '',
        'blank' => WSM_URL . 'images/ICO_1px.gif',
        'i18n' => array(
            'visitors' => array(__('visitor', 'wp-stats-manager'), __('visitors', 'wp-stats-manager')),
            'countries' => array(__('country', 'wp-stats-manager'), __('countries', 'wp-stats-manager')),
            /* translators: 1: number of visitors, 2: number of countries */
            'sum' => array(__('%1$s visitor from %2$s', 'wp-stats-manager'), __('%1$s visitors from %2$s', 'wp-stats-manager')),
            /* translators: 1: number of visitors, 2: number of countries */
            'sumOnline' => array(__('%1$s visitor online now from %2$s', 'wp-stats-manager'), __('%1$s visitors online now from %2$s', 'wp-stats-manager')),
            'nonePeriod' => __('No visitors in this period yet', 'wp-stats-manager'),
            'noneOnline' => __('No visitors online right now', 'wp-stats-manager'),
            'noVisitors' => __('No visitors', 'wp-stats-manager'),
            'new' => __('New', 'wp-stats-manager'),
            /* translators: %d: number of countries */
            'viewAll' => __('View all %d countries', 'wp-stats-manager'),
            /* translators: %d: number of countries */
            'showTop' => __('Show top %d', 'wp-stats-manager'),
        ),
    );
    $height = max(200, min(900, (int) $height));

    $html = wsmCountryMapStyles();
    $html .= '<div class="wsm-cmap" data-config="' . esc_attr(wp_json_encode($config)) . '">';
    $html .= '<div class="wsm-cmap-hd">';
    $html .= '<div class="wsm-cmap-seg" role="group" aria-label="' . esc_attr__('Map range', 'wp-stats-manager') . '">';
    foreach ($ranges as $key => $item) {
        $html .= '<button type="button" data-range="' . esc_attr($key) . '" aria-pressed="' . ($key === $range ? 'true' : 'false') . '">' .
            ($key === 'online' ? '<span class="wsm-cmap-live" aria-hidden="true"></span>' : '') . esc_html($item['label']) . '</button>';
    }
    $html .= '</div></div>';
    $upgradeUrl = 'https://www.plugins-market.com/product/visitor-statistics-pro/';
    $lockIcon = '<span class="wsm-lock-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="11" width="14" height="9" rx="2" stroke="#fff" stroke-width="2"/><path d="M8 11V8a4 4 0 018 0v3" stroke="#fff" stroke-width="2"/></svg></span>';
    $html .= '<div class="wsm-locked wsm-cmap-locked">';
    $html .= '<div class="wsm-locked-overlay">' . $lockIcon;
    $html .= '<div class="wsm-cmap-lock-text" data-lock="map"><div class="wsm-locked-title">' . esc_html__('See where your visitors come from', 'wp-stats-manager') . '</div><div class="wsm-locked-sub">' . esc_html__('Pro shows your visitors on a world map by country, with a ranked list and the change against the previous period.', 'wp-stats-manager') . '</div></div>';
    $html .= '<div class="wsm-cmap-lock-text" data-lock="online" hidden><div class="wsm-locked-title">' . esc_html__('See who is on your site right now', 'wp-stats-manager') . '</div><div class="wsm-locked-sub">' . esc_html__('Pro shows the countries of the visitors online right now, updated every few seconds.', 'wp-stats-manager') . '</div></div>';
    $html .= '<a class="wsm-locked-btn" target="_blank" rel="noopener" href="' . esc_url($upgradeUrl) . '">' . esc_html__('Upgrade to PRO', 'wp-stats-manager') . '</a></div>';
    // Inline blur too, so the sample stays blurred even if the stylesheet is missing or stale.
    $html .= '<div class="wsm-cmap-body wsm-locked-data" aria-hidden="true" style="filter:blur(3px);pointer-events:none">';
    $html .= '<div class="wsm-cmap-mapwrap"><div class="wsm-cmap-map" style="height:' . $height . 'px" role="img" aria-label="' . esc_attr__('Map of visitors by country', 'wp-stats-manager') . '"></div>';
    $html .= '<div class="wsm-cmap-scale" aria-hidden="true">' . esc_html__('Visitors', 'wp-stats-manager') . '<div class="sw"><i style="background:#dbe8f6"></i><i style="background:#a9c8ea"></i><i style="background:#6fa3d8"></i><i style="background:#3a7cc0"></i><i style="background:#1d4f8a"></i></div><div class="lb"><span class="lo">1</span><span class="hi"></span></div></div></div>';
    $html .= '<div class="wsm-cmap-side"><h3><span>' . esc_html__('Top countries', 'wp-stats-manager') . '</span><span>' . esc_html__('Visitors', 'wp-stats-manager') . '</span></h3><ul class="wsm-cmap-list"></ul><button type="button" class="wsm-cmap-more" hidden></button></div>';
    $html .= '</div></div></div>';
    return $html;
}
