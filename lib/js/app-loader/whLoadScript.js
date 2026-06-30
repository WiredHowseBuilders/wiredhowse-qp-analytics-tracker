// Function to load a script dynamically
function whLoadScript(whScriptName) {
    const whScriptUrl = window.whglblConfig.getwhScriptUrl(whScriptName);
    
    const whScriptElement = document.createElement('script');
    whScriptElement.src = whScriptUrl;
    whScriptElement.type = 'text/javascript';
    whScriptElement.async = true;
    
    document.head.appendChild(whScriptElement);
    
    console.log(`Loading script: ${whScriptUrl}`);
}

// Example: Loading the global script or defer script
whLoadScript(window.whglblConfig.whGlblScript);
whLoadScript(window.whglblConfig.additionalScripts.deferScript);
