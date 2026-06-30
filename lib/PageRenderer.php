<?php
 
if(!class_exists('PageRenderer')) {
class PageRenderer {
    /**
     * Render a PHP file with FileLoader integration
     * @param string $pageFile Filename (auto-adds .php if needed)
     * @param string|bool $type FileLoader type or true for custom full path
     * @param array $data Optional data to extract into scope
     * @return bool True if rendered, false otherwise
     */
public static function render($pageFile, $type = 'pages', $data = []) {

    if (is_array($pageFile)) {
        $folder   = key($pageFile);
        $file     = current($pageFile);
        $pageFile = is_string($folder) ? $folder . '/' . $file : $file;
    }

    $pageFile = (pathinfo($pageFile, PATHINFO_EXTENSION) === '') ? $pageFile . '.php' : $pageFile;

    if (!empty($data)) {
        extract($data, EXTR_SKIP);
    }

    if ($type === true) {
        if (file_exists($pageFile)) {
            include $pageFile;
            return true;
        }
        echo "Error: File '{$pageFile}' not found.";
        return false;
    }

    $filepath = FileLoader::findFile($type, $pageFile);

    if ($filepath) {
        include $filepath;
        return true;
    }

    echo "Error: File '{$pageFile}' not found in '{$type}'.";
    return false;
}
    
    /**
     * Render and return output as string (buffered)
     */
    public static function fetch($pageFile, $type = 'pages', $data = []) {
        ob_start();
        self::render($pageFile, $type, $data);
        return ob_get_clean();
    }
}
}