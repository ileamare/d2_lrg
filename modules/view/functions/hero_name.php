<?php
function hero_portrait($hid) {
  global $meta;
  return "<img class=\"hero_portrait\" src=\"res/heroes/".$meta['heroes'][ $hid ]['tag'].
    ".png\" alt=\"".$meta['heroes'][ $hid ]['tag']."\" />";
}

function hero_full($hid) {
  return hero_portrait($hid)." ".hero_name($hid);
}

function hero_name($hid) {
  global $meta;
  return $meta['heroes'][ $hid ]['name'];
}
?>
