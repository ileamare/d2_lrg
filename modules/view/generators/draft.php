<?php
include_once($root."/modules/view/functions/ranking.php");

function rg_generator_draft($table_id, $context_pickban, $context_draft, $context_total_matches, $hero_flag = true) {
  $res = ""; $draft = [];
  $id_name = $hero_flag ? "heroid" : "playerid";

  if(!sizeof($context_pickban)) return "";

  for ($i=0; $i<2; $i++) {
    $type = $i ? "pick" : "ban";
    $max_stage = 1;
    if(!isset($context_draft[$i])) continue;
    foreach($context_draft[$i] as $stage_num => $stage) {
      if ($stage_num > $max_stage) $max_stage = $stage_num;
      foreach($stage as $el) {
        if(!isset($draft[ $el[$id_name] ])) {
          if($stage_num > 1) {
            for($j=1; $j<$stage_num; $j++) {
              $draft[ $el[$id_name] ][$j] = array ("pick" => 0, "pick_wr" => 0, "ban" => 0, "ban_wr" => 0 );
            }
          }
        }

        if(!isset($draft[ $el[$id_name] ][$stage_num]))
          $draft[ $el[$id_name] ][$stage_num] = array ("pick" => 0, "pick_wr" => 0, "ban" => 0, "ban_wr" => 0 );
        $draft[ $el[$id_name] ][$stage_num][$type] = $el['matches'];
        $draft[ $el[$id_name] ][$stage_num][$type."_wr"] = $el['winrate'];
      }
    }
  }

  foreach($context_pickban as $k => $v) {
    if(isset($v['winrate_picked'])) break;

    if($context_pickban[$k]['matches_picked'])
      $context_pickban[$k]['winrate_picked'] = $context_pickban[$k]['wins_picked'] / $context_pickban[$k]['matches_picked'];
    else
      $context_pickban[$k]['winrate_picked'] = 0;

    if($context_pickban[$k]['matches_banned'])
      $context_pickban[$k]['winrate_banned'] = $context_pickban[$k]['wins_banned'] / $context_pickban[$k]['matches_banned'];
    else
      $context_pickban[$k]['winrate_banned'] = 0;
  }

  $ranks = [];
  $compound_ranking_sort = function($a, $b) use ($context_total_matches) {
    return compound_ranking_sort($a, $b, $context_total_matches);
  };

  uasort($context_pickban, $compound_ranking_sort);

  $increment = 100 / sizeof($context_pickban); $i = 0;

  foreach ($context_pickban as $id => $el) {
    $oi = ($el['matches_picked']*$el['winrate_picked'] + $el['matches_banned']*$el['winrate_banned'])
      / $context_total_matches * 100;
    if(isset($last) && $oi == $last) {
      $i++;
      $ranks[$id] = $last_rank;
    } else
      $ranks[$id] = 100 - $increment*$i++;
    $last = $oi; $last_rank = $ranks[$id];
  }
  unset($last);

  $ranks_stages = [];
  for ($i = 1; $i <= $max_stage; $i++) {
    $ranks_stages[$i] = [];
    $scores = [];
    foreach ($draft as $id => $stages) {
      if(isset($stages[$i]) && ($stages[$i]['pick']+$stages[$i]['ban']))
        $scores[$id] = [
          "matches_picked" => $stages[$i]['pick'],
          "winrate_picked" => $stages[$i]['pick_wr'],
          "matches_banned" => $stages[$i]['ban'],
          "winrate_banned" => $stages[$i]['ban_wr'],
        ];

    }
    uasort($scores, $compound_ranking_sort);

    $increment = 100 / sizeof($scores); $j = 0;

    foreach ($scores as $id => $el) {
      if(isset($last) && $el == $last) {
        $j++;
        $ranks_stages[$i][$id] = $last_rank;
      } else
        $ranks_stages[$i][$id] = 100 - $increment*$j++;
      $last = $el;
      $last_rank = $ranks_stages[$i][$id];
    }
    unset($last);
  }
  unset($last_rank);

  foreach ($draft as $id => $stages) {
    $draftline = "";

    $stages_passed = 0;

    foreach($stages as $stage_num => $stage) {
      if($max_stage > 1) {
        $draftline .= "<td class=\"separator\">".(isset($ranks_stages[$stage_num][$id]) ? number_format($ranks_stages[$stage_num][$id],2) : "-")."</td>";
        if($stage['pick'])
          $draftline .= "<td>".$stage['pick']."</td><td>".number_format($stage['pick_wr']*100, 2)."%</td>";
        else
          $draftline .= "<td>&zwnj;-</td><td>&zwnj;-</td>";

        if($stage['ban'])
          $draftline .= "<td>".$stage['ban']."</td><td>".number_format($stage['ban_wr']*100, 2)."%</td>";
        else
          $draftline .= "<td>&zwnj;-</td><td>&zwnj;-</td>";
      }

      $stages_passed++;
    }

    if($stages_passed < $max_stage) {
      for ($i=$stages_passed; $i<$max_stage; $i++)
        $draftline .= "<td class=\"separator\">&zwnj;-</td><td>&zwnj;-</td><td>&zwnj;-</td><td>&zwnj;-</td><td>&zwnj;-</td>";
    }

    $draft[$id] = array ("out" => "", "matches" => $context_pickban[$id]['matches_total']);
    if($hero_flag)
      $draft[$id]['out'] .= "<td>".hero_portrait($id)."</td><td>".hero_name($id)."</td>";
    else
      $draft[$id]['out'] .= "<td>".player_name($id)."</td>";

    $draft[$id]['out'] .= "<td>".$context_pickban[$id]['matches_total']."</td>";
    $draft[$id]['out'] .= "<td>".number_format($ranks[$id], 2)."</td>";

    if($context_pickban[$id]['matches_picked'])
      $draft[$id]['out'] .= "<td>".$context_pickban[$id]['matches_picked']."</td><td>".number_format($context_pickban[$id]['winrate_picked']*100, 2)."%</td>";
    else
      $draft[$id]['out'] .= "<td>-</td><td>-</td>";

    if($context_pickban[$id]['matches_banned'])
      $draft[$id]['out'] .= "<td>".$context_pickban[$id]['matches_banned']."</td><td>".number_format($context_pickban[$id]['winrate_banned']*100, 2)."%</td>";
    else
      $draft[$id]['out'] .= "<td>-</td><td>-</td>";

    $draft[$id]['out'] .= $draftline."</tr>";
  }


  uasort($draft, function($a, $b) {
    if($a['matches'] == $b['matches']) return 0;
    else return ($a['matches'] < $b['matches']) ? 1 : -1;
  });

  $res .= "<table id=\"$table_id\" class=\"list wide sortable\"><thead><tr class=\"overhead\"><th width=\"11%\"".($hero_flag ? " colspan=\"2\"" : "")."></th>".
          "<th colspan=\"6\">".locale_string("total")."</th>";
  $heroline = "<tr>".
                ($hero_flag ? "<th class=\"sorter-no-parser\" width=\"1%\"></th>" : "").
                "<th data-sorter=\"text\">".locale_string($hero_flag ? "hero" : "player")."</th>".
                "<th data-sorter=\"digit\">".locale_string("matches_s")."</th>".
                "<th data-sorter=\"digit\">".locale_string("rank")."</th>".
                "<th data-sorter=\"digit\">".locale_string("picks_s")."</th>".
                "<th data-sorter=\"digit\">".locale_string("winrate_s")."</th>".
                "<th data-sorter=\"digit\">".locale_string("bans_s")."</th>".
                "<th data-sorter=\"digit\">".locale_string("winrate_s")."</th>";

  if($max_stage > 1)
    for($i=1; $i<=$max_stage; $i++) {
      $res .= "<th class=\"separator\" colspan=\"5\" data-sorter=\"digit\">".locale_string("stage")." $i</th>";
      $heroline .= "<th class=\"separator\" data-sorter=\"digit\">".locale_string("rank")."</th>".
                  "<th data-sorter=\"digit\">".locale_string("picks_s")."</th>".
                  "<th data-sorter=\"digit\">".locale_string("winrate_s")."</th>".
                  "<th data-sorter=\"digit\">".locale_string("bans_s")."</th>".
                  "<th data-sorter=\"digit\">".locale_string("winrate_s")."</th>";
    }
  $res .= "</tr>".$heroline."</tr></thead><tbody>";

  unset($heroline);

  foreach($draft as $hero)
    $res .= $hero['out'];

  $res .= "</tbody></table>";

  if ($hero_flag)
    $res = rg_draft_accuracy_test($context_pickban, $context_draft).$res;

  return $res;
}

function rg_draft_accuracy_test(&$context_pickban, &$context_draft) {
  $ratios = [];
  foreach ($context_draft[1][3] as $dr) {
    $total = $context_pickban[ $dr['heroid'] ]['matches_total'];
    $stage = $dr['matches'];
    $ratios[] = $stage/$total;
  }
  $res = array_sum($ratios) / sizeof($ratios);
  if ($res < 0.15) return "<div class=\"content-text\">".locale_string("desc_inaccurate_draft")."</div>";
  return "";
}

?>
