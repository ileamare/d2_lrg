<?php
$init = true;
include_once("head.php");
include_once("modules/commons/migrate_params.php");

if (!file_exists("templates/default.json")) die("[F] No default league template found, exitting.");
$lg_settings = json_decode(file_get_contents("templates/default.json"), true);

if(isset($argv)) {
  $options = getopt("ST:l:N:D:I:", [ "settings", "template", "league", "name", "desc", "id" ]);

  if(isset($options['T'])) {
    if (file_exists("templates/".$options['T'].".json")) {
      $tmp = json_decode(file_get_contents("templates/".$options['T'].".json"), true);
      migrate_params($lg_settings, $tmp);
      unset($tmp);
    }
  }

  if(isset($options['l'])) $lg_settings['league_tag'] = $options['l'];
  else $lg_settings['league_tag'] = readline_rg(" >  League tag: ");

  if(isset($options['N'])) $lg_settings['league_name'] = $options['N'];
  else $lg_settings['league_name'] = readline_rg(" >  League name: ");

  if(isset($options['D'])) $lg_settings['league_desc'] = $options['D'];
  else $lg_settings['league_desc'] = readline_rg(" >  League description: ");

  if(isset($options['I'])) {
    $lg_settings['league_id'] = (int)$options['I'];
    if (!$lg_settings['league_id']) $lg_settings['league_id'] = null;
  }

  if(isset($options['S'])) {
    echo "[ ] Enter parameters below in format \"Parameter = value\".\n    Divide parameters subcategories by a \".\", empty line to exit.\n";
    while (!empty($st = readline_rg(" >  "))) {
      $st = explode("=", trim($st));
      $val = &$lg_settings;
      $st[0] = explode(".", $st[0]);

      foreach ($st[0] as $level) {
        if(!isset($val[$level])) $val[$level] = array();
        $val = &$val[$level];
      }
      $val = $st[1];
    }
    unset($st);
    unset($val);
  }
}

$lg_settings['version'] = $lrg_version;

$f = fopen("leagues/".$lg_settings['league_tag'].".json", "w") or die("[F] Couldn't open file to save results. Check working directory for `reports` folder.\n");
fwrite($f, json_encode($lg_settings, JSON_PRETTY_PRINT));
fclose($f);

echo "[ ] Opening matchlist file\n";

$f = fopen("matchlists/".$lg_settings['league_tag'].".list", "w") or die("[F] Couldn't open file to save results. Check working directory for `reports` folder.\n");
if($lg_settings['league_id'] == null) $out = "";
else {
    $request = "https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/v0001/?key=".$steamapikey."&league_id=".$lg_settings['league_id'];
    echo "[ ] Requested...";

    $matches = array();
    $response = json_decode(file_get_contents($request), true);

    do {
      echo "OK [".sizeof($response['result']['matches'])."] ";
      if(isset($last_matchid)) $last_tail = $last_matchid;
      else $last_tail = 0;

      foreach($response['result']['matches'] as $r_match) {
        $last_matchid = $r_match['match_id'];

        if($lg_settings['time_limit_after'] != null && $lg_settings['time_limit_after'] > $r_match['start_time'])
          continue;
        if($lg_settings['time_limit_after'] != null && $lg_settings['time_limit_before'] < $r_match['start_time'])
          continue;
        if($lg_settings['match_limit_after'] != null && $lg_settings['match_limit_after'] > $r_match['match_id'])
          continue;
        if($lg_settings['match_limit_before'] != null && $lg_settings['match_limit_before'] < $r_match['match_id'])
          continue;


        if(!in_array($r_match['match_id'], $matches)) {
          $matches[] = $r_match['match_id'];
        }
      }
      $response = json_decode(file_get_contents($request."&start_at_match_id=".$last_matchid), true);
    } while (sizeof($response['result']['matches']) > 2 && $last_matchid != $last_tail);

    $out = implode($matches, "\n");
}
echo "\n";

fwrite($f, $out);
fclose($f);

echo "[ ] Creating database...";

$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) die("[F] Connection to SQL server failed: ".$conn->connect_error."\n");

