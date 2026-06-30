/**
 * pixel.js — WiredHowse Tracking Pixel
 *
 * Works two ways:
 *  1) Direct embed: <script src=".../pixel.js"></script> then call wh('init', 'SITE_ID'); wh('track','pageview');
 *  2) Inside Google Tag Manager: paste the GTM snippet below into a Custom HTML tag,
 *     fire it on the triggers you want (All Pages = pageview, button click = conversion).
 *
 * Endpoint: change ENDPOINT below to your live track.php URL before deploying.
 */
(function (window) {
  var ENDPOINT = 'https://qop.wiredhowse.app/track.php';
  var SESSION_KEY = 'wh_session_id';
  var SITE_KEY = 'wh_site_id';

  function uuid() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = (Math.random() * 16) | 0, v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  }

  function getSessionId() {
    try {
      var sid = sessionStorage.getItem(SESSION_KEY);
      if (!sid) {
        sid = uuid();
        sessionStorage.setItem(SESSION_KEY, sid);
      }
      return sid;
    } catch (e) {
      // sessionStorage blocked (privacy mode etc) - fall back to a per-call id
      return uuid();
    }
  }

  function getClickId() {
    var params = new URLSearchParams(window.location.search);
    return params.get('click_id') || params.get('cid') || params.get('clickid') || null;
  }

  function send(eventType, data) {
    data = data || {};
    var siteId = window.__whSiteId;
    if (!siteId) {
      console.warn('[wh-pixel] init not called, no site_id set');
      return;
    }

    var payload = {
      site_id: siteId,
      event: eventType,
      session_id: getSessionId(),
      click_id: getClickId(),
      url: window.location.href,
      value: data.value || null,
      meta: JSON.stringify(data.meta || {})
    };

    var body = Object.keys(payload)
      .filter(function (k) { return payload[k] !== null && payload[k] !== undefined; })
      .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(payload[k]); })
      .join('&');

    // sendBeacon survives page navigation (important on conversion redirects); fallback to fetch
    if (navigator.sendBeacon) {
      var blob = new Blob([body], { type: 'application/x-www-form-urlencoded' });
      navigator.sendBeacon(ENDPOINT, blob);
    } else {
      fetch(ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body,
        keepalive: true
      }).catch(function () {});
    }
  }

  function autoInit() {
    // find this script's own tag and pull site_id off its src query string,
    // so a single <script src="...pixel.js?site_id=XYZ"> is enough - no second
    // config block, no load-order requirement.
    var scripts = document.getElementsByTagName('script');
    for (var i = 0; i < scripts.length; i++) {
      var src = scripts[i].src || '';
      if (src.indexOf('pixel.js') !== -1) {
        try {
          var url = new URL(src);
          var sid = url.searchParams.get('site_id');
          if (sid) {
            window.__whSiteId = sid;
            send('pageview');
          }
        } catch (e) {}
        break;
      }
    }
  }

  window.wh = function (command, a, b) {
    if (command === 'init') {
      window.__whSiteId = a;
    } else if (command === 'track') {
      send(a, b);
    }
  };

  // auto-fire pageview if site_id was supplied on the script tag itself
  autoInit();
})(window);
