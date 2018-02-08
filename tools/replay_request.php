#!/bin/php
<?php

$options = getopt("f:");
if(isset($options['f'])) {
  $filename = $options['f'];
} else die();

$input_cont = file_get_contents($filename) or die("[F] Error while opening file.\n");
$input_cont = str_replace("\r\n", "\n", $input_cont);

$matches    = explode("\n", trim($input_cont));

$matches = array_unique($matches);

foreach ($matches as $match) {
    if ($match[0] == "#") continue;

      /*$fields = array(
        //'match_id' => $match,
        );
        $postvars = http_build_query($fields);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.opendota.com/api/request/$match");
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

        $result = curl_exec($ch);

        curl_close($ch);
        */
        echo "[ ] Match $match: ";
        $result = `curl -i -s -X POST https://api.opendota.com/api/request/$match`;
        echo "OK.\n";
        /* I didn't really get into this whole curl thingy, but using it from command line worked just
         * fine, so let it be like that for now. I'll save old code until later on.
         */
}

echo "[S] All matches were requested.\n";

?>
