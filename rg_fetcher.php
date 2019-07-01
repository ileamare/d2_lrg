#!/bin/php
<?php
ini_set('memory_limit', '4000M');

include_once("head.php");
include_once("modules/fetcher/get_patchid.php");
include_once("modules/fetcher/processRules.php");
include_once("modules/fetcher/fetch.php");
include_once("modules/commons/generate_tag.php");
include_once("modules/commons/metadata.php");

include_once("libs/simple-opendota-php/simple_opendota.php");

echo("\nInitialising...\n");

$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);
$conn->set_charset('utf8mb4');
$meta = new lrg_metadata;

$listen = isset($options['L']);

//$stratz_old_api_endpoint = 3707179408;
$stratz_timeout_retries = 2;

$force_adding = isset($options['F']);
$cache_dir = $options['c'] ?? "cache";
if($cache_dir === "NULL") $cache_dir = "";

$use_stratz = isset($options['S']) || isset($options['s']);
$require_stratz = isset($options['S']);
$use_full_stratz = isset($options['Z']);

$request_unparsed = isset($options['R']);

if(!empty($odapikey) && !isset($ignore_api_key))
  $opendota = new \SimpleOpenDotaPHP\odota_api(false, "", 0, $odapikey);
else
  $opendota = new \SimpleOpenDotaPHP\odota_api();

if ($conn->connect_error) die("[F] Connection to SQL server failed: ".$conn->connect_error."\n");

$lrg_input  = "matchlists/".$lrg_league_tag.".list";

$rnum = 1;
$matches = [];
$failed_matches = [];

$scheduled = [];
$first_scheduled = [];

$scheduled_wait_period = (int)($options['w'] ?? 60);

$force_await = isset($options['A']);
//$force_await_retries = (int)$options['A'] ? (int)$options['A'] : 0;

$scheduled_stratz = [];

if (!$listen) {
  $input_cont = file_get_contents($lrg_input);
  $input_cont = str_replace("\r\n", "\n", $input_cont);
  $matches    = explode("\n", trim($input_cont));
  $matches = array_unique($matches);
} else {
  $matches = [];
}

$json = "";
if ($lg_settings['main']['teams']) {
    $t_teams = [];

    $sql = "SELECT teamid, name, tag FROM teams";
    if ($conn->multi_query($sql)) {
      $res = $conn->store_result();

      while ($row = $res->fetch_row()) {
        $t_teams[$row[0]] = [
          "name"  => $row[1],
          "tag"   => $row[2],
          "added" => true
        ];
      }
      $res->free();
    }
}

$t_players = [];
$sql = "SELECT playerid, name FROM players";
if ($conn->multi_query($sql)) {
  $res = $conn->store_result();

  while ($row = $res->fetch_row()) {
    $t_players[(int)$row[0]] = $row[1];
  }
  $res->free();
}

$stdin_flag = false;

if ($listen)
  echo "Listening...\n";

// this code is such a shitshow tbh, but I don't want to fix it, c ya in lrg-simon
while(sizeof($matches) || $listen) {
  if (!$stdin_flag && !empty($first_scheduled) && sizeof($matches) < 2 && !$force_await) {
    asort($first_scheduled);
    $first_requested = reset($first_scheduled);
    if (time() - $first_requested < $scheduled_wait_period)
      $stdin_flag = true;
  }

  if (!sizeof($matches) || $stdin_flag) {
    if (feof(STDIN)) break;
    $match_str = fgets(STDIN);
    if (!$match_str || strlen($match_str) === 0) {
      $stdin_flag = false;
    } else {
      array_unshift($matches, trim($match_str));
      $stdin_flag = false;
    }
  }

  $match = array_shift($matches);

  if (strpos($match, '::')) {
    processRules($match_raw);
  } else $match_raw = $match;

  if($request_unparsed && isset($first_scheduled[$match_raw])) {
    if (time() - $first_scheduled[$match_raw] < $scheduled_wait_period) {
      if (!sizeof($matches)) {
        if ($listen && !$force_await) {
          $stdin_flag = true;
        }
        if (!$stdin_flag) {
          echo "[W] Waiting for $scheduled_wait_period seconds\n";
          sleep($scheduled_wait_period);
        }
      }
      if (!in_array($match, $matches))
        array_push($matches, $match);
      continue;
    }
  }

  $r = fetch($match);
  if ($r === FALSE) { //|| ($force_await && $request_unparsed && $r !== TRUE)) {
    array_push($matches, $match);
  } else if ($r === NULL) {
    $failed_matches[] = $match;
  }
}

if (sizeof($failed_matches)) {
  echo "[R] Unparsed matches: \t".sizeof($failed_matches)."\n";

  echo "[_] Recording failed matches to file...\n";

  $output = implode("\n", $failed_matches);
  $filename = "tmp/failed".time();
  $f = fopen($filename, "w");
  fwrite($f, $output);
  fclose($f);

  echo "[S] Recorded failed matches to $filename\n";
}

echo "[S] Fetch complete.\n";

?>
