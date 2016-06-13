<?
session_start();

// config
$db        = new mysqli('localhost','root','','mal');
$domain    = 'mal.dev';
$email     = 'mail@mal.dev';
$fb        = 'tmalenkowicz';
$adminpass = '****';
$prefix    = 'malen_';
$perpage   = 16;
$sendmails = true;

// routing
$par = $_SERVER['REQUEST_URI'] !== '/' ?
  (strpos($_SERVER['REQUEST_URI'],'?') ?
    explode("/", substr($_SERVER['REQUEST_URI'],1,strpos($_SERVER['REQUEST_URI'],'?')-1)) :
    explode("/", substr($_SERVER['REQUEST_URI'],1))) :
  array('home');

// admin
if (isset($_POST['pass'])) {
  if ($_POST['pass'] == $adminpass) {$_SESSION['admin'] = true;}
  else {$loginError = true;}
}
if ($par[0] == 'out') {unset($_SESSION['admin']); session_destroy();}
$adm = isset($_SESSION['admin']) && $_SESSION['admin'];

// libs, classes
require_once('lib/swift/swift_required.php');
spl_autoload_register(function ($class_name) {
  $path = $class_name . '.class.php';
  if (file_exists($path)) {
    require_once($path);
  }
});

// functions
function getIp() {
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    return $_SERVER['HTTP_CLIENT_IP'];
  } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    return $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
    return $_SERVER['REMOTE_ADDR'];
  }
}

function closetags ($html) {
  preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
  for($i=0;$i<count($result[0]);$i++) {if(substr($result[0][$i],-2)=='/>') unset($result[1][$i]);}
  $openedtags = $result[1];
  preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
  $closedtags = $result[1];
  $len_opened = count ( $openedtags );
  if( count ( $closedtags ) == $len_opened ) { return $html; }
  $openedtags = array_reverse ( $openedtags );
  for( $i = 0; $i < $len_opened; $i++ ) {
    if (!in_array($openedtags[$i], $closedtags)) {
      $html .= "</" . $openedtags[$i] . ">";
    } else {
      unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
    }
  }
  return $html;
}

// routing
if ($par[0] == 'rss') {require('rss.php');exit;}