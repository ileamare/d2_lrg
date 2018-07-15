<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_meta_graph($div_id, $context, $context_pickban, $heroes_flag = true) {
  global $visjs_settings, $use_visjs, $meta;

  $use_visjs = true;
  $id = $heroes_flag ? "heroid" : "playerid";

  $res = "<div id=\"$div_id\" class=\"graph\"></div><script type=\"text/javascript\">";

  $nodes = "";

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
      ", label: '".($heroes_flag ? hero_name($elid) : player_name($elid))."'".
      ", title: '".($heroes_flag ? hero_name($elid) : player_name($elid)).", ".
      $el['matches_total']." ".locale_string("total").", ".
      $el['matches_picked']." ".locale_string("matches_picked").", ".
      number_format($el['winrate_picked']*100, 1)." ".locale_string("winrate_picked")."'".
      ", shape:'circularImage', ".
      ($heroes_flag ? "image: 'res/heroes/".$meta['heroes'][$elid]['tag'].".png', " : "").
      "color:{ border:'rgba(".number_format(255-255*$el['winrate_picked'], 0).",124,".
      number_format(255*$el['winrate_picked'], 0).")' }},";
  }
  $res .= "var nodes = [".$nodes."];";

  $nodes = "";
  foreach($context as $combo) {
    $nodes .= "{from: ".$combo[$id.'1'].", to: ".$combo[$id.'2'].", value:".$combo['matches'].", title:\"".$combo['matches']."\", color:{color:'rgba(".
      number_format(255-255*$combo['wins']/$combo['matches'], 0).",124,".
      number_format(255*$combo['wins']/$combo['matches'],0).",1)'}},";
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