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