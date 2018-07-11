<?php

function rg_generator_draft($table_id, $context_pickban, $context_draft, $context_total_matches, $hero_flag = true) {
  $res = ""; $draft = [];
  $id_name = $hero_flag ? "heroid" : "playerid";

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

  $ranks = [];
  uasort($context_pickban, function($a, $b) use ($context_total_matches) {
    $a_oi_rank = ($a['matches_picked']*$a['winrate_picked'] + $a['matches_banned']*$a['winrate_banned'])
      / $context_total_matches * 100;
    $b_oi_rank = ($b['matches_picked']*$b['winrate_picked'] + $b['matches_banned']*$b['winrate_banned'])
      / $context_total_matches * 100;
    if($a_oi_rank == $b_oi_rank) return 0;
    else return ($a_oi_rank < $b_oi_rank) ? 1 : -1;
  });

  $increment = 100 / sizeof($context_pickban); $i = 0;

  foreach ($context_pickban as $id => $el) {
    $ranks[$id] = 100 - $increment*$i++;
  }

  $ranks_stages = [];
  for ($i = 1; $i <= $max_stage; $i++) {
    $ranks_stages[$i] = [];
    $oi = [];
    foreach ($draft as $id => $stages) {
      if(isset($stages[$i]))
        $oi[$id] = ($stages[$i]['pick']*$stages[$i]['pick_wr']+$stages[$i]['ban']*$stages[$i]['ban_wr'])/$context_total_matches*100;
    }
    uasort($oi, function($a, $b) {
      if($a == $b) return 0;
      else return ($a < $b) ? 1 : -1;
    });

    $increment = 100 / sizeof($oi); $j = 0;

    foreach ($oi as $id => $el) {
      $ranks_stages[$i][$id] = 100 - $increment*$j++;
    }
  }

  foreach ($draft as $id => $stages) {
    $draftline = "";

    $stages_passed = 0;

    foreach($stages as $stage_num => $stage) {
      if($max_stage > 1) {
        $draftline .= "<td class=\"separator\">".(isset($ranks_stages[$stage_num][$id]) ? number_format($ranks_stages[$stage_num][$id],2) : "")."</td>";
        if($stage['pick'])
          $draftline .= "<td>".$stage['pick']."</td><td>".number_format($stage['pick_wr']*100, 2)."%</td>";
        else
          $draftline .= "<td>-</td><td>-</td>";

        if($stage['ban'])
          $draftline .= "<td>".$stage['ban']."</td><td>".number_format($stage['ban_wr']*100, 2)."%</td>";
        else
          $draftline .= "<td>-</td><td>-</td>";
      }

      $stages_passed++;
    }

    if($stages_passed < $max_stage) {
      for ($i=$stages_passed; $i<$max_stage; $i++)
        $draftline .= "<td class=\"separator\">-</td><td>-</td><td>-</td><td>-</td><td>-</td>";
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

  $res .= "<table id=\"$table_id\" class=\"list wide\"><tr class=\"thead overhead\"><th width=\"11%\"".($hero_flag ? " colspan=\"2\"" : "")."></th>".
          "<th colspan=\"6\">".locale_string("total")."</th>";
  $heroline = "<tr class=\"thead\">".
                ($hero_flag ? "<th width=\"1%\"></th>" : "").
                "<th onclick=\"sortTable(".(0+$hero_flag).",'$table_id');\">".locale_string($hero_flag ? "hero" : "player")."</th>".
                "<th onclick=\"sortTableNum(".(1+$hero_flag).",'$table_id');\">".locale_string("matches_s")."</th>".
                "<th onclick=\"sortTableNum(".(2+$hero_flag).",'$table_id');\">".locale_string("rank")."</th>".
                "<th onclick=\"sortTableNum(".(3+$hero_flag).",'$table_id');\">".locale_string("picks_s")."</th>".
                "<th onclick=\"sortTableNum(".(4+$hero_flag).",'$table_id');\">".locale_string("winrate_s")."</th>".
                "<th onclick=\"sortTableNum(".(5+$hero_flag).",'$table_id');\">".locale_string("bans_s")."</th>".
                "<th onclick=\"sortTableNum(".(6+$hero_flag).",'$table_id');\">".locale_string("winrate_s")."</th>";

  if($max_stage > 1)
    for($i=1; $i<=$max_stage; $i++) {
      $res .= "<th class=\"separator\" colspan=\"5\">".locale_string("stage")." $i</th>";
      $heroline .= "<th onclick=\"sortTableNum(".(1+5*$i+1+$hero_flag).",'$table_id');\" class=\"separator\">".locale_string("rank")."</th>".
                  "<th onclick=\"sortTableNum(".(1+5*$i+2+$hero_flag).",'$table_id');\">".locale_string("picks_s")."</th>".
                  "<th onclick=\"sortTableNum(".(1+5*$i+3+$hero_flag).",'$table_id');\">".locale_string("winrate_s")."</th>".
                  "<th onclick=\"sortTableNum(".(1+5*$i+4+$hero_flag).",'$table_id');\">".locale_string("bans_s")."</th>".
                  "<th onclick=\"sortTableNum(".(1+5*$i+5+$hero_flag).",'$table_id');\">".locale_string("winrate_s")."</th>";
    }
  $res .= "</tr>".$heroline."</tr>";

  unset($heroline);

  foreach($draft as $hero)
    $res .= $hero['out'];

  $res .= "</table>";

  return $res;
}

?>
