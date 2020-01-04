<?php 

$meta = new lrg_metadata;
$endpoints = [];

if (isset($report)) {
  include_once("modules/view/__post_load.php");

  if(empty($mod)) $mod = "";

  include_once(__DIR__ . "modules/records.php");
  include_once(__DIR__ . "modules/participants.php");
  include_once(__DIR__ . "modules/matches.php");
  // overview
  // combos
  // pickban
  // draft
  // positions
  // party_graph
  // meta_graph
  // pvp
  // hvh
  // haverages
  // teams-cards
  // teams-t123-roster

  $endpoints['__fallback'] = function() use (&$endpoints) {
    return $endpoints['overview'];
  };
} else {
  // basic response
  // list of matches + category
  // metadata
  // locale
  // cache
}

$mod = str_replace("/", "-", $mod);
$modline = array_reverse(explode("-", $mod));
$vars = [];

foreach ($modline as $ml) {
  if (!isset($endp) && isset($endpoints[$ml])) {
    $endp = $endpoints[$ml];
    break;
  }
  if (strpos($ml, "region") && $ml != "regions") $vars['region'] = (int)str_replace("region", "", $ml);
  if (strpos($ml, "position_")) $vars['position'] = str_replace("position_", "", $ml);
  if (strpos($ml, "heroid")) $vars['heroid'] = (int)str_replace("heroid", "", $ml);
  if (strpos($ml, "playerid")) $vars['playerid'] = (int)str_replace("playerid", "", $ml);
  if (strpos($ml, "team") && $ml != "teams") $vars['team'] = (int)str_replace("team", "", $ml);
}
if (empty($endp))
  $endp = $endpoints['__fallback']();

try {
  $result = $endp($modline, $vars, $report);
} catch (\Throwable $e) {
  $result = [
    'error' => $e->getMessage()
  ];
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
//header("Access-Control-Allow-Headers: X-Requested-With");
header('Access-Control-Allow-Headers: token, Content-Type');


echo json_encode($response, (isset($_REQUEST['pretty']) ? JSON_PRETTY_PRINT : 0) 
  | JSON_INVALID_UTF8_SUBSTITUTE 
  | JSON_UNESCAPED_UNICODE
  //| JSON_THROW_ON_ERROR
);
