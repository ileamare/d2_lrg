<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_meta_graph($div_id, $context, $context_pickban, $heroes_flag = true) {
  //if(!sizeof($context)) return "";
  global $visjs_settings, $use_visjs, $meta;

  $use_visjs = true;
  $id = $heroes_flag ? "heroid" : "playerid";

  $res = "<div id=\"$div_id\" class=\"graph\"></div><script type=\"text/javascript\">";

  $nodes = "";

  $max_wr = 0;
  foreach($context as $combo) {
    $diff = abs(($combo['winrate'] ?? $combo['wins']/$combo['matches'])-0.5);
    $max_wr = $diff > $max_wr ? $diff : $max_wr;
  }
  $max_wr *= 2;

  if($heroes_flag) {
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

    $counter = 0; $endp = sizeof($context_pickban)*0.35;

    uasort($context_pickban, function($a, $b) {
      if($a['matches_total'] == $b['matches_total']) return 0;
      else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
    });

    foreach($context_pickban as $elid => $el) {
      if($counter++ >= $endp && !has_pair($elid, $context)) {
          continue;
      }
      $nodes .= "{id: $elid, value: ".$el['matches_total'].
        ", label: '".addslashes(hero_name($elid))."'".
        ", title: '".addslashes(hero_name($elid)).", ".
        $el['matches_total']." ".locale_string("total").", ".
        $el['matches_picked']." ".locale_string("matches_picked").", ".
        number_format($el['winrate_picked']*100, 1)." ".locale_string("winrate_picked")."'".
        ", shape:'circularImage', ".
        "image: 'res/heroes/".$meta['heroes'][$elid]['tag'].".png', ".
        "color:{ border:'rgba(".number_format(255-255*$el['winrate_picked'], 0).",124,".
        number_format(255*$el['winrate_picked'], 0).")' }},";
    }
  } else {
    foreach($context_pickban as $elid => $el) {
      if (!has_pair($elid, $context)) continue;
      $wr = $el['won'] / $el['matches'];
      $nodes .= "{id: $elid, value: ".$el['matches'].
        ", label: '".addslashes(player_name($elid))."'".
        ", title: '".addslashes(player_name($elid)).", ".
        $el['matches']." ".locale_string("total").", ".
        number_format($wr*100, 1)." ".locale_string("winrate")."', ".
        "color:{ border:'rgba(".number_format(255-255*$wr, 0).",124,".
        number_format(255*$wr, 0).")' }},";
    }
  }
  $res .= "var nodes = [".$nodes."];";

  $nodes = "";
  foreach($context as $combo) {
    if(!isset($combo['winrate']))
      $combo['winrate'] = $combo['wins']/$combo['matches'];
    $nodes .= "{from: ".$combo[$id.'1'].", to: ".$combo[$id.'2'].", value:".$combo['matches'].", title:\"".
      $combo['matches']." ".locale_string("matches").", ".number_format($combo['winrate']*100, 2)."% ".locale_string("winrate").
      (isset($combo['dev_pct']) ? ", ".number_format($combo['dev_pct']*100, 2)."% ".locale_string("deviation") : "")."\", color:{color:'rgba(".
      round(126-255*($max_wr ? ($combo['winrate']-0.5)/$max_wr : 0)).",124,".
      round(126+255*($max_wr ? ($combo['winrate']-0.5)/$max_wr : 0)).",1)'}},";
  }

  $res .= "var edges = [".$nodes."];";

  $res .= "var container = document.getElementById('$div_id');\n".
          "var data = { nodes: nodes, edges: edges};\n".
          "var options={ $visjs_settings };\n".
          "var network = new vis.Network(container, data, options);\n".
          "</script>";
  return $res;
}

?>
