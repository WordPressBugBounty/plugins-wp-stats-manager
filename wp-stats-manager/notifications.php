<?php
if (! defined('ABSPATH')) {
	// Exit if accessed directly.
	exit;
}

// Use your own prefix, i use "wsm_free_", replace it;
$wsm_icon_path = plugins_url('/images/icon-128x128.gif', __FILE__);
$wsm_rating_url = "https://wordpress.org/support/plugin/wp-stats-manager/reviews/?filter=5#new-post";
$wsm_activation_time = 604800; // 7 days in seconds
$wsm_file_version = 2.2;
$wsm_development_mode = false; // Put yes to allow development mode, you will see the rating notice without timers

/**
 * @since  1.9
 * @version 1.9
 * @class wsm_free_Notification
 */

if (! class_exists('wsm_free_Notification')) :

	class wsm_free_Notification
	{

		/* * * * * * * * * *
    * Class constructor
    * * * * * * * * * */
		public function __construct()
		{

			$this->_hooks();
		}

		/**
		 * Hook into actions and filters
		 * @since  1.0.0
		 * @version 1.2.1
		 */
		private function _hooks()
		{
			add_action('admin_init', array($this, 'wsm_free_review_notice'));
			add_action('admin_notices', array($this, 'wsm_timezone_mismatch_notice'));
		}


		function wsm_timezone_mismatch_notice()
		{
			// Check if the current user has permission
			if (!current_user_can('manage_options')) {
				return;
			}

			// Get the current admin page
			$current_screen = get_current_screen();
			$allowed_pages  = ['visitor-statistics_page_wsm_settings', 'visitor-statistics_page_wsm_traffic', 'visitor-statistics_page_wsm_trafficsrc', 'visitor-statistics_page_wsm_visitors', 'visitor-statistics_page_wsm_content', 'visitor-statistics_page_wsm_addons']; // Add other plugin page slugs if needed

			if (!in_array($current_screen->id, $allowed_pages)) {
				return; // Show only on plugin pages
			}

			$user_timezone   = get_option(WSM_PREFIX . 'TimezoneString');
			$system_timezone = $this->wsm_get_system_timezone();

			if ($user_timezone && $system_timezone && $user_timezone !== $system_timezone) {
				$settings_url = admin_url('admin.php?page=wsm_settings');
				$dismiss_url  = add_query_arg('wsm_dismiss_timezone_notice', '1');

				// Check if the user dismissed the notice
				if (isset($_GET['wsm_dismiss_timezone_notice'])) {
					update_user_meta(get_current_user_id(), 'wsm_dismiss_timezone_notice', '1');
					return;
				}

				// If the notice is dismissed, do not show it
				if (get_user_meta(get_current_user_id(), 'wsm_dismiss_timezone_notice', true)) {
					return;
				}

				echo '<div class="notice notice-warning wsm-timezone-notice">
        <p>
    <strong>' . __('Timezone Mismatch: ', 'wordpress-stats-manager') . '</strong> 
    ' . __(' Your selected timezone does not match the system timezone. This may affect how your statistics are displayed.', 'wordpress-stats-manager') . '
    <a href="' . esc_url($settings_url) . '" class="wsm-update-timezone">' . __('Update Timezone Settings', 'wordpress-stats-manager') . '</a>
    <span class="wsm-notice-right">
        <a href="' . esc_url($dismiss_url) . '" class="wsm-dismiss-notice">✖</a>
    </span>
</p>

      </div>
      <style>
       .wsm-timezone-notice {
    border-left: 4px solid #ffba00;
    background: #fff;
    color: #333;
    padding: 10px 15px;
    margin: 10px 0;
    font-size: 14px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.wsm-timezone-notice p {
    margin: 0;
    display: flex;
    align-items: center;
    flex-grow: 1;
}

.wsm-update-timezone {
    color: #0073aa;
    text-decoration: none;
    font-weight: bold;
    margin-left: 10px;
}

.wsm-update-timezone:hover {
    text-decoration: underline;
}

.wsm-notice-right {
    margin-left: auto; /* This pushes the cancel icon to the right */
}

.wsm-dismiss-notice {
    color: #777;
    text-decoration: none;
    font-size: 16px;
    cursor: pointer;
}

.wsm-dismiss-notice:hover {
    color: #d63638;
}

      </style>';
			}
		}


		function wsm_get_system_timezone()
		{
			$timezone = get_option('timezone_string');

			// If timezone_string is empty, fallback to gmt_offset
			if (!$timezone) {
				$gmt_offset = get_option('gmt_offset');
				if ($gmt_offset !== '') {
					$timezone = 'UTC' . ($gmt_offset >= 0 ? '+' : '') . $gmt_offset;
				}
			}

			return $timezone;
		}
		/**
		 * Ask users to review our plugin on wordpress.org
		 *
		 * @since 1.0.11
		 * @return boolean false
		 * @version 1.1.3
		 */
		public function wsm_free_review_notice()
		{

			global $wsm_file_version, $wsm_activation_time, $wsm_development_mode;

			$this->wsm_free_review_dismissal();

			$this->wsm_free_review_pending();

			$wsm_activation_time 	= get_site_option('wsm_free_active_time');

			$review_dismissal	= get_site_option('wsm_free_review_dismiss');

			if ($review_dismissal == 'yes' && !$wsm_development_mode) return;

			if (!$wsm_activation_time && !$wsm_development_mode) :

				$wsm_activation_time = time(); // Reset Time to current time.
				add_site_option('wsm_free_active_time', $wsm_activation_time);

			endif;
			if ($wsm_development_mode) $wsm_activation_time = 432001; //This variable used to show the message always for testing purposes only
			// 432000 = 5 Days in seconds.
			if (time() - $wsm_activation_time > 432000) :

				wp_enqueue_style('wsm_free_review_stlye', plugins_url('/css/style-review.css', __FILE__), array(), $wsm_file_version);
				add_action('admin_notices', array($this, 'wsm_free_review_notice_message'));

			endif;
		}

		/**
		 *	Check and Dismiss review message.
		 *
		 *	@since 1.9
		 */
		private function wsm_free_review_dismissal()
		{

			if (
				! is_admin() ||
				! current_user_can('manage_options') ||
				! isset($_GET['_wpnonce']) ||
				! wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'wsm_free_review-nonce') ||
				! isset($_GET['wsm_free_review_dismiss'])
			) :

				return;
			endif;

			add_site_option('wsm_free_review_dismiss', 'yes');
		}

		/**
		 * Set time to current so review notice will popup after 14 days
		 *
		 * @since 1.9
		 */
		private function wsm_free_review_pending()
		{

			if (
				! is_admin() ||
				! current_user_can('manage_options') ||
				! isset($_GET['_wpnonce']) ||
				! wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'wsm_free_review-nonce') ||
				! isset($_GET['wsm_free_review_later'])
			) :

				return;
			endif;

			// Reset Time to current time.
			update_site_option('wsm_free_active_time', time());
		}

		/**
		 * Review notice message
		 *
		 * @since  1.0.11
		 */
		public function wsm_free_review_notice_message()
		{

			$scheme      = (wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)) ? '&' : '?';
			$url         = $_SERVER['REQUEST_URI'] . $scheme . 'wsm_free_review_dismiss=yes';
			$dismiss_url = wp_nonce_url($url, 'wsm_free_review-nonce');

			$_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'wsm_free_review_later=yes';
			$later_url   = wp_nonce_url($_later_link, 'wsm_free_review-nonce');

			global $wsm_icon_path;

			global $wsm_rating_url;
