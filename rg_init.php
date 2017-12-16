<?php
# TODO
# init empty database
# get matchids for league
# clone
# reload settings from database
# reload settings to database
# export to JSON
# import from json

# create database
# create tables
# check for reports folder
$init = true;
require_once("settings.php");

$lg_settings = array(
  "main"=> array(),
  "ana" => array(),
  "web" => array()
);

$lg_settings['league_tag'] = "fpl_sept_2017";
$lg_settings['league_name'] = "FPL - September 2017";
$lg_settings['league_desc'] = "FPL - September 2017";
$lg_settings['league_id'] = null;
$lg_settings['time_limit_after'] = false;
$lg_settings['time_limit_before'] = false;

/* STARLADDER */

$lg_settings['league_tag'] = "d2cl_s13";
$lg_settings['league_name'] = "Dota 2 Champions League Season 13";
$lg_settings['league_desc'] = "Minor Tournament from Epic Esports Events";
$lg_settings['league_id'] = 5850;
$lg_settings['time_limit_after'] = null;
$lg_settings['time_limit_before'] = null;
$lg_settings['match_limit_after'] = 3455406668;
$lg_settings['match_limit_before'] = 3507199385;

$lg_settings['league_tag'] = "summit8_minor_finals";
$lg_settings['league_name'] = "Dota Summit 8 Los Angeles LAN Finals";
$lg_settings['league_desc'] = "Dota 2 Pro Circuit Minor Finals";
$lg_settings['league_id'] = 5850;
$lg_settings['time_limit_after'] = null;
$lg_settings['time_limit_before'] = null;
$lg_settings['match_limit_after'] = 3565905100;
$lg_settings['match_limit_before'] = null;

$lg_settings['league_tag'] = "summit8_minor_full";
$lg_settings['league_name'] = "Dota Summit 8 Los Angeles";
$lg_settings['league_desc'] = "Dota 2 Pro Circuit Minor";
$lg_settings['league_id'] = 5850;
$lg_settings['time_limit_after'] = null;
$lg_settings['time_limit_before'] = null;
$lg_settings['match_limit_after'] = null;
$lg_settings['match_limit_before'] = null;

$lg_settings['league_tag'] = "summit8_minor_qualis";
$lg_settings['league_name'] = "Dota Summit 8 Los Angeles Qualifiers";
$lg_settings['league_desc'] = "Dota 2 Pro Circuit Minor Qualifiers";
$lg_settings['league_id'] = 5850;
$lg_settings['time_limit_after'] = null;
$lg_settings['time_limit_before'] = null;
$lg_settings['match_limit_after'] = null;
$lg_settings['match_limit_before'] = 3565905100;

/*/
/*/

$lg_settings['league_tag'] = "workshop_bots_dec1_dec15";
$lg_settings['league_name'] = "Workshop Bots - December 1st - 15th";
$lg_settings['league_desc'] = "Battle between workshop botscripts";
$lg_settings['league_id'] = null;
$lg_settings['time_limit_after'] = null;
$lg_settings['time_limit_before'] = null;
$lg_settings['match_limit_after'] = 3592194081;
$lg_settings['match_limit_before'] = null;
/* */

# League Parameters

$lg_settings['main']['teams'] = true; # set team or player mix competition
                    # false = players competition
                    # true  = teams competition

$lg_settings['main']['fantasy'] = false; # not implemented yet TODO

$lg_settings['ana']['records']     = true; # records
$lg_settings['ana']['avg_limit']   = 5;
$lg_settings['ana']['avg_heroes']  = true; # averages for heroes
$lg_settings['ana']['avg_players'] = true; # averages for players

$lg_settings['ana']['hero_positions']             = true; # heroes on positions
$lg_settings['ana']['hero_positions_matches']     = true; #   include matchids
$lg_settings['ana']['hero_positions_player_data'] = true;
  # team games only: rely on player's positions instead of lanes
$lg_settings['ana']['hero_sides'] = true; # hero stats on sides

$lg_settings['ana']['draft_stages'] = true; # pick/ban draft stages stats

