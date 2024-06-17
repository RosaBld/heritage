<?php
/** Development */
define('SAVEQUERIES', true);
define('WP_DEBUG', true);
define('SCRIPT_DEBUG', true);
define('PLL_CACHE_LANGUAGES', false);
define('FS_METHOD', 'direct');
define('FS_CHMOD_DIR',0755);
define('FS_CHMOD_FILE',0644);

if (!(php_sapi_name() == "cli")) :
    if (!isset($_COOKIE["alpaga-gate"]) || (isset($_SERVER["REMOTE_ADDR"]) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']))) :
        if (isset($_GET["view"]) && $_GET["view"] == "master" || (isset($_SERVER["REMOTE_ADDR"]) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']))) {
            setcookie("alpaga-gate","yes", time()+ (3600 * 24 *30));
        } else {
            die("There's nothing here.");
        }
    endif;
endif;
