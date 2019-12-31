<?php 

if (isset($report)) {
  $output = "";

  $meta = new lrg_metadata;

  include_once("modules/view/__post_load.php");

  $modules = [];

  if(empty($mod)) $mod = "";
  
  // TODO: mod.split(-)
  // from latest: endpoint
  // if such endpoint doesn't exist: move to another
  // use mod as parameters object

  $endpoints = [];


  // overview
  // fallback
  // records
  // pickban
  // matches (list/cards)
  // participants (teams/players)

  // summary
  // 
} else {
  // basic response
  // list of matches + category
}

$modline = array_reverse(explode("-", $mod));

foreach ($modline as $ml) {
  if (isset($endpoints[$ml])) {
    $endp = $endpoints[$ml];
    break;
  } else {
    continue;
  }
}
if (empty($endp))
  $endp = $endpoints['fallback']();

try {
  $result = $endp($modline, $report);
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
