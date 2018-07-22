<?php
$modules['records'] = "";

function rg_view_generate_records() {
  global $report, $root;
  include("generators/records.php");

  $res = rg_generator_records($report['records']);

  $res .= "<div class=\"content-text\">".locale_string("desc_records")."</div>";

  return $res;
}

?>
