<?php
/** Staging */
ini_set('display_errors', 0);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);
/** Disable all file modifications including updates and update notifications */
//define('DISALLOW_FILE_MODS', true);

define('PLL_CACHE_LANGUAGES', false);

function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
if (!(php_sapi_name() == "cli")) :
  if (!isset($_COOKIE["alpaga-gate"])) :
      if ((isset($_GET["view"]) && $_GET["view"] == "adoptanalpaca")) {
          setcookie("alpaga-gate","yes", time()+ (3600 * 24 *30));
      } else {
          header('Content-Type: text/html; charset=utf-8');
          print file_get_contents(__dir__ . '/../offline/content.html');
          die();
      }
  endif;
endif;
