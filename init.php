<?php
/**
 * Kint is a zero-setup debugging tool to output information about variables and stack traces prettily and comfortably.
 *
 * https://github.com/kint-php/kint
 */
if (defined('KINT_DIR')) {
    return;
}

if (version_compare(PHP_VERSION, '5.1.2') < 0) {
    return trigger_error('Kint 2.0 requires PHP 5.1.2 or higher', E_USER_ERROR);
}

define('KINT_DIR', dirname(__FILE__));
define('KINT_PHP52', (version_compare(PHP_VERSION, '5.2') >= 0));
define('KINT_PHP523', (version_compare(PHP_VERSION, '5.2.3') >= 0));
define('KINT_PHP525', (version_compare(PHP_VERSION, '5.2.5') >= 0));
define('KINT_PHP53', (version_compare(PHP_VERSION, '5.3') >= 0));
define('KINT_PHP70', (version_compare(PHP_VERSION, '7.0') >= 0));

// Only preload classes if no autoloader specified
if (!class_exists('Kint', true)) {
    require_once dirname(__FILE__).'/src/Kint.php';

    // Data
    require_once dirname(__FILE__).'/src/Object.php';
    require_once dirname(__FILE__).'/src/Object/Instance.php';
    require_once dirname(__FILE__).'/src/Object/Blob.php';
    require_once dirname(__FILE__).'/src/Object/Closure.php';
    require_once dirname(__FILE__).'/src/Object/Color.php';
    require_once dirname(__FILE__).'/src/Object/Method.php';
    require_once dirname(__FILE__).'/src/Object/Nothing.php';
    require_once dirname(__FILE__).'/src/Object/Parameter.php';
    require_once dirname(__FILE__).'/src/Object/Trace.php';
    require_once dirname(__FILE__).'/src/Object/TraceFrame.php';
    require_once dirname(__FILE__).'/src/Object/Representation.php';
    require_once dirname(__FILE__).'/src/Object/Representation/Color.php';
    require_once dirname(__FILE__).'/src/Object/Representation/Docstring.php';
    require_once dirname(__FILE__).'/src/Object/Representation/Microtime.php';
    require_once dirname(__FILE__).'/src/Object/Representation/Source.php';
    require_once dirname(__FILE__).'/src/Object/Representation/SplFileInfo.php';

    // Parsers
    require_once dirname(__FILE__).'/src/Parser.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Base64.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Binary.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Blacklist.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/ClassMethods.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/ClassStatics.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Closure.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Color.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/DOMIterator.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/DOMNode.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/FsPath.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Iterator.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Json.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Microtime.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Serialize.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/SimpleXMLElement.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/SplFileInfo.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Table.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Timestamp.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Trace.php';
    require_once dirname(__FILE__).'/src/Parser/Plugin/Xml.php';
    //~ require_once dirname(__FILE__).'/src/Parser/Plugin/SplObjectStorage.php';

    // Renderers
    require_once dirname(__FILE__).'/src/Renderer.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Plugin.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Binary.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Blacklist.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Callable.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Closure.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Color.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/ColorDetails.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/DepthLimit.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Docstring.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Microtime.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Nothing.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Recursion.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/SimpleXMLElement.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Source.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Table.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/Timestamp.php';
    require_once dirname(__FILE__).'/src/Renderer/Rich/TraceFrame.php';
    //~ require_once dirname(__FILE__).'/src/Renderer/Plain.php';
}

// Dynamic default settings
Kint::$file_link_format = ini_get('xdebug.file_link_format');
if (isset($_SERVER['DOCUMENT_ROOT'])) {
    Kint::$app_root_dirs = array($_SERVER['DOCUMENT_ROOT'] => '<ROOT>');
}

if (!function_exists('d')
) {
    /**
     * Alias of Kint::dump().
     *
     * @return string
     */
    function d()
    {
        $args = func_get_args();

        return call_user_func_array(array('Kint', 'dump'), $args);
    }

    Kint::$aliases[] = 'd';
}

if (!function_exists('s')) {
    /**
     * Alias of Kint::dump(), however the output is in plain text.
     *
     * Alias of Kint::dump(), however the output is in plain htmlescaped text
     * with some minor visibility enhancements added.
     *
     * If run in CLI mode, output is not escaped.
     *
     * To force rendering mode without autodetecting anything:
     *
     * Kint::$enabled_mode = Kint::MODE_PLAIN;
     * Kint::dump( $variable );
     *
     * @return string
     */
    function s()
    {
        if (!Kint::$enabled_mode) {
            return '';
        }

        $stash = Kint::settings();

        if (Kint::$enabled_mode !== Kint::MODE_WHITESPACE) {
            Kint::$enabled_mode = Kint::MODE_PLAIN;
            if (PHP_SAPI === 'cli' && Kint::$cli_detection === true) {
                Kint::$enabled_mode = Kint::MODE_CLI;
            }
        }

        $args = func_get_args();
        $out = call_user_func_array(array('Kint', 'dump'), $args);

        Kint::settings($stash);

        return $out;
    }

    Kint::$aliases[] = 's';
}
