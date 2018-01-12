#!/bin/php
<?php

$options = getopt("f:");
if(isset($options['f'])) {
  $filename = $options['f'];
} else die();

$input_cont = file_get_contents($filename);
$input_cont = str_replace("\r\n", "\n", $input_cont);

$matches    = explode("\n", trim($input_cont));

$matches = array_unique($matches);


# https://api.opendota.com/api/matches/{match_id}

foreach ($matches as $match) {
    if ($match[0] == "#") continue;

      $fields = array(
        //'match_id' => $match,
        );
        /*$postvars = http_build_query($fields);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.opendota.com/api/request/$match");
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

        $result = curl_exec($ch);

        curl_close($ch);
        */
        $result = `curl -i -X POST https://api.opendota.com/api/request/$match`;
        //var_dump($result);
}

echo "[S] Fetch complete.\n";

?>