$lg_settings['ana']['hero_pairs']            = true; # hero pairs winrates
$lg_settings['ana']['hero_pairs_matches']    = true; #   include matchids

$lg_settings['ana']['hero_triplets']          = true; # hero triplets winrates
$lg_settings['ana']['hero_triplets_matches']  = true; #   include matchids

$lg_settings['ana']['hero_combos_graph']      = true; # interactive graph using vis.js
                                                    # may not work with big amount of data

$lg_settings['ana']['matchlist'] = true; # matches list + drafts in matches and participants

# PLAYERS ONLY (only work with $lg_settings['main']['teams'] = false)
$lg_settings['ana']['pvp'] = true; # players only: player vs player winrates
$lg_settings['ana']['pvp_matches'] = true;

$lg_settings['ana']['player_positions'] = true; # players stats on positions
$lg_settings['ana']['player_positions_matches'] = true;

$lg_settings['ana']['player_pairs'] = true; # player pairs
$lg_settings['ana']['player_pairs_matches'] = true;

$lg_settings['ana']['player_triplets'] = true; # player triplets
$lg_settings['ana']['player_triplets_matches'] = true;

$lg_settings['ana']['players_combo_graph']     = false; # interactive graph using vis.js
                                                    # may not work with big amount of data

$lg_settings['ana']['player_vs_hero'] = false; # not implemented yet TODO
$lg_settings['ana']['player_hero_combos'] = false; # not implemented yet TODO
$lg_settings['ana']['player_hero_stats'] = false;  # not implemented yet TODO

$lg_settings['ana']['hero_vs_hero'] = false; # not implemented yet TODO

if($lg_settings['main']['teams']) {
$lg_settings['ana']['teams'] = array();
# TEAMS ONLY (only workwith $lg_settings['main']['teams'] = true)
$lg_settings['ana']['teams']['rosters']  = true;
$lg_settings['ana']['teams']['avg']      = true;
$lg_settings['ana']['teams']['pickbans'] = true;
$lg_settings['ana']['teams']['draft']    = true;
$lg_settings['ana']['teams']['heropos']  = true;
$lg_settings['ana']['teams']['hero_graph']=true;
$lg_settings['ana']['teams']['pairs']    = true;
$lg_settings['ana']['teams']['triplets'] = true;
$lg_settings['ana']['teams']['matches']  = true;
# teams only: team stats
#   total games, winrate, average k / d / a / xpm / gpm / wards / wards_destroyed
#   pick/ban stats
#   draft stages stats
#   heroes on positions
#   hero pairs
#   hero triplets
#   matches list
$lg_settings['ana']['teams']['team_vs_team']   = true;
}

$lg_settings['web'] = array(
  //"custom_style" => "sa",
  //"custom_style" => "fpl",
  //"custom_style" => "sl-minor-17",
  "custom_style" => "d2cl",
  "pvp_grid" => false,

  "heroes_combo_graph" => true,
  "players_combo_graph" => true,

  "overview_versions" => true,
  "overview_versions_chart" => true,
  "overview_last_match_winners" => true,
  "overview_charts" => true,
  "overview_regions" => true,
  "overview_modes" => true,
  "overview_sides_graph" => true,
  "overview_time_limits" => true,
  "overview_heroes_contested_graph" => true,
  "overview_days_graph" => true,

  "overview_top_contested" => true,
  "overview_top_contested_count" => 10,
  "overview_top_picked" => true,
  "overview_top_picked_count" => 5,
  "overview_top_bans" => true,
  "overview_top_bans_count" => 5,

  "overview_top_hero_pairs" => true,
  "overview_top_hero_pairs_count" => 5,

  "overview_top_player_pairs" => true,
  "overview_top_player_pairs_count" => 5,

  "overview_matches" => true,
  "overview_first_match" => false,
  "overview_last_match" => true,
  "overview_records_duration" => true,
  "overview_records_stomp" => true,
  "overview_records_comeback" => true,

  "overview_random_stats" => true,

  "overview_top_draft" => true,
  "overview_draft_1_1" => true,
  "overview_draft_1_1_count" => 5,
  "overview_draft_1_2" => false,
  "overview_draft_1_2_count" => 3,
  "overview_draft_1_3" => false,
  "overview_draft_1_3_count" => 3,
  "overview_draft_0_1" => true,
  "overview_draft_0_1_count" => 5,
  "overview_draft_0_2" => false,
  "overview_draft_0_2_count" => 3,
  "overview_draft_0_3" => false,
  "overview_draft_0_3_count" => 4,
);

