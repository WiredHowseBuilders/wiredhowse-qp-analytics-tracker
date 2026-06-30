

<a hred="_tag1.php">001</a>

<a hred="_tag2.php">002</a>

<a hred="_tag2.php">003</a>

============================================================
NON-GTM / DIRECT EMBED (same two events, just inline)
============================================================
On the optin page, right before </body>:

<script src="https://q0p.wiredhowse.app/pixel.js?site_id=001" async></script>

On the conversion page (thank-you page), right before </body>:

<script src="https://q0p.wiredhowse.app/pixel.js?site_id=001" async></script>
<script>
  wh('track', 'conversion', { value: 47.00 }); // swap in real sale amount if known
</script>

Note: the conversion page still needs its own pixel.js tag (it auto-fires a
pageview too, which is fine and useful) - then the second script adds the
conversion event on top.


//$id = 'SELECT * FROM events WHERE site_id = 'SITE_ID_GOES_HERE' ORDER BY created_at DESC LIMIT 10;'; 