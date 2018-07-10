<?php

function rg_view_generate_regions_heroes_pickban($region, $reg_report) {
  global $meta;
  global $modules;

  $heroes = $meta['heroes'];

  uasort($reg_report['pickban'], function($a, $b) {
    if($a['matches_total'] == $b['matches_total']) return 0;
    else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
  });

  $res =  "<table id=\"region$region-heroes-pickban\" class=\"list\">
                                        <tr class=\"thead\">
                                          <th onclick=\"sortTable(0,'region$region-heroes-pickban');\">".locale_string("hero")."</th>
                                          <th onclick=\"sortTableNum(1,'region$region-heroes-pickban');\">".locale_string("matches_total")."</th>
                                          <th  class=\"separator\"onclick=\"sortTableNum(2,'region$region-heroes-pickban');\">".locale_string("contest_rate")."</th>
                                          <th onclick=\"sortTableNum(3,'region$region-heroes-pickban');\">".locale_string("outcome_impact")."</th>
                                          <th class=\"separator\" onclick=\"sortTableNum(4,'region$region-heroes-pickban');\">".locale_string("matches_picked")."</th>
                                          <th onclick=\"sortTableNum(5,'region$region-heroes-pickban');\">".locale_string("winrate")."</th>
                                          <th class=\"separator\" onclick=\"sortTableNum(6,'region$region-heroes-pickban');\">".locale_string("matches_banned")."</th>
                                          <th onclick=\"sortTableNum(7,'region$region-heroes-pickban');\">".locale_string("winrate")."</th>
                                        </tr>";
  foreach($reg_report['pickban'] as $hid => $hero) {
    unset($heroes[$hid]);
    $oi = ($hero['matches_picked']*$hero['winrate_picked'] + $hero['matches_banned']*$hero['winrate_banned'])/$reg_report["main"]["matches"];
    $res .=  "<tr>
                                          <td>".($hid ? hero_full($hid) : "")."</td>
                                          <td>".$hero['matches_total']."</td>
                                          <td class=\"separator\">".number_format($hero['matches_total']/$reg_report["main"]["matches"]*100,2)."%</td>
                                          <td>".number_format($oi*100,2)."%</td>
                                          <td class=\"separator\">".$hero['matches_picked']."</td>
                                          <td>".number_format($hero['winrate_picked']*100,2)."%</td>
                                          <td class=\"separator\">".$hero['matches_banned']."</td>
                                          <td>".number_format($hero['winrate_banned']*100,2)."%</td>
                                        </tr>";
  }
  unset($oi);
  $res .= "</table>";

  if(sizeof($heroes)) {
    $res .= "<div class=\"content-text\"><h1>".locale_string("heroes_uncontested").": ".sizeof($heroes)."</h1><div class=\"hero-list\">";

    foreach($heroes as $hero) {
      $res .= "<div class=\"hero\"><img src=\"res/heroes/".$hero['tag'].
          ".png\" alt=\"".$hero['tag']."\" /><span class=\"hero_name\">".
          $hero['name']."</span></div>";
    }
    $res .= "</div></div>";
  }
  $res .= "<div class=\"content-text\">".locale_string("desc_heroes_pickban")."</div>";

  return $res;
}


?>
