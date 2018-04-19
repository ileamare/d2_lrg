#!/bin/php
<?php
require_once("libs/simple-opendota-php/simple_opendota.php");

$options = getopt("f:");
if(isset($options['f'])) {
  $filename = $options['f'];
} else die();

$input_cont = file_get_contents($filename) or die("[F] Error while opening file.\n");
$input_cont = str_replace("\r\n", "\n", $input_cont);
$opendota = new odota_api(true);

$matches    = explode("\n", trim($input_cont));

$matches = array_unique($matches);

foreach ($matches as $match) {
    if ($match[0] == "#") continue;
    
    $opendota->request_match($match);
}

echo "[S] All matches were requested.\n";

?>
