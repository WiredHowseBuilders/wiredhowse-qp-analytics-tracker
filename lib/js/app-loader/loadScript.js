// Function to load a script dynamically
function loadScript(scriptName) {
    const scriptUrl = window.glblConfig.getScriptUrl(scriptName);
    
    const scriptName = document.createElement('script');
    scriptName.src = scriptUrl;
    scriptName.type = 'text/javascript';
    scriptName.async = true;
    
    document.head.appendChild(scriptName);
    
    console.log(`Loading script: ${scriptUrl}`);
}

// Example: Loading the global script or defer script
loadScript(window.glblConfig.glblScript);
loadScript(window.glblConfig.additionalScripts.deferScript);