$lg_settings['version'] = $lrg_version;

echo "[ ] Creating database...";

$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass);

if ($conn->connect_error) die("[F] Connection to SQL server failed: ".$conn->connect_error."\n");

if ($conn->select_db($lrg_db_prefix."_".$lg_settings['league_tag'])) {
  echo "\n[E] Database already exists\n";
  die();
  # TODO ask user for clearing database or changing prefix
} else {
  $conn->query("CREATE DATABASE ".$lrg_db_prefix."_".$lg_settings['league_tag'].";");
    if ($conn->connect_error) die("[F] Can't create database: ".$conn->connect_error."\n");
  $conn->select_db($lrg_db_prefix."_".$lg_settings['league_tag']);
  echo "OK\n[ ] Creating table `matches`...";

  $conn->query("CREATE TABLE `matches` (
    `matchid` int(10) UNSIGNED NOT NULL,
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
    `matchid` int(11) UNSIGNED NOT NULL,
    `playerid` int(11) NOT NULL,
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
    `matchid` int(10) UNSIGNED NOT NULL,
    `playerid` int(10) NOT NULL,
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
    `matchid` int(10) UNSIGNED NOT NULL,
    `is_radiant` tinyint(1) NOT NULL,
    `is_pick` tinyint(1) NOT NULL,
    `hero_id` smallint(5) UNSIGNED NOT NULL,
    `stage` tinyint(3) UNSIGNED NOT NULL
  );");
    if ($conn->connect_error) die("[F] Can't create table `draft`: ".$conn->connect_error."\n");
  echo "OK\n[ ] Creating table `players`...";

  $conn->query("CREATE TABLE `players` (
    `playerID` int(11) NOT NULL,
    `nickname` varchar(25) NOT NULL
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
      `teamid` int(10) UNSIGNED NOT NULL,
      `name` varchar(25) NOT NULL,
      `tag` varchar(10) NOT NULL
    );");
      if ($conn->connect_error) die("[F] Can't create table `teams`: ".$conn->connect_error."\n");
    echo "OK\n";

    echo "[ ] Creating table `teams_matches`...";
    $conn->query("CREATE TABLE `teams_matches` (
      `matchid` int(10) UNSIGNED NOT NULL,
      `teamid` int(10) UNSIGNED NOT NULL,
      `is_radiant` tinyint(1) NOT NULL
    );");
      if ($conn->connect_error) die("[F] Can't create table `teams_matches`: ".$conn->connect_error."\n");
    echo "OK\n";

    echo "[ ] Creating table `teams_rosters`...";
    $conn->query("CREATE TABLE `teams_rosters` (
      `teamid` int(10) UNSIGNED NOT NULL,
      `playerid` int(11) NOT NULL,
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


$f = fopen("leagues/".$lg_settings['league_tag'].".json", "w") or die("[F] Couldn't open file to save results. Check working directory for `reports` folder.\n");
fwrite($f, json_encode($lg_settings));
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
    } while (sizeof($response['result']['matches']) > 2);

    $out = implode($matches, "\n");
}
echo "\n";

fwrite($f, $out);
fclose($f);

/*
https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/?match_id=27110133&key=<key>
league_id=<id> # matches for a particular league
start_at_match_id=<id> # Start the search at the indicated match id, descending
date_min=<date> # date in UTC seconds since Jan 1, 1970 (unix time format)
date_max=<date> # date in UTC seconds since Jan 1, 1970 (unix time format)
*/
 ?>
