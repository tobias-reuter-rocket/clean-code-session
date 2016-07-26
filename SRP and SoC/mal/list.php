<?
$p = 1;
if (isset($par[1]) && $par[1] == 'page' && isset($par[2])) {$p = (int)$par[2];}

$aa = Art::getAll($par[0], ($p-1)*$perpage.','.$perpage );
$ads = Ad::getAll();
$polls = Poll::getAll();

for($i = 1; $i <= count($aa)+1; $i++) {
  if ($i == 2) {echo '<div class="cb"></div><div id="mason">';}

  if (isset($ads[$i]) && count ($ads[$i])) {echo $ads[$i][rand(0,count($ads[$i])-1)]->getAd($i == 1 ? 12 : 4);}
  if (isset($polls[$i]) && count ($polls[$i])) {echo $polls[$i][rand(0,count($polls[$i])-1)]->getPoll($i == 1 ? 12 : 4);}

  if (isset($aa[$i-1])) {echo $aa[$i-1]->getArt($i == 1 ? 12 : 4);}
}
if ($i == 2) {echo '<p class="centerp wide"><b>Brak artykułów...</b></p>';}
if ($i > 2) {echo '</div>';}

$count = Art::doCount($par[0]);
if ($count > $perpage) {
  $pages = ceil($count/$perpage);
  echo '<div class="row centerp"><div class="btn-group">';
  for($i=1; $i<=$pages; $i++) {
    echo '<a type="button" class="btn '.($i == $p ? 'btn-primary' : 'btn-default').'" href="/'.$par[0].'/page/'.$i.'">'.$i.'</a>';
  }
  echo '</div></div>';
}