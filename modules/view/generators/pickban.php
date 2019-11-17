<?php
include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");
include_once($root."/modules/view/functions/ranking.php");

function rg_generator_pickban($table_id, $context, $context_total_matches, $heroes_flag = true) {
  if(!sizeof($context)) return "";

  uasort($context, function($a, $b) {
    if($a['matches_total'] == $b['matches_total']) return 0;
    else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
  });

  $res =  "<table id=\"$table_id\" class=\"list sortable\"><thead><tr>".
            ($heroes_flag ? "<th class=\"sorter-no-parser\" width=\"1%\"></th>" : "").
            "<th data-sortInitialOrder=\"asc\">".locale_string($heroes_flag ? "hero" : "player")."</th>".
            "<th>".locale_string("matches_total")."</th>".
            "<th class=\"separator\">".locale_string("contest_rate")."</th>".
            "<th>".locale_string("rank")."</th>".
            "<th class=\"separator\">".locale_string("matches_picked")."</th>".
            "<th>".locale_string("winrate")."</th>".
            "<th class=\"separator\">".locale_string("matches_banned")."</th>".
            "<th>".locale_string("winrate")."</th></tr></thead>";

  $ranks = [];
  $context_copy = $context;

  $compound_ranking_sort = function($a, $b) use ($context_total_matches) {
    return compound_ranking_sort($a, $b, $context_total_matches);
  };

  uasort($context, $compound_ranking_sort);

  $increment = 100 / sizeof($context); $i = 0;

  foreach ($context as $id => $el) {
    $ranks[$id] = 100 - $increment*$i++;
  }
  unset($context_copy);

  foreach($context as $id => $el) {
    $res .=  "<tr>".
            ($heroes_flag ? "<td>".hero_portrait($id)."</td><td>".hero_name($id)."</td>" : "<td>".player_name($id)."</td>").
            "<td>".$el['matches_total']."</td>".
            "<td class=\"separator\">".number_format($el['matches_total']/$context_total_matches*100,2)."%</td>".
            "<td>".number_format($ranks[$id],2)."</td>".
            "<td class=\"separator\">".$el['matches_picked']."</td>".
            "<td>".number_format($el['winrate_picked']*100,2)."%</td>".
            "<td class=\"separator\">".$el['matches_banned']."</td>".
            "<td>".number_format($el['winrate_banned']*100,2)."%</td></tr>";
  }
  unset($oi);
  $res .= "</table>";

  return $res;
}

function rg_generator_uncontested($context, $contested, $small = false, $heroes_flag = true) {
  if ($heroes_flag) {
    // uncontested
    // unpicked : matches_picked 0
    // least contested : uasort, get the last 10%
    if (sizeof($contested) == sizeof($context)) {
      $context_tmp = [];
      foreach ($contested as $tid => $v) {
        if ($v['matches_picked'] == 0)
          $context_tmp[$tid] = $context[$tid];
      }

      if (sizeof($context_tmp) == 0) {
        $loc = "least_contested";
        $sz = floor(sizeof($contested) * 0.1);
        uasort($contested, function($a, $b) {
          if ($a['matches_total'] == $b['matches_total']) return 0;
          return $a['matches_total'] < $b['matches_total'] ? -1 : 1;
        });
        $i = 0;
        foreach ($contested as $tid => $v) {
          $context_tmp[$tid] = $context[$tid];
          $i++;
          if ($i == $sz) break;
        }
      } else {
        $loc = "heroes_unpicked";
      }
      $context = $context_tmp;
    } else {
      foreach($contested as $id => $el) {
        unset($context[$id]);
      }
      $loc = "heroes_uncontested";
    }
  } else {
    foreach($contested as $id => $el) {
      unset($context[$id]);
    }
    $loc = "players_uncontested";
  }

  $res = "";
  if(sizeof($context)) {
    $res .= "<div class=\"content-text ".($small ? "small-heroes" : "")."\"><h1>".
      locale_string($loc).
      ": ".sizeof($context)."</h1><div class=\"hero-list\">";

    if ($heroes_flag)
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
