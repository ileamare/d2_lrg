<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_overview_positions_section($tables_prefix, $context, &$pickban, $count = 5, $sort_param = "matches") {
  if(!sizeof($context)) return "";

  for ($i=1; $i>=0 && !isset($keys); $i--) {
    for ($j=1; $j<6 && $j>0; $j++) {
      if (!$i) { $j = 0; }
      if(isset($context[$i][$j][0])) {
        $keys = array_keys($context[$i][$j][0]);
        break;
      }
      if (!$i) { break; }
    }
  }

  for ($i=1; $i>=0; $i--) {
    for ($j=1; $j<6 && $j>0; $j++) {
      if (!$i) { $j = 0; }
      if(sizeof($context[$i][$j]))
        $position_overview_template = array("matches" => 0, "wr" => 0);
      if (!$i) { break; }
    }
  }

  $overview = [];
  $ranks = [];

  for ($i=1; $i>=0; $i--) {
    for ($j=1; $j<4 && $j>0; $j++) {
      if (!$i) { $j = 0; }

      if (!sizeof($context[$i][$j])) continue;

      $ranks[$i][$j] = [];
      $context_copy = $context[$i][$j];
      $total_matches = 0;
      foreach ($context_copy as $c) {
        if ($total_matches < $c['matches_s']) $total_matches = $c['matches_s'];
      }
  
      uasort($context_copy, function($a, $b) use ($total_matches) {
        return positions_ranking_sort($a, $b, $total_matches);
      });
  
      $increment = 100 / sizeof($context_copy); $k = 0;
  
      foreach ($context_copy as $id => $el) {
        if(isset($last) && $el['matches_s'] == $last['matches_s'] && $el['winrate_s'] == $last['winrate_s']) {
          $k++;
          $ranks[$i][$j][$id] = $last_rank;
        } else
          $ranks[$i][$j][$id] = 100 - $increment*$k++;
        $last = $el;
        $last_rank = $ranks[$i][$j][$id];
      }
      unset($last);
      unset($context_copy);

      $overview["$i.$j"] = [];

      foreach($context[$i][$j] as $id => $el) {
        if (!isset($overview["$i.$j"][$id])) $overview["$i.$j"][$id] = [];

        $overview["$i.$j"][ $id ]['matches'] = $el['matches_s'];
        $overview["$i.$j"][ $id ]['wr'] = $el['winrate_s'];
        $overview["$i.$j"][ $id ]['rank'] = $ranks[$i][$j][$id]; 
      }

      if (!$i) { break; }
    }
  }

  $res = "<div class=\"small-list-wrapper\">";

  foreach ($overview as $k => $heroes) {
    $res .= "<table id=\"$tables_prefix\" class=\"list list-small\"><caption>".locale_string("position_$k")."</caption><thead><tr>".
      "<th></th>".
      "<th>".locale_string("hero")."</th>".
      "<th>".locale_string("matches_s")."</th>".
      "<th>".locale_string("rank")."</th>".
      //"<th>".locale_string("ratio")."</th>".
      "<th>".locale_string("winrate_s")."</th></tr></thead><tbody>";
    
    uasort($heroes, function($a, $b) use ($sort_param) {
      return $b[$sort_param] <=> $a[$sort_param];
    });

    $i = 0;
    foreach ($heroes as $id => $v) {
      if (++$i > $count) break;
      $res .= "<tr><td>".hero_portrait($id)."</td><td>".hero_name($id)."</td>".
        "<td>".$v['matches']."</td>".
        "<td>".number_format($v['rank'],1)."</td>".
        //"<td>".number_format($v['matches']*100/$pickban[$id]['matches_total'],1)."%</td>".
        "<td>".number_format($v['wr']*100,1)."%</td></tr>";
    }

    $res .= "</tbody></table>";
  }
  $res .= "</div>";

  return $res;
}

?>
