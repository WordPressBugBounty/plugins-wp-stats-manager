<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<style>
#wsm-upgrade-modal{padding:0;min-width:480px;max-width:520px;border-radius:20px;overflow:hidden;border:none;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.wsm-hero{background:#0F1B2D;padding:32px 28px 26px;position:relative;overflow:hidden}
.wsm-hero-glow1{position:absolute;top:-60px;right:-60px;width:200px;height:200px;border-radius:50%;background:#185FA5;opacity:0.25;pointer-events:none}
.wsm-hero-glow2{position:absolute;bottom:-80px;left:-40px;width:160px;height:160px;border-radius:50%;background:#534AB7;opacity:0.2;pointer-events:none}
.wsm-version-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(83,74,183,0.3);border:1px solid rgba(83,74,183,0.6);color:#AFA9EC;font-size:11px;font-weight:500;padding:4px 10px;border-radius:20px;margin-bottom:16px;position:relative;z-index:1}
.wsm-version-dot{width:6px;height:6px;border-radius:50%;background:#7F77DD;display:inline-block;flex-shrink:0}
.wsm-hero-title{color:#fff;font-size:22px;font-weight:600;line-height:1.35;margin:0 0 8px;position:relative;z-index:1}
.wsm-hero-title span{color:#7F77DD}
.wsm-hero-sub{color:rgba(255,255,255,0.5);font-size:13px;line-height:1.6;margin:0;position:relative;z-index:1}
.wsm-body{padding:22px 26px 26px;background:#fff}
.wsm-offer-strip{display:flex;align-items:center;gap:10px;background:#FFF8EE;border:1px solid #EF9F27;border-radius:10px;padding:11px 14px;margin-bottom:20px}
.wsm-offer-icon{font-size:18px;flex-shrink:0;line-height:1}
.wsm-offer-left{flex:1}
.wsm-offer-title{font-size:13px;font-weight:600;color:#633806;margin:0}
.wsm-offer-sub{font-size:11px;color:#854F0B;margin:2px 0 0}
.wsm-offer-badge{background:#E87722;color:#fff;font-size:12px;font-weight:600;padding:4px 10px;border-radius:6px;white-space:nowrap}
.wsm-section-label{font-size:11px;font-weight:600;color:#888;letter-spacing:0.8px;text-transform:uppercase;margin:0 0 10px;display:block}
.wsm-features{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px}
.wsm-feat{display:flex;align-items:center;gap:8px;background:#F8F8FA;border:1px solid #EBEBED;border-radius:8px;padding:9px 11px}
.wsm-feat-icon{width:22px;height:22px;border-radius:6px;background:#E6F1FB;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wsm-feat-icon svg{display:block}
.wsm-feat-text{font-size:12px;color:#555;line-height:1.3;margin:0}
.wsm-social-proof{display:flex;align-items:center;gap:10px;padding:12px 14px;background:#F8F8FA;border-radius:10px;margin-bottom:20px;border:1px solid #EBEBED}
.wsm-avatars{display:flex}
.wsm-av{width:26px;height:26px;border-radius:50%;border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#fff;margin-left:-8px}
.wsm-av:first-child{margin-left:0}
.wsm-sp-text{flex:1;font-size:12px;color:#666}
.wsm-sp-text strong{color:#222;font-weight:600;display:block}
.wsm-stars{color:#EF9F27;font-size:11px;letter-spacing:1px}
.wsm-btn-main{display:flex;width:100%;background:#185FA5;color:#fff;border:none;border-radius:10px;padding:14px;font-size:15px;font-weight:600;cursor:pointer;align-items:center;justify-content:center;gap:8px;margin-bottom:12px;text-decoration:none;box-sizing:border-box}
.wsm-btn-main:hover{background:#0C447C;color:#fff}
.wsm-btn-main svg{flex-shrink:0}
.wsm-skip{display:block;text-align:center;font-size:12px;color:#999;cursor:pointer;background:none;border:none;width:100%;padding:4px 0;margin:0}
.wsm-skip:hover{color:#555}
.close-modal{display:none !important}
</style>

<div id="wsm-upgrade-modal" style="display:none">
  <div class="wsm-hero">
    <div class="wsm-hero-glow1"></div>
    <div class="wsm-hero-glow2"></div>
    <div class="wsm-version-badge">
      <span class="wsm-version-dot"></span>
      New &mdash; Version 6.8
    </div>
    <h2 class="wsm-hero-title">Stop guessing.<br><span>Know exactly</span> who visits your site &mdash; and why.</h2>
    <p class="wsm-hero-sub">Join 10,000+ website owners who turn raw traffic data into real growth every single day.</p>
  </div>
  <div class="wsm-body">
    <div class="wsm-offer-strip">
      <div class="wsm-offer-icon">&#9889;</div>
      <div class="wsm-offer-left">
        <p class="wsm-offer-title">Limited-time offer &mdash; ends soon</p>
        <p class="wsm-offer-sub">50% off your upgrade + free addons included</p>
      </div>
      <div class="wsm-offer-badge">50% OFF</div>
    </div>
    <span class="wsm-section-label">What you unlock</span>
    <div class="wsm-features">
      <div class="wsm-feat">
        <div class="wsm-feat-icon"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="4" stroke="#185FA5" stroke-width="1.2"/><circle cx="6" cy="6" r="1.5" fill="#185FA5"/></svg></div>
        <p class="wsm-feat-text">Real-time online users</p>
      </div>
      <div class="wsm-feat">
        <div class="wsm-feat-icon"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 9L5 6L7 8L10 3" stroke="#185FA5" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <p class="wsm-feat-text">Advanced reporting</p>
      </div>
      <div class="wsm-feat">
        <div class="wsm-feat-icon"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><rect x="1" y="3" width="10" height="7" rx="1.5" stroke="#185FA5" stroke-width="1.2"/><path d="M4 3V2a2 2 0 014 0v1" stroke="#185FA5" stroke-width="1.2" stroke-linecap="round"/></svg></div>
        <p class="wsm-feat-text">Visitor tracking by IP</p>
      </div>
      <div class="wsm-feat">
        <div class="wsm-feat-icon"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1C3.8 1 2 2.8 2 5c0 3 4 7 4 7s4-4 4-7c0-2.2-1.8-4-4-4z" stroke="#185FA5" stroke-width="1.2"/><circle cx="6" cy="5" r="1.2" fill="#185FA5"/></svg></div>
        <p class="wsm-feat-text">Live visitor map</p>
      </div>
      <div class="wsm-feat">
        <div class="wsm-feat-icon"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="4" r="2" stroke="#185FA5" stroke-width="1.2"/><path d="M2 10c0-2.2 1.8-4 4-4s4 1.8 4 4" stroke="#185FA5" stroke-width="1.2" stroke-linecap="round"/></svg></div>
        <p class="wsm-feat-text">Country &amp; city data</p>
      </div>
      <div class="wsm-feat">
        <div class="wsm-feat-icon"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M10 3L5 8L2 5" stroke="#185FA5" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <p class="wsm-feat-text">Priority support</p>
      </div>
    </div>
    <div class="wsm-social-proof">
      <div class="wsm-avatars">
        <div class="wsm-av" style="background:#185FA5">A</div>
        <div class="wsm-av" style="background:#534AB7">M</div>
        <div class="wsm-av" style="background:#0F6E56">S</div>
        <div class="wsm-av" style="background:#993C1D">R</div>
      </div>
      <div class="wsm-sp-text">
        <strong>10,000+ website owners trust us</strong>
        <span class="wsm-stars">&#9733;&#9733;&#9733;&#9733;&#9733; Rated 5 stars</span>
      </div>
    </div>
    <a href="https://www.plugins-market.com/product/visitor-statistics-pro/" target="_blank" class="wsm-btn-main">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2v9M4 8l4 4 4-4" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 13h12" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
      Upgrade Now &mdash; Get 50% Off
    </a>
    <button class="wsm-skip" id="wsm-dismiss-btn">Dismiss</button>
  </div>
</div>

<script>
jQuery(document).ready(function($){
  <?php if ( ! get_option( 'wsm_upgrade_modal_dismissed', false ) ) : ?>
  var wsmInstallTime = <?php echo (int) get_option( 'wsm_upgrade_modal_install_time', time() ); ?>;
  var wsmNow        = <?php echo time(); ?>;
  var wsmElapsed    = wsmNow - wsmInstallTime;

  if( wsmElapsed >= 86400 ){
    $('#wsm-upgrade-modal').modal({ escapeClose: true, clickClose: true, showClose: false });
  }
  <?php endif; ?>

  function wsmDoD(){
    $.modal.close();
    $.post('<?php echo esc_js( admin_url("admin-ajax.php") ); ?>', {
      action: 'wsm_dismiss_upgrade_modal',
      nonce:  '<?php echo esc_js( wp_create_nonce("wsm_dismiss_upgrade") ); ?>'
    });
  }

  $('#wsm-dismiss-btn').on('click', function(){ wsmDoD(); });
  $(document).on('modal:close', '#wsm-upgrade-modal', function(){ wsmDoD(); });
});
</script>
