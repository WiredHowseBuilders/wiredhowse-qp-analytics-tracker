// (function(){
//   function fire() {
//     if (typeof wh === 'function') {
//       wh('track', 'conversion', { value: {{conversion value}} }); // wire to a GTM variable, or hardcode a number
//     } else {
//       setTimeout(fire, 200); // base tag hasn't loaded yet, retry briefly
//     }
//   }
//   fire();
// })();



(function(){

    var conversion_value = '0009000'; //{{conversion_value}} 
  function fire() {
    if (typeof wh === 'function') {
      wh('track', 'conversion', { value:conversion_value }); // wire to a GTM variable, or hardcode a number
    } else {
      setTimeout(fire, 200); // base tag hasn't loaded yet, retry briefly
    }
  }
  fire();
})();