?>

			<div class="wsm_free_review-notice">
				<div class="wsm_free_review-thumbnail">
					<img src="<?php echo esc_url($wsm_icon_path); ?>" alt="">
				</div>
				<div class="wsm_free_review-text">
					<h3><?php _e('Leave A Review?', 'wp-stats-manager') ?></h3>
					<p><?php _e('We hope you\'ve enjoyed using Visitor Statistics :) Would you mind taking a few minutes to write a review on WordPress.org?<br>Just writing simple "thank you" will make us happy!', 'wp-stats-manager') ?></p>
					<ul class="wsm_free_review-ul">
						<li><a href="<?php echo esc_url($wsm_rating_url); ?>" target="_blank"><span class="dashicons dashicons-external"></span><?php _e('Sure! I\'d love to!', 'wp-stats-manager') ?></a></li>
						<li><a href="<?php echo esc_url($dismiss_url); ?>"><span class="dashicons dashicons-smiley"></span><?php _e('I\'ve already left a review', 'wp-stats-manager') ?></a></li>
						<li><a href="<?php echo esc_url($later_url); ?>"><span class="dashicons dashicons-calendar-alt"></span><?php _e('Will Rate Later', 'wp-stats-manager') ?></a></li>
						<li><a href="<?php echo esc_url($dismiss_url); ?>"><span class="dashicons dashicons-dismiss"></span><?php _e('Hide Forever', 'wp-stats-manager') ?></a></li>
					</ul>
				</div>
			</div>
<?php
		}
	}

endif;
$admincore = '';
if (isset($_GET['page'])) $admincore = sanitize_text_field($_GET['page']);
if ($admincore != 'wsmoptionspro') {
	new wsm_free_Notification();
}
?>