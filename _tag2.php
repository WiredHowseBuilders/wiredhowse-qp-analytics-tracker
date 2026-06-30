<script src="https://q0p.wiredhowse.app/pixel.js?site_id=001" async></script>
<script src="https://q0p.wiredhowse.app/conversion.js?_wha=conversion" async></script>
============================================================
TAG 2: "WH Pixel - Conversion"
Trigger: whatever fires conversion (form submit success, thank-you page load,
button click — set this trigger in GTM based on the actual conversion event)
============================================================
<script>
(function(){
  function fire() {
    if (typeof wh === 'function') {
      wh('track', 'conversion', { value: {{conversion value}} }); // wire to a GTM variable, or hardcode a number
    } else {
      setTimeout(fire, 200); // base tag hasn't loaded yet, retry briefly
    }
  }
  fire();
})();
</script>

Tag 1 must be present on the same page (or a prior page in the same session)
so wh() exists. The retry loop covers normal load-order timing.

============================================================

<a href="_tag1.php">001</a>

<a href="_tag2.php">002</a>

<a href="_tag2.php">003</a>

