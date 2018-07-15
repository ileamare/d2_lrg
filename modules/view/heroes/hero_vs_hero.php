<?php

include_once($root."/modules/view/generators/pvp_unwrap_data.php");
include_once($root."/modules/view/generators/pvp_profile.php");

$modules['heroes']['hvh'] = [];

function rg_view_generate_heroes_hvh() {
  global $report, $mod, $parent, $strings, $meta;
  $parent_module = $parent."hvh-";

  $hvh = rg_generator_pvp_unwrap_data($report['hvh'], $report['pickban']);

  $hnames = $meta["heroes"];

  uasort($hnames, function($a, $b) {
    if($a['name'] == $b['name']) return 0;
    else return ($a['name'] > $b['name']) ? 1 : -1;
  });

  foreach($hnames as $hid => $name) {
    $strings['en']["heroid".$hid] = hero_name($hid);
    $res["heroid".$hid] = "";

    if(check_module($parent_module."heroid".$hid)) {
      $res["heroid".$hid] = "<div class=\"content-text\">".locale_string("desc_heroes_hvh")."</div>";
      $res["heroid".$hid] .= rg_generator_pvp_profile("hero-hvh-$hid", $hvh[$hid]);
    }
  }

  return $res;
}

?>
