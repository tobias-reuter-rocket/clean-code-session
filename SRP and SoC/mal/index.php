<? ob_start(); require_once('sh.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?
    $sp = ' &bull; ';
    if (isset($par[0])) {
      if ($par[0] == 'o-mnie') {echo 'O mnie'.$sp;}
      if (in_array($par[0],array('polityka','opinie','krotko-i-na-temat', 'okiem-berlinczyka'))) {
        if (isset($par[1]) && is_numeric($par[1])) {
          $a = Art::getOne($par[1]);
          echo $a->getTitle().$sp.$a->getCatName().$sp;
        } else {
          echo Art::getFullCatName($par[0]).$sp;
        }
      }
    }
  ?>Tomasz Malenkowicz</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="...">
  <meta name="description" content="<?
    if (isset($a) && $a) {echo addslashes($a->getText(false,true));}
    else {echo '...';}
  ?>">
  <meta name="keywords" content="<?
    if (isset($a) && $a) {echo $a->getTagsPlain();}
    else {echo '...';}
  ?>">
  <? if (isset($a) && $a): ?>
    <meta property="og:description" content="<?=$a->getText(false,true)?>"/>
    <meta property="og:url" content="http://<?=$domain.$a->getLink()?>"/>
    <meta property="og:title" content="<?=$a->getTitle()?>"/>
  <? endif; ?>
  <meta name="my:fb" content="on"/>
  <meta property="og:locale" content="pl_PL"/>
  <meta property="og:site_name" content="Tomasz Malenkowicz"/>
  <link rel="stylesheet" href="/lib/bootstrap.min.css" media="screen">
  <link rel="stylesheet" href="/lib/select2/select2.css">
  <link rel="stylesheet" href="/style.css">
  <link rel="alternate" type="application/rss+xml" title="RSS" href="/rss"/>
  <link rel="shortcut icon" href="/gfx/favicon.png" />
  <script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
<header>
  <div class="container">
    <a href="/" alt="Strona główna"><img src="/gfx/logo.png" id="logo"/></a>
    <div id="right">
      <img src="/gfx/foto.jpg" id="foto"/>
      <?=Stat::getOne(1)->getText() ?>
      <div id="rightsocial">
        <a href="https://www.facebook.com/<?=$fb?>" target="_blank"><img src="/gfx/face.png" alt="Fanpage" /></a>
        <a href="/rss" target="_blank"><img src="/gfx/rss.png" alt="RSS" /></a>
        <div class="right">
          <input type="text" id="search" <?
          if ($par[0] == 'szukaj' || $par[0] == 'tag') {
            $q = @str_replace('+',' ',urldecode($par[1]));
          }
          if ($par[0] == 'szukaj') {
            $q = @str_replace('+',' ',urldecode($par[1]));
            echo 'value="'.$q.'"';
          }
          ?>/>
          <img src="/gfx/search.png" alt="Szukaj" id="search-btn"/>
        </div>
      </div>
    </div>
  </div>
  <? if ($adm): ?>
    <div id="admin-btn">
      <button class="btn btn-sm btn-success">Admin</button>
    </div>
    <div id="admin-menu" class="btn-group-vertical">
      <a class="btn btn-sm btn-default" href="/nowy">Nowy</a>
      <? if (isset($par[1]) && is_numeric($par[1]) && in_array($par[0],array('polityka','opinie','krotko-i-na-temat','okiem-berlinczyka')))
        {echo '<a class="btn btn-sm btn-default" href="edit">Edytuj</a>';} ?>
      <a class="btn btn-sm btn-default" href="/statyczne">Statyczne</a>
      <a class="btn btn-sm btn-default" href="/lib/filemanager/dialog.php" target="_blank">Pliki</a>
      <a class="btn btn-sm btn-default" href="/komenty">Komenty</a>
      <a class="btn btn-sm btn-default" href="/reklamy">Reklamy</a>
      <a class="btn btn-sm btn-default" href="/ankiety">Ankiety</a>
      <a class="btn btn-sm btn-default" href="https://www.google.com/analytics/web/#..." target="_blank">Statystyki</a>
      <a class="btn btn-sm btn-default" href="/out">Wyloguj</a>
    </div>
  <? endif ?>
  <button id="menu-xs" class="btn btn-red">&nbsp;&equiv;&nbsp;</button>
  <nav>
    <div class="container">
      <a type="button" href="/" class="btn btn-red" id="home1">Strona główna</a>
      <a type="button" href="/" class="btn btn-red btn-nopad" id="home2"><img src="/gfx/logomin.png"/></a>
      <a type="button" href="/polityka" class="btn btn-orange">Polityka</a>
      <a type="button" href="/opinie" class="btn btn-yellow">Opinie</a>
      <a type="button" href="/krotko-i-na-temat" class="btn btn-green">Krótko i na temat</a>
      <a type="button" href="/okiem-berlinczyka" class="btn btn-blue">Okiem Berlińczyka</a>
      <a type="button" href="/o-mnie" class="btn btn-purple">O mnie</a>
    </div>
  </nav>
</header>
<div class="container">
  <?
  switch($par[0]) {
    case 'home':
    case 'polityka':
    case 'opinie':
    case 'krotko-i-na-temat':
    case 'okiem-berlinczyka':
      if (isset($par[1]) && $par[1]) {
        if (isset($par[2]) && ($par[2] == 'edit' || $par[2] == 'delete')) {
          include('artform.php');
        } else if (isset($par[1]) && $par[1] == 'page') {
          include('list.php');
        } else {
          include('art.php');
        }
      } else {
        include('list.php');
      }
      break;
    case 'nowy':
      include('artform.php');
      break;
    default:
      if (file_exists($par[0].'.php'))
        include($par[0].'.php');
      else
        include('404.php');
  }
  ?>
</div>
<footer class="well well-sm">
  <div class="container">
    <div class="left">
      &copy; Tomasz Malenkowicz |
      <a href="mailto:<?=$email?>">e-mail</a> |
      <a href="https://www.facebook.com/<?=$fb?>">facebook</a> |
      <a href="/rss">rss</a>
    </div>
    <div class="right"><a href="http://avris.it" target="_blank"><img src="http://avris.it/gfx/favicon.png"/> avris.it</a></div>
  </div>
</footer>
<script async src="/lib/jquery.min.js"></script>
<script async src="/lib/bootstrap.min.js"></script>
<script async src="/lib/masonry.pkgd.min.js"></script>
<script async src="/lib/tinymce/tinymce.min.js"></script>
<script async src="/lib/select2/select2.min.js"></script>
<script async src="/scripts.js"></script>
</body>
</html>
<? ob_end_flush(); ?>