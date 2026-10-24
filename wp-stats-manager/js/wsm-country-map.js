/* "Visitors by Country" map: countries shaded by visitor count + a ranked list.
 * Markup and data come from wsmRenderCountryMap() (includes/wsm_country_map.php);
 * borders from window.WSM_WORLD (js/wsm-world.js). No tile server is used. */
(function ($) {
	'use strict';

	var COLORS = ['#dbe8f6', '#a9c8ea', '#6fa3d8', '#3a7cc0', '#1d4f8a'];
	var EMPTY = '#e4e8ee';
	var LIST_SIZE = 5;

	function esc(v) {
		return String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}
	function fmt(n) { return (Math.round(Number(n) || 0)).toLocaleString(); }
	function tr(cfg, key, n) {
		var t = cfg.i18n && cfg.i18n[key];
		if (!t) return key;
		if (typeof t === 'object') t = n === 1 ? t[0] : t[1];
		return t;
	}

	// 5 shades on a square-root scale, so a few big countries do not wash out the rest.
	function shade(v, max) {
		if (!v) return EMPTY;
		if (max <= 1) return COLORS[4];
		return COLORS[Math.min(4, Math.max(0, Math.ceil(Math.sqrt(v / max) * 5) - 1))];
	}

	// Change vs. the previous period, as {cls, text} (null when not comparable).
	function change(cfg, c) {
		if (c.p === null || c.p === undefined) return null;
		if (!c.p) return c.v ? { cls: 'new', text: tr(cfg, 'new') } : null;
		var pct = Math.round((c.v - c.p) / c.p * 100);
		if (pct === 0) return { cls: 'flat', text: '0%' };
		return { cls: pct > 0 ? 'up' : 'down', text: (pct > 0 ? '▲ ' : '▼ ') + Math.abs(pct) + '%' };
	}

	function init(el) {
		var cfg;
		try { cfg = JSON.parse(el.getAttribute('data-config')); } catch (e) { return; }
		if (!window.L || !window.WSM_WORLD) return;

		var mapEl = el.querySelector('.wsm-cmap-map');
		var listEl = el.querySelector('.wsm-cmap-list');
		var moreBtn = el.querySelector('.wsm-cmap-more');
		var sumEl = el.querySelector('.wsm-cmap-sum');
		var hiEl = el.querySelector('.wsm-cmap-scale .hi');
		var small = el.offsetWidth < 600;
		var data = null, byCode = {}, max = 0, showAll = false;

		var map = L.map(mapEl, {
			zoomControl: false,
			attributionControl: false,
			scrollWheelZoom: false,
			zoomSnap: 0.25,
			minZoom: 0.5,
			maxZoom: 6,
			worldCopyJump: false
		});
		if (!small) L.control.zoom({ position: 'topright' }).addTo(map);

		function style(f) {
			var c = byCode[f.properties.i];
			return { fillColor: shade(c ? c.v : 0, max), fillOpacity: 1, color: '#ffffff', weight: 0.7 };
		}

		function tipHtml(code, name) {
			var c = byCode[code];
			if (!c) return '<b>' + esc(name) + '</b><br>' + esc(tr(cfg, 'noVisitors'));
			var share = data.total ? Math.round(c.v / data.total * 100) : 0;
			var ch = change(cfg, c);
			return '<b>' + esc(c.n || name) + '</b><br>' + fmt(c.v) + ' ' + esc(tr(cfg, 'visitors', c.v)) + ' · ' + share + '%' +
				(ch ? '<br><span class="' + ch.cls + '">' + esc(ch.text) + '</span> ' + esc(data.cmp) : '');
		}

		// Another plugin may load an older Leaflet over ours: 0.7 names it geoJson and has no tooltips.
		var geoJSON = L.geoJSON || L.geoJson;
		var canTip = typeof L.Layer === 'function' && typeof L.Layer.prototype.bindTooltip === 'function';

		var shapes = {}; // ISO code -> Leaflet layer
		var world = geoJSON(window.WSM_WORLD, {
			// Antarctica only takes room at the bottom of a taller map.
			filter: function (f) { return f.properties.i !== 'AQ'; },
			style: style,
			onEachFeature: function (f, layer) {
				shapes[f.properties.i] = layer;
				if (canTip) layer.bindTooltip(function () { return tipHtml(f.properties.i, f.properties.n); }, { sticky: true, className: 'wsm-cmap-tip', direction: 'top', offset: [0, -8] });
				layer.on('mouseover', function () { highlight(f.properties.i, true); });
				layer.on('mouseout', function () { highlight(f.properties.i, false); });
			}
		}).addTo(map);

		// Countries too small for the 1:110m borders (e.g. Singapore) get a dot.
		var dots = L.layerGroup().addTo(map);

		function highlight(code, on) {
			var layer = shapes[code];
			if (layer) {
				layer.setStyle(on ? { color: '#101828', weight: 1.6 } : { color: '#ffffff', weight: 0.7 });
				if (on && layer.bringToFront) layer.bringToFront();
			}
			if (listEl) $(listEl).find('li[data-code="' + code + '"]').toggleClass('is-on', on);
		}

		var baseHeight = mapEl.offsetHeight || 430;
		var mapWrap = el.querySelector('.wsm-cmap-mapwrap');
		var sideEl = el.querySelector('.wsm-cmap-side');
		function fit() {
			var w = mapEl.offsetWidth;
			if (!w) return; // closed postbox: fitted again when it is opened
			var h = baseHeight;
			if (w < 600) {
				// Narrow screens: shorter map, so the world fills it instead of floating in empty space.
				h = Math.max(180, Math.min(baseHeight, Math.round(w * 0.6)));
			} else if (sideEl && mapWrap && sideEl.offsetTop === mapWrap.offsetTop) {
				// List beside the map: the map is as tall as the list (reset first so it can also shrink).
				mapEl.style.height = baseHeight + 'px';
				h = Math.max(baseHeight, sideEl.offsetHeight);
			}
			mapEl.style.height = h + 'px';
			map.invalidateSize();
			map.fitBounds([[-50, -168], [78, 190]], { padding: [4, 4] });
		}

		function renderList() {
			if (!listEl) return;
			var rows = data.countries;
			if (!rows.length) {
				listEl.innerHTML = '<li class="wsm-cmap-empty">' + esc(tr(cfg, data.range === 'online' ? 'noneOnline' : 'nonePeriod')) + '</li>';
				listEl.classList.remove('is-all');
				if (moreBtn) moreBtn.hidden = true;
				return;
			}
			var shown = showAll ? rows : rows.slice(0, LIST_SIZE);
			var top = rows[0].v || 1;
			listEl.innerHTML = shown.map(function (c) {
				var ch = change(cfg, c);
				var share = data.total ? c.v / data.total * 100 : 0;
				return '<li data-code="' + esc(c.c) + '">' +
					'<span class="f"><img src="' + esc(cfg.blank) + '" class="flag flag-' + esc(String(c.c).toLowerCase()) + '" alt=""></span>' +
					'<span class="n" title="' + esc(c.n) + '">' + esc(c.n) + '</span>' +
					'<span class="v">' + fmt(c.v) + '</span>' +
					'<span class="c ' + (ch ? ch.cls : '') + '">' + (ch ? esc(ch.text) : '') + '</span>' +
					'<span class="bar"><i style="width:' + (c.v / top * 100).toFixed(1) + '%"></i></span>' +
					'<span class="p">' + (share >= 1 || share === 0 ? Math.round(share) : '<1') + '%</span></li>';
			}).join('');
			listEl.classList.toggle('is-all', showAll);
			if (moreBtn) {
				moreBtn.hidden = rows.length <= LIST_SIZE;
				moreBtn.textContent = showAll ? tr(cfg, 'showTop').replace('%d', LIST_SIZE) + ' ‹' : tr(cfg, 'viewAll').replace('%d', rows.length) + ' ›';
			}
		}

		function render(d) {
			data = d;
			byCode = {};
			max = 0;
			d.countries.forEach(function (c) {
				byCode[c.c] = c;
				if (c.v > max) max = c.v;
			});
			world.setStyle(style);

			dots.clearLayers();
			d.countries.forEach(function (c) {
				if (shapes[c.c] || (!c.la && !c.lo)) return;
				var dot = L.circleMarker([c.la, c.lo], { radius: 5, color: '#fff', weight: 1, fillColor: shade(c.v, max), fillOpacity: 1 });
				if (canTip) dot.bindTooltip(tipHtml(c.c, c.n), { className: 'wsm-cmap-tip', direction: 'top', offset: [0, -6] });
				dot.addTo(dots);
			});

			if (hiEl) hiEl.textContent = fmt(max || 1);
			if (sumEl) {
				var n = d.countries.length;
				var s;
				if (!n) {
					s = esc(tr(cfg, d.range === 'online' ? 'noneOnline' : 'nonePeriod'));
				} else {
					s = (d.range === 'online' ? tr(cfg, 'sumOnline', d.total) : tr(cfg, 'sum', d.total))
						.replace('%1$s', '<b>' + fmt(d.total) + '</b>')
						.replace('%2$s', '<b>' + n + '</b> ' + esc(tr(cfg, 'countries', n)));
					if (d.cmp) s += ' · ' + esc(d.cmp);
				}
				sumEl.innerHTML = s;
			}
			renderList();
			fit(); // the list height may have changed
		}

		if (listEl) {
			$(listEl).on('mouseenter', 'li[data-code]', function () { highlight(this.getAttribute('data-code'), true); })
				.on('mouseleave', 'li[data-code]', function () { highlight(this.getAttribute('data-code'), false); });
		}
		if (moreBtn) {
			moreBtn.addEventListener('click', function () { showAll = !showAll; renderList(); fit(); });
		}

		// Range switcher. Unlocked (Pro): data over AJAX. Locked (this free version): embedded samples.
		var req = null;
		$(el).on('click', '.wsm-cmap-seg button', function () {
			var range = this.getAttribute('data-range');
			// Locked teaser (free version): switch between the embedded SAMPLE ranges, no request.
			if (cfg.locked) {
				$(el).find('.wsm-cmap-seg button').attr('aria-pressed', 'false');
				this.setAttribute('aria-pressed', 'true');
				$(el).find('.wsm-cmap-lock-text').each(function () {
					this.hidden = this.getAttribute('data-lock') !== (range === 'online' ? 'online' : 'map');
				});
				if (cfg.samples && cfg.samples[range]) { showAll = false; render(cfg.samples[range]); }
				return;
			}
			if (!cfg.ajax) return;
			$(el).find('.wsm-cmap-seg button').attr('aria-pressed', 'false');
			this.setAttribute('aria-pressed', 'true');
			el.classList.add('is-loading');
			if (req) req.abort();
			req = $.ajax({
				url: cfg.ajax,
				type: 'POST',
				dataType: 'json',
				data: { action: 'wsmCountryMap', range: range }
			}).done(function (res) {
				if (res && res.success && res.data) { showAll = false; render(res.data); }
			}).always(function () {
				req = null;
				el.classList.remove('is-loading');
			});
		});

		fit();
		render(cfg.data);
		var t;
		$(window).on('resize', function () { clearTimeout(t); t = setTimeout(fit, 150); });
		// The map is laid out again when its postbox is opened or moved.
		$(document).on('postbox-toggled postboxes-columnchange', function () { setTimeout(fit, 50); });
	}

	$(function () {
		// A failure in one map must never break anything else on the page.
		$('.wsm-cmap').each(function () {
			try {
				init(this);
			} catch (e) {
				if (window.console) console.error('Visitors by Country map:', e);
			}
		});
	});
})(jQuery);
