// Global Configuration Object for Paths and File Names
window.glblConfig = {
    // General Script Path
    glblPath: "http://editorial.wiredhowse.com/util/lib/js/app-loader/",
    
    // Specific Script File
    glblScript: "glblScript.js",

    // Additional script(s) or resources can be added here
    additionalScripts: {
        deferScript: "deferScript.js",
        anotherScript: "app.js"
    },

    // Methods to dynamically access and manage these paths
    getScriptUrl: function(scriptName) {
        return this.glblPath + scriptName;
    }
};