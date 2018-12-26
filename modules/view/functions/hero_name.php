<?php
function hero_portrait($hid) {
  global $meta;
  if (isset($meta->heroes[ $hid ]['tag']))
    return "<img class=\"hero_portrait\" src=\"res/heroes/".$meta['heroes'][ $hid ]['tag'].".png\" alt=\"".$meta['heroes'][ $hid ]['tag']."\" />";
  else return "<img class=\"hero_portrait\" alt=\"undefined\" />";
}

function hero_full($hid) {
  return hero_portrait($hid)." ".hero_name($hid);
}

function hero_name($hid) {
  global $meta;
  if (isset($meta->heroes[ $hid ]['name'])) return $meta->heroes[ $hid ]['name'];
  return "undefined";
}
?>