if ($conn->select_db($lrg_db_prefix."_".$lg_settings['league_tag'])) {
  echo "\n[E] Database already exists\n";
  die();
  # TODO ask user for clearing database or changing prefix
} else {
  $conn->query("CREATE DATABASE ".$lrg_db_prefix."_".$lg_settings['league_tag']." CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
  if ($conn->connect_error) die("[F] Can't create database: ".$conn->connect_error."\n");
  if ($conn->error) die("[F] Can't create database: ".$conn->error."\n");
  $conn->select_db($lrg_db_prefix."_".$lg_settings['league_tag']);
  echo "OK\n[ ] Creating table `matches`...";

  $conn->query("CREATE TABLE `matches` (
    `matchid` bigint(20) UNSIGNED NOT NULL,
    `radiantWin` tinyint(1) NOT NULL,
    `duration` int(11) NOT NULL,
    `modeID` tinyint(11) UNSIGNED NOT NULL,
    `leagueID` int(11) NOT NULL,
    `start_date` int(11) NOT NULL,
    `stomp` int(11) NOT NULL,
    `comeback` int(11) NOT NULL,
    `cluster` int(10) UNSIGNED NOT NULL,
    `version` int(10) UNSIGNED NOT NULL
  );");
    if ($conn->connect_error) die("[F] Can't create table `matches`: ".$conn->connect_error."\n");
  echo "OK\n[ ] Creating table `matchlines`...";

  $conn->query("CREATE TABLE `matchlines` (
    `matchid` bigint(20) UNSIGNED NOT NULL,
    `playerid` bigint(20) NOT NULL,
    `heroid` smallint(6) NOT NULL,
    `level` tinyint(3) UNSIGNED NOT NULL,
    `isRadiant` tinyint(1) NOT NULL,
    `kills` smallint(6) NOT NULL,
    `deaths` smallint(6) NOT NULL,
    `assists` smallint(6) NOT NULL,
    `networth` mediumint(9) NOT NULL,
    `gpm` smallint(6) NOT NULL,
    `xpm` smallint(6) NOT NULL,
    `heal` mediumint(9) NOT NULL,
    `heroDamage` mediumint(9) NOT NULL,
    `towerDamage` smallint(6) NOT NULL,
    `lastHits` smallint(6) NOT NULL,
    `denies` smallint(6) NOT NULL
  );");
    if ($conn->connect_error) die("[F] Can't create table `matchlines`: ".$conn->connect_error."\n");
  echo "OK\n[ ] Creating table `adv_matchlines`...";

  $conn->query("CREATE TABLE `adv_matchlines` (
    `matchid` bigint(20) UNSIGNED NOT NULL,
    `playerid` bigint(20) NOT NULL,
    `heroid` smallint(5) UNSIGNED NOT NULL,
    `lh_at10` tinyint(3) UNSIGNED NOT NULL,
    `isCore` tinyint(1) NOT NULL,
    `lane` tinyint(3) UNSIGNED NOT NULL,
    `efficiency_at10` float NOT NULL,
    `wards` smallint(5) UNSIGNED NOT NULL,
    `sentries` smallint(5) UNSIGNED NOT NULL,
    `couriers_killed` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
    `roshans_killed` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
    `multi_kill` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
    `streak` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
    `stacks` tinyint(4) NOT NULL DEFAULT '0',
    `time_dead` int(10) UNSIGNED NOT NULL,
    `buybacks` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
    `wards_destroyed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
    `pings` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
    `stuns` float NOT NULL,
    `teamfight_part` float NOT NULL,
    `damage_taken` int(10) UNSIGNED NOT NULL
  );");
    if ($conn->connect_error) die("[F] Can't create table `adv_matchlines`: ".$conn->connect_error."\n");
  echo "OK\n[ ] Creating table `draft`...";

  $conn->query("CREATE TABLE `draft` (
    `matchid` bigint(20) UNSIGNED NOT NULL,
    `is_radiant` tinyint(1) NOT NULL,
    `is_pick` tinyint(1) NOT NULL,
    `hero_id` smallint(5) UNSIGNED NOT NULL,
    `stage` tinyint(3) UNSIGNED NOT NULL
  );");
    if ($conn->connect_error) die("[F] Can't create table `draft`: ".$conn->connect_error."\n");
  echo "OK\n[ ] Creating table `players`...";

  $conn->query("CREATE TABLE `players` (
    `playerID` bigint(20) NOT NULL,
    `nickname` varchar(128) NOT NULL
  );");
    if ($conn->connect_error) die("[F] Can't create table `draft`: ".$conn->connect_error."\n");
  echo "OK\n";

  echo "[ ] Adding keys to main tables...";
  $conn->query("ALTER TABLE `matches` ADD PRIMARY KEY (`matchid`), ADD UNIQUE KEY `matchid` (`matchid`);");
    if ($conn->connect_error) die("[F] Can't add key to `matches`: ".$conn->connect_error."\n");
  $conn->query("ALTER TABLE `adv_matchlines` ADD PRIMARY KEY (`matchid`,`playerid`);");
    if ($conn->connect_error) die("[F] Can't add key to `adv_matchlines`: ".$conn->connect_error."\n");
  $conn->query("ALTER TABLE `matchlines` ADD PRIMARY KEY (`matchid`,`playerid`);");
    if ($conn->connect_error) die("[F] Can't add key to `matchlines`: ".$conn->connect_error."\n");
  $conn->query("ALTER TABLE `players` ADD PRIMARY KEY (`playerID`), ADD UNIQUE KEY `playerid` (`playerid`);");
    if ($conn->connect_error) die("[F] Can't add key to `players`: ".$conn->connect_error."\n");

  echo "OK\n[ ] Linking main tables...";
  $conn->query("ALTER TABLE `adv_matchlines` ADD CONSTRAINT `adv_matchlines` FOREIGN KEY (`matchid`) REFERENCES `matches` (`matchid`);");
    if ($conn->connect_error) die("[F] Can't link `adv_matchlines` to `matches`: ".$conn->connect_error."\n");
  $conn->query("ALTER TABLE `matchlines` ADD CONSTRAINT `matchlines_ibfk_1` FOREIGN KEY (`matchID`) REFERENCES `matches` (`matchid`);");
    if ($conn->connect_error) die("[F] Can't link `matchlines` to `matches`: ".$conn->connect_error."\n");
  $conn->query("ALTER TABLE `draft` ADD CONSTRAINT `draft` FOREIGN KEY (`matchid`) REFERENCES `matches` (`matchid`);");
    if ($conn->connect_error) die("[F] Can't link `draft` to `matches`: ".$conn->connect_error."\n");
  $conn->query("ALTER TABLE `adv_matchlines` ADD CONSTRAINT `adv_matchlines_pl` FOREIGN KEY (`playerID`) REFERENCES `players` (`playerID`);");
    if ($conn->connect_error) die("[F] Can't link `adv_matchlines` to `players`: ".$conn->connect_error."\n");
  $conn->query("ALTER TABLE `matchlines` ADD CONSTRAINT `matchlines_pl` FOREIGN KEY (`playerID`) REFERENCES `players` (`playerID`);");
    if ($conn->connect_error) die("[F] Can't link `matchlines` to `players`: ".$conn->connect_error."\n");
  echo "OK\n";

  if($lg_settings['main']['teams']) {
    echo "[ ] Creating table `teams`...";
    $conn->query("CREATE TABLE `teams` (
      `teamid` bigint(20) UNSIGNED NOT NULL,
      `name` varchar(50) NOT NULL,
      `tag` varchar(25) NOT NULL
    );");
      if ($conn->connect_error) die("[F] Can't create table `teams`: ".$conn->connect_error."\n");
    echo "OK\n";

    echo "[ ] Creating table `teams_matches`...";
    $conn->query("CREATE TABLE `teams_matches` (
      `matchid` bigint(20) UNSIGNED NOT NULL,
      `teamid` bigint(20) UNSIGNED NOT NULL,
      `is_radiant` tinyint(1) NOT NULL
    );");
      if ($conn->connect_error) die("[F] Can't create table `teams_matches`: ".$conn->connect_error."\n");
    echo "OK\n";

    echo "[ ] Creating table `teams_rosters`...";
    $conn->query("CREATE TABLE `teams_rosters` (
      `teamid` bigint(20) UNSIGNED NOT NULL,
      `playerid` bigint(20) NOT NULL,
      `position` tinyint(3) UNSIGNED NOT NULL
    );");
      if ($conn->connect_error) die("[F] Can't create table `teams_rosters`: ".$conn->connect_error."\n");
    echo "OK\n";

    echo "[ ] Adding keys to team tables...";
    $conn->query("ALTER TABLE `teams` ADD PRIMARY KEY (`teamid`), ADD UNIQUE KEY `teamid` (`teamid`);");
      if ($conn->connect_error) die("[F] Can't add key to `teams`: ".$conn->connect_error."\n");
    $conn->query("ALTER TABLE `teams_matches` ADD PRIMARY KEY (`matchid`,`is_radiant`);");
      if ($conn->connect_error) die("[F] Can't add key to `teams_matches`: ".$conn->connect_error."\n");
    $conn->query("ALTER TABLE `teams_rosters` ADD PRIMARY KEY (`teamid`,`playerid`);");
      if ($conn->connect_error) die("[F] Can't add key to `teams_rosters`: ".$conn->connect_error."\n");

    echo "OK\n[ ] Linking team tables...";
    $conn->query("ALTER TABLE `teams_matches` ADD CONSTRAINT `teams_matches` FOREIGN KEY (`matchid`) REFERENCES `matches` (`matchid`);");
      if ($conn->connect_error) die("[F] Can't link `teams_matches` to `matches`: ".$conn->connect_error."\n");
    $conn->query("ALTER TABLE `teams_matches` ADD CONSTRAINT `teams_matches` FOREIGN KEY (`teamid`) REFERENCES `teams` (`teamid`);");
      if ($conn->connect_error) die("[F] Can't link `teams_matches` to `teams`: ".$conn->connect_error."\n");
    $conn->query("ALTER TABLE `teams_rosters` ADD CONSTRAINT `teams_rosters` FOREIGN KEY (`teamid`) REFERENCES `teams` (`teamid`);");
      if ($conn->connect_error) die("[F] Can't link `teams_rosters` to `teams`: ".$conn->connect_error."\n");
    $conn->query("ALTER TABLE `teams_rosters` ADD CONSTRAINT `teams_rosters` FOREIGN KEY (`playerid`) REFERENCES `players` (`playerID`);");
      if ($conn->connect_error) die("[F] Can't link `teams_rosters` to `players`: ".$conn->connect_error."\n");

    echo "OK\n";
  }
}


 ?>
