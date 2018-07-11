<?php

function rg_generator_pickban($table_id, $context, $context_total_matches, $heroes_flag = true) {
  // TODO $report["random"]["matches_total"]

  uasort($context, function($a, $b) {
    if($a['matches_total'] == $b['matches_total']) return 0;
    else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
  });

  $res =  "<table id=\"$table_id\" class=\"list\"><tr class=\"thead\">".
            ($heroes_flag ? "<th width=\"1%\"></th>" : "").
            "<th onclick=\"sortTable(".(0+$heroes_flag).",'$table_id');\">".locale_string($heroes_flag ? "hero" : "player")."</th>".
            "<th onclick=\"sortTableNum(".(1+$heroes_flag).",'$table_id');\">".locale_string("matches_total")."</th>".
            "<th class=\"separator\"onclick=\"sortTableNum(".(2+$heroes_flag).",'$table_id');\">".locale_string("contest_rate")."</th>".
            "<th onclick=\"sortTableNum(".(3+$heroes_flag).",'$table_id');\">".locale_string("rank")."</th>".
            "<th class=\"separator\" onclick=\"sortTableNum(".(4+$heroes_flag).",'$table_id');\">".locale_string("matches_picked")."</th>".
            "<th onclick=\"sortTableNum(".(5+$heroes_flag).",'$table_id');\">".locale_string("winrate")."</th>".
            "<th class=\"separator\" onclick=\"sortTableNum(".(6+$heroes_flag).",'$table_id');\">".locale_string("matches_banned")."</th>".
            "<th onclick=\"sortTableNum(".(7+$heroes_flag).",'$table_id');\">".locale_string("winrate")."</th></tr>";

  foreach($context as $id => $el) {
    $rank = ($el['matches_picked']*$el['winrate_picked'] + $el['matches_banned']*$el['winrate_banned'])/$context_total_matches;
    $res .=  "<tr>".
            ($heroes_flag ? "<td>".hero_portrait($id)."</td><td>".hero_name($id)."</td>" : "").
            "<td>".$el['matches_total']."</td>".
            "<td class=\"separator\">".number_format($el['matches_total']/$context_total_matches*100,2)."%</td>".
            "<td>".number_format($rank*100,2)."%</td>".
            "<td class=\"separator\">".$el['matches_picked']."</td>".
            "<td>".number_format($el['winrate_picked']*100,2)."%</td>".
            "<td class=\"separator\">".$el['matches_banned']."</td>".
            "<td>".number_format($el['winrate_banned']*100,2)."%</td></tr>";
  }
  unset($oi);
  $res .= "</table>";

  return $res;
}

function rg_generator_uncontested($context, $contested, $heroes_flag = true) {
  foreach($contested as $id => $el) {
    unset($context[$id]);
  }
  $res = "";
  if(sizeof($context)) {
    $res .= "<div class=\"content-text\"><h1>".
      locale_string($heroes_flag ? "heroes_uncontested" : "players_uncontested").
      ": ".sizeof($context)."</h1><div class=\"hero-list\">";

    if ($heroes_tag)
      foreach($context as $el) {
        $res .= "<div class=\"hero\"><img src=\"res/heroes/".$el['tag'].".png\" alt=\"".$el['tag']."\" />".
                "<span class=\"hero_name\">".$el['name']."</span></div>";
      }
    else
      foreach($context as $el) {
        $res .= "<div class=\"hero\"><span class=\"hero_name\">".$el['name']."</span></div>";
      }
    $res .= "</div></div>";
  }

  return $res;
}

?>
