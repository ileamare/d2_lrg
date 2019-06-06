<?php 

// rules format: matchid::type:id:replacer::...
// types: player, pslot, team, side
const RULES_TYPES = [
  "player",
  "pslot",
  "team",
  "side",
];

function processRules(&$matchstring) {
  $match_rules = [];
  if (strpos($matchstring, "::")) {
    $rules_raw = explode("::", $matchstring);
    $matchstring = array_shift($rules_raw);
    $rules = [];
    foreach($rules_raw as $rule) {
      if (strpos($rule, ":") === FALSE) continue;
      $rule = explode(":", strtolower($rule) );
      if (sizeof($rule) < 3 || !in_array($rule[0], RULES_TYPES)) continue;

      if (!isset($match_rules[ $rule[0] ]))
        $match_rules[ $rule[0] ] = [];

      $match_rules[ $rule[0] ][ $rule[1] ] = $rule[2];
    }
  }

  return $match_rules;
}
?>