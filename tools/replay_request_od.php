#!/bin/php
<?php
require_once("libs/simple-opendota-php/simple_opendota.php");

$options = getopt("f:");
if(isset($options['f'])) {
  $filename = $options['f'];
} else die();

$settings = json_decode(file_get_contents("rg_settings.json"), true);

$input_cont = file_get_contents($filename) or die("[F] Error while opening file.\n");
$input_cont = str_replace("\r\n", "\n", $input_cont);

if(!empty($settings['odapikey']))
  $opendota = new \SimpleOpenDotaPHP\odota_api(true, "", 1500, $settings['odapikey']);
else
  $opendota = new \SimpleOpenDotaPHP\odota_api(true, "", 1500);

unset($settings);

$matches    = explode("\n", trim($input_cont));

$matches = array_unique($matches);

foreach ($matches as $match) {
    if ($match[0] == "#") continue;

    $opendota->request_match($match);
}

echo "[S] All matches were requested.\n";

?>
