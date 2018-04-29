#!/bin/php
<?php
$options = getopt("f:");
if(isset($options['f'])) {
  $filename = $options['f'];
  $input_cont = file_get_contents($filename) or die("[F] Error while opening file.\n");
  $input_cont = str_replace("\r\n", "\n", $input_cont);
  $matches    = explode("\n", trim($input_cont));
  $matches = array_unique($matches);
} else die();

foreach ($matches as $match) {
    if ($match[0] == "#") continue;

    //`rm cache/$match.json`;
    if(file_exists("cache/$match.json"))
      unlink("cache/$match.json");
    echo "[ ] RM $match\n";
}

echo "[S] All matches were removed.\n";
?>
