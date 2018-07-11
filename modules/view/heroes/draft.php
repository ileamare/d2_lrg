<?php
//echo dirname(__FILE__);
include_once($root."/modules/view/generators/draft.php");

$modules['heroes']['draft'] = "";

function rg_view_generate_heroes_draft() {
  global $report;

  $res = rg_generator_draft("heroes-draft", $report['pickban'], $report['draft'], $report["random"]["matches_total"]);

  return $res;
}

?>
