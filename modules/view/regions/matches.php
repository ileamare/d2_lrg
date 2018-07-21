<?php

$res["region".$region]["matches"] = "";

if(check_module($modstr."-matches")) {
  $res["region".$region]['matches'] = "<div class=\"content-text\">".locale_string("desc_matches")."</div>";
  $res["region".$region]['matches'] .= "<div class=\"content-cards\">";
  foreach($reg_report['matches'] as $matchid => $match) {
    $res["region".$region]['matches'] .= match_card($matchid);
  }
  $res["region".$region]['matches'] .= "</div>";
}

?>
