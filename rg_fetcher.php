#!/bin/php
<?php
ini_set('memory_limit', '4000M');

include_once("head.php");
include_once("modules/fetcher/get_patchid.php");
include_once("modules/commons/generate_tag.php");
include_once("modules/commons/metadata.php");

include_once("libs/simple-opendota-php/simple_opendota.php");

echo("\nInitialising...\n");

$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);
$conn->set_charset('utf8mb4');
$meta = new lrg_metadata;

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
$first_scheduled = null;

$scheduled_wait_period = $options['w'] ?? 60;

$scheduled_stratz = [];

$input_cont = file_get_contents($lrg_input);
$input_cont = str_replace("\r\n", "\n", $input_cont);
$matches    = explode("\n", trim($input_cont));
$matches = array_unique($matches);

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
$sql = "SELECT playerid FROM players";
if ($conn->multi_query($sql)) {
  $res = $conn->store_result();

  while ($row = $res->fetch_row()) {
    $t_players[$row[0]] = true;
  }
  $res->free();
}

// this code is such a shitshow tbh, but I don't want to fix it, c ya in lrg-simon
function fetch($matches) {
  global $opendota, $conn, $rnum, $failed_matches, $scheduled, $scheduled_stratz, $t_teams, $t_players, $use_stratz, $require_stratz,
    $request_unparsed, $meta, $stratz_timeout_retries, $force_adding, $cache_dir, $lg_settings, $lrg_use_cache, $first_scheduled,
    $use_full_stratz, $scheduled_wait_period;
  
  foreach ($matches as $match) {
      $t_match = [];
      $t_matchlines = [];
      $t_adv_matchlines = [];
      $t_draft = [];
      $t_new_players = [];
      $bad_replay = false;

      if ($lg_settings['main']['teams']) {
        $t_team_matches = [];
      }

      if (empty($match) || $match[0] == "#") continue;
      echo("[$rnum\t] Match $match: ");
      $rnum++;

      $query = $conn->query("SELECT matchid FROM matches WHERE matchid = ".$match.";");

      if (isset($query->num_rows) && $query->num_rows) {
          echo("Already in database, skipping\n");
          continue;
      }

      if($lrg_use_cache && file_exists("$cache_dir/".$match.".json")) {
        echo("Reusing cache.");
        $json = file_get_contents("$cache_dir/".$match.".json");
        $matchdata = json_decode($json, true);
      } else if($lrg_use_cache && file_exists("$cache_dir/unparsed_".$match.".json") && $force_adding) {
        echo("Reusing unparsed cache.");
        $json = file_get_contents("$cache_dir/unparsed_".$match.".json");
        $matchdata = json_decode($json, true);
        $bad_replay = true;
      } else {
        echo("Requesting OpenDota.");
        $matchdata = $opendota->match($match);
        echo("..OK.");
        if ($matchdata === FALSE || !isset($matchdata['duration'])) {
            echo("..ERROR: Unable to read JSON skipping.\n");
            //if (!isset($matchdata['duration'])) var_dump($matchdata);

            if($request_unparsed && !in_array($match, $scheduled)) {
              $opendota->request_match($match);
              echo "[\t] Requested and scheduled $match\n";
              if(empty($first_scheduled))
                $first_scheduled = time();
              $scheduled[] = $match;
            } else if (in_array($match, $scheduled) && !$force_adding) {
              $failed_matches[sizeof($failed_matches)] = $match;
            }

            continue;
        } else {
          if($matchdata['duration'] < 600) {
              echo("..Duration is less than 10 minutes, skipping...\n");
              // Initially it used to be 5 minutes, but sice a lot of stuff is hardly
              // binded with 10 min mark, it's better to use 10 min as a benchmark.
              continue;
          }
          if($matchdata['radiant_score'] < 5 && $matchdata['dire_score'] < 5) {
              echo("..Low score, skipping.\n");
              continue;
          }

          $abandon = false;
          for($i=0; $i<10; $i++) {
              if($matchdata['players'][$i]['abandons']) {
                  $abandon = true;
                  break;
              }
          }

          if($abandon) {
              echo("..Abandon detected, skipping.\n");
              continue;
          }

          if ($matchdata['players'][0]['lh_t'] == null) {
            if($request_unparsed && !in_array($match, $scheduled)) {
              $opendota->request_match($match);
              echo "..Unparsed. Requested and scheduled $match\n";
              if(empty($first_scheduled))
                $first_scheduled = time();
              $scheduled[] = $match;
              continue;
            }

            if(!$force_adding) {
              echo("..ERROR: Replay isn't parsed.\n");
              $failed_matches[sizeof($failed_matches)] = $match;
              continue;
            } else {
              echo("..WARNING: Replay isn't parsed.");
              $bad_replay = true;
            }
          }
        }
      }

      if(!file_exists("$cache_dir/".$match.".json") || ( $bad_replay && !file_exists("$cache_dir/unparsed_".$match.".json") )) {
        if($matchdata['lobby_type'] != 1 && $matchdata['lobby_type'] != 2 && $use_stratz) {
          echo("..Requesting STRATZ.");

          // Not all matches in Stratz database have PickBan support for /match? endpoint
          // so there will be kind of workaround for it.

          $request = "https://api.stratz.com/api/v1/match?include=Player,PickBan&matchid=$match";

          $json = @file_get_contents($request);

          $stratz = empty($json) ? [] : json_decode($json, true);

          if(!isset($stratz[0]['parsedDate'])) {
            unset($stratz);

            if($request_unparsed && !in_array($match, $scheduled_stratz)) {
              @file_get_contents($request);
              `php tools/replay_request_stratz.php -m$match`;
              echo "..Requested and scheduled $match\n";
              if(empty($first_scheduled))
                $first_scheduled = time();
              $scheduled_stratz[] = $match;
              continue;
            }
          }

          if (!empty($stratz[0]['players'])) {
            for($i=0, $j=0, $sz=sizeof($matchdata['players']); $i<$sz; $i++) {
              if(!isset($matchdata['players'][$i]['hero_id']) || !$matchdata['players'][$i]['hero_id'] || $j>9) {
                unset($matchdata['players'][$i]);
                continue;
              }
              if(!isset($matchdata['players'][$i]['account_id']) || $matchdata['players'][$i]['account_id'] === null
                  || $matchdata['players'][$i]['account_id'] != $stratz[0]['players'][$j]['steamId']) {
                $matchdata['players'][$i]['account_id'] = $stratz[0]['players'][$j]['steamId'];
                //$tmp = $opendota->player($matchdata['players'][$i]['account_id']);
    
                $matchdata['players'][$i]["name"] = $stratz[0]['players'][$j]['name'];
                if(isset($stratz[0]['players'][$j]['proPlayerName']))
                  $matchdata['players'][$i]["personaname"] = $stratz[0]['players'][$j]['proPlayerName'];
              }
              $j++;
            }
          }

          $full_request = false;
          if(($matchdata['game_mode'] == 22 || $matchdata['game_mode'] == 3 || empty($matchdata['picks_bans'])) && 
              (!in_array($match, $failed_matches) || false)) {
            $stratz_retries = $stratz_timeout_retries+1;
            while ((!isset($stratz[0]['pickBans']) || $stratz[0]['pickBans'] === NULL) && $use_full_stratz) {
              $stratz_retries--;
              echo "..STRATZ ERROR";
              sleep(5);
              echo ", retrying.";

              if (!isset($stratz[0]['pickBans'])) {
                  $request = "https://api.stratz.com/api/v1/match/$match";
                  $full_request = true;
              }

              $json = file_get_contents($request);

              if($full_request && strlen($json) < 6500 || !$stratz_retries) {
                  echo("..ERROR: Missing STRATZ analysis, skipping.\n");

                  if($request_unparsed && !in_array($match, $scheduled_stratz)) {
                    @file_get_contents($request);
                    `php tools/replay_request_stratz.php -m$match`;
                    echo "[\t] Requested and scheduled $match\n";
                    if(empty($first_scheduled))
                      $first_scheduled = time();
                    $scheduled_stratz[] = $match;
                    break;
                  } else if ($require_stratz) {
                    echo("..ERROR: Missing STRATZ analysis, skipping.\n");
                    $failed_matches[sizeof($failed_matches)] = $match;
                    break;
                  }
              } else {
                $stratz = json_decode($json, true);
                if ($full_request) $stratz = [ $stratz ];
              }
            }

            if(in_array($match, $failed_matches) && $require_stratz)
              continue;
            else if(isset($stratz)) {
              $matchdata['picks_bans_stratz'] = $stratz[0]['pickBans'];
            } 
          }
          
          $matchdata['players'] = array_values($matchdata['players']);
          
          if (isset($stratz)) {
            echo("..Stratz data merged.");
            unset($stratz);
          } else {
            echo("..Missing full stratz analysis, merged players.");
          }
        }

        if($lg_settings['main']['teams'] && (!isset($matchdata['radiant_team']['team_id']) || !isset($matchdata['dire_team']['team_id'])) ) {
            $json = file_get_contents("https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/?match_id=$match&key=$steamapikey");
            $tmp = json_decode($json, true);
            unset($json);
            if(!isset($matchdata['radiant_team']['team_id'])) {
              if(isset($tmp['result']['radiant_team_id'])) {
                if(isset($t_teams[$tmp['result']['radiant_team_id']]) ) {
                  $matchdata['radiant_team']['team_id'] = $tmp['result']['radiant_team_id'];
                  $matchdata['radiant_team']['name'] = $t_teams[$tmp['result']['radiant_team_id']]['name'];
                  $matchdata['radiant_team']['tag'] = $t_teams[$tmp['result']['radiant_team_id']]['tag'];
                } else {
                  $matchdata['radiant_team']['team_id'] = $tmp['result']['radiant_team_id'];
                  $matchdata['radiant_team']['name'] = $tmp['result']['radiant_name'];

                  $json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetTeamInfoByTeamID/v001/?key='.$steamapikey.'&teams_requested=1&start_at_team_id='.$matchdata['radiant_team']['team_id']);
                  $team = json_decode($json, true);

                  if( !isset($team['result']['teams'][0]['tag']) || $team['result']['teams'][0]['tag'] == null )
                      $matchdata['radiant_team']['tag'] = generate_tag($tmp['result']['radiant_name']);
                  else
                      $matchdata['radiant_team']['tag'] = $team['result']['teams'][0]['tag'];
                }
              }
            }
            if(!isset($matchdata['dire_team']['team_id'])) {
              if(isset($tmp['result']['dire_team_id'])) {
                if(isset($t_teams[$tmp['result']['dire_team_id']]) ) {
                  $matchdata['dire_team']['team_id'] = $tmp['result']['dire_team_id'];
                  $matchdata['dire_team']['name'] = $t_teams[$tmp['result']['dire_team_id']]['name'];
                  $matchdata['dire_team']['tag'] = $t_teams[$tmp['result']['dire_team_id']]['tag'];
                } else {
                  $matchdata['dire_team']['team_id'] = $tmp['result']['dire_team_id'];
                  $matchdata['dire_team']['name'] = $tmp['result']['dire_name'];

                  $json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetTeamInfoByTeamID/v001/?key='.$steamapikey.'&teams_requested=1&start_at_team_id='.$matchdata['dire_team']['team_id']);
                  $team = json_decode($json, true);

                  if( !isset($team['result']['teams'][0]['tag']) || $team['result']['teams'][0]['tag'] == null )
                      $matchdata['dire_team']['tag'] = generate_tag($tmp['result']['dire_name']);
                  else
                      $matchdata['dire_team']['tag'] = $team['result']['teams'][0]['tag'];
                }
              }
            }
        }

        unset($matchdata['chat']);
        unset($matchdata['cosmetics']);

        $json = json_encode($matchdata);
        if(!empty($cache_dir) && $lrg_use_cache) {
          $f = fopen("$cache_dir/".($bad_replay ? "unparsed_" : "").$match.".json", "w");
          fwrite($f, $json);
          fclose($f);

          echo("..Saved to cache.");
        }
      }

      unset($json);

      $t_match['matchid'] = $match;
      $t_match['version'] = get_patchid($matchdata['start_time'], $matchdata['patch'], $meta);
      $t_match['radiantWin'] = $matchdata['radiant_win'];
      $t_match['duration'] = $matchdata['duration'];
      $t_match['modeID'] = $matchdata['game_mode'];
      $t_match['leagueID'] = $matchdata['leagueid'];
      $t_match['cluster']  = $matchdata['cluster'];
      $t_match['date'] = $matchdata['start_time'];
      if (isset($matchdata['stomp']))
          $t_match['stomp'] = $matchdata['stomp'];
      else $t_match['stomp'] = $bad_replay ? 0 : $matchdata['loss'];
      if (isset($matchdata['comeback']))
          $t_match['comeback'] = $matchdata['comeback'];
      else $t_match['comeback'] = $bad_replay ? 0 : $matchdata['throw'];

      if ($lg_settings['main']['teams'] && (isset($matchdata['radiant_team']) || isset($matchdata['dire_team']))) {
        for($i=0; $i<2; $i++) {
          $tag = !$i ? 'dire_team' : 'radiant_team';
          if(!isset($matchdata[$tag])) continue;

          $t_team_matches[] = array(
            "matchid" => $match,
            "teamid"  => $matchdata[$tag]['team_id'],
            "is_radiant" => $i,
          );
          if (!isset($t_teams[$matchdata[$tag]['team_id']])) {
            $t_teams[$matchdata[$tag]['team_id']] = array(
              "name" => $matchdata[$tag]['name'],
              "tag" => $matchdata[$tag]['tag'],
              "added" => false
            );
          }
        }
      }
      
      
      
      $i = sizeof($t_matchlines);
      for ($j=0, $sz=10; $j<$sz; $j++) {
          $t_matchlines[$i]['matchid'] = $match;

          # for wrong numbers of players in opendota response
          if (!isset($matchdata['players'][$j]['hero_id'])) {
            $sz++;
            continue;
          }
          
          # support for botmatches
          if ($matchdata['players'][$j]['account_id'] != null)
            $t_matchlines[$i]['playerid'] = $matchdata['players'][$j]['account_id'];
          else {
            if (isset($matchdata['radiant_team'])) {
              if($matchdata['players'][$j]['isRadiant'])
                  $matchdata['players'][$j]['account_id'] = $matchdata['radiant_team']['team_id'];
              else
                  $matchdata['players'][$j]['account_id'] = $matchdata['dire_team']['team_id'];
            } else $matchdata['players'][$j]['account_id'] = 1;

            $matchdata['players'][$j]['account_id'] *= (-1)*$matchdata['players'][$j]['hero_id'];
            $t_matchlines[$i]['playerid'] = $matchdata['players'][$j]['account_id'];
          }
          if(!isset($t_players[$matchdata['players'][$j]['account_id']])) {
            if ($matchdata['players'][$j]['account_id'] < 0) {
              $t_new_players[$matchdata['players'][$j]['account_id']] = $meta['heroes'][$matchdata['players'][$j]['hero_id']]['name']." Player";
            } else {
              if (isset($matchdata['players'][$j]["name"]) && $matchdata['players'][$j]["name"] != null) {
                $t_new_players[$matchdata['players'][$j]['account_id']] = $matchdata['players'][$j]["name"];
              } else if ($matchdata['players'][$j]["personaname"] != null) {
                $t_new_players[$matchdata['players'][$j]['account_id']] = $matchdata['players'][$j]["personaname"];
              } else
                $t_new_players[$matchdata['players'][$j]['account_id']] = "Player ".$matchdata['players'][$j]['account_id'];
            }

          }
          $t_matchlines[$i]['heroid'] = $matchdata['players'][$j]['hero_id'];
          $t_matchlines[$i]['isRadiant'] = $matchdata['players'][$j]['isRadiant'];
          $t_matchlines[$i]['level'] = $matchdata['players'][$j]['level'];
          $t_matchlines[$i]['kills'] = $matchdata['players'][$j]['kills'];
          $t_matchlines[$i]['deaths'] = $matchdata['players'][$j]['deaths'];
          $t_matchlines[$i]['assists'] = $matchdata['players'][$j]['assists'];
          $t_matchlines[$i]['networth'] = $matchdata['players'][$j]['total_gold'];
          $t_matchlines[$i]['gpm'] = $matchdata['players'][$j]['gold_per_min'];
          $t_matchlines[$i]['xpm'] = $matchdata['players'][$j]['xp_per_min'];
          $t_matchlines[$i]['heal'] = $matchdata['players'][$j]['hero_healing'];
          $t_matchlines[$i]['heroDamage'] = $matchdata['players'][$j]['hero_damage'];
          $t_matchlines[$i]['towerDamage'] = $matchdata['players'][$j]['tower_damage'];
          $t_matchlines[$i]['lasthits'] = $matchdata['players'][$j]['last_hits'];
          $t_matchlines[$i]['denies'] = $matchdata['players'][$j]['denies'];


          $t_adv_matchlines[$i]['matchid'] = $match;
          $t_adv_matchlines[$i]['playerid'] = $matchdata['players'][$j]['account_id'];
          $t_adv_matchlines[$i]['heroid'] = $matchdata['players'][$j]['hero_id'];

          if (!$bad_replay) {
            $t_adv_matchlines[$i]['lh10'] = $matchdata['players'][$j]['lh_t'][10];
            if ($matchdata['players'][$j]['lane_role'] == 5)
                $matchdata['players'][$j]['lane_role'] = 4; # we don't care about different jungles
            //if ($matchdata['players'][$j]['is_roaming'])
            //    $matchdata['players'][$j]['lane_role'] = 5;
            $t_adv_matchlines[$i]['lane'] = $matchdata['players'][$j]['lane_role'];
          }

          # trying to decide, is it a core
          $support_indicators = 0;
          //if ($matchdata['players'][$j]['lane_role'] == 4) $support_indicators+=2;
          if (!$bad_replay) {
            if ($matchdata['players'][$j]['lh_t'][5] <= 6) $support_indicators++;
            if ($matchdata['players'][$j]['lh_t'][3] <= 2) $support_indicators++;

            if ($matchdata['players'][$j]['obs_placed'] > 0) $support_indicators++;
            if ($matchdata['players'][$j]['obs_placed'] > 3) $support_indicators++;
            if ($matchdata['players'][$j]['obs_placed'] > 8) $support_indicators++;
            if ($matchdata['players'][$j]['obs_placed'] > 12) $support_indicators++;
            if ($matchdata['players'][$j]['sen_placed'] > 6) $support_indicators++;

            if ($matchdata['players'][$j]['lane_efficiency'] < 0.55 && $matchdata['players'][$j]['win']) $support_indicators++;
            if ($matchdata['players'][$j]['lane_efficiency'] < 0.50) $support_indicators++;
            if ($matchdata['players'][$j]['lane_efficiency'] < 0.35) $support_indicators++;
            if ($matchdata['players'][$j]['is_roaming']) $support_indicators+=3;
          }

          if ($matchdata['players'][$j]['gold_per_min'] < 420 && $matchdata['players'][$j]['win']) $support_indicators++;
          if ($matchdata['players'][$j]['gold_per_min'] < 355) $support_indicators++;
          if ($matchdata['players'][$j]['gold_per_min'] < 290) $support_indicators++;

          if ($matchdata['players'][$j]['hero_damage']*60/$matchdata['duration'] < 375 && $matchdata['players'][$j]['win']) $support_indicators++;
          if ($matchdata['players'][$j]['hero_damage']*60/$matchdata['duration'] < 275 && !$matchdata['players'][$j]['win']) $support_indicators++;

          if ($matchdata['players'][$j]['last_hits']*60/$matchdata['duration'] < 2.5) $support_indicators++;

          # TODO compare to teammates on the same lane/role

          if (!$bad_replay && $support_indicators > 4) $t_adv_matchlines[$i]['is_core'] = 0;
          else if ($bad_replay && $support_indicators > 2) $t_adv_matchlines[$i]['is_core'] = 0;
          else $t_adv_matchlines[$i]['is_core'] = 1;

          if (!$bad_replay) {
            if ($t_adv_matchlines[$i]['is_core'] && $matchdata['players'][$j]['is_roaming'])
              $t_adv_matchlines[$i]['lane'] = $matchdata['players'][$j]['lane_role'];
            else if (!$t_adv_matchlines[$i]['is_core'] && $matchdata['players'][$j]['is_roaming'])
              $t_adv_matchlines[$i]['lane'] = 5;
            # Gonna put roaming cores into junglers for now
          } else {
            # We can't determine hero's lane, so we're going to set it as the most popular value for that hero
            # if it's core. Supports are going to be roaming.
            if ($t_adv_matchlines[$i]['is_core']) {
              #$t_adv_matchlines[$i]['heroid']
              $sql = "SELECT a.lane, COUNT(DISTINCT matchid) mc FROM".
                      "(select * from `adv_matchlines` WHERE heroid=".$t_adv_matchlines[$i]['heroid']." limit 35) a ".
                      "GROUP BY a.lane ORDER BY mc DESC";

              if ($conn->multi_query($sql) !== TRUE) die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

              $query_res = $conn->store_result();

              $row = $query_res->fetch_row();
              $t_adv_matchlines[$i]['lane'] = $row[0];
              if($row[0] == 5)
                $t_adv_matchlines[$i]['is_core'] = 0;

              $query_res->free_result();
              # It's not ideal, but it works for now.
            } else {
              $t_adv_matchlines[$i]['lane'] = 4;
            }

          }


          if (!$bad_replay) {
            $t_adv_matchlines[$i]['lane_efficiency'] = $matchdata['players'][$j]['lane_efficiency'];
            $t_adv_matchlines[$i]['observers'] = $matchdata['players'][$j]['obs_placed'];
            $t_adv_matchlines[$i]['sentries'] = $matchdata['players'][$j]['sen_placed'];
            $t_adv_matchlines[$i]['couriers_killed'] = $matchdata['players'][$j]['courier_kills'];
            $t_adv_matchlines[$i]['roshans_killed'] = $matchdata['players'][$j]['roshan_kills'];
            $t_adv_matchlines[$i]['wards_destroyed'] = $matchdata['players'][$j]['observer_kills'];
            if (count($matchdata['players'][$j]['multi_kills']) == 0) $t_adv_matchlines[$i]['max_multikill'] = 0;
            else {
                $tmp = array_keys($matchdata['players'][$j]['multi_kills']);
                $t_adv_matchlines[$i]['max_multikill'] = end($tmp);
                unset($tmp);
            }
            if (count($matchdata['players'][$j]['kill_streaks']) == 0) $t_adv_matchlines[$i]['max_streak'] = 0;
            else {
                $tmp = array_keys($matchdata['players'][$j]['kill_streaks']);
                $t_adv_matchlines[$i]['max_streak'] = end($tmp);
                unset($tmp);
            }
            $t_adv_matchlines[$i]['stacks'] = $matchdata['players'][$j]['camps_stacked'];
            $t_adv_matchlines[$i]['time_dead'] = $matchdata['players'][$j]['life_state_dead'];
            $t_adv_matchlines[$i]['buybacks'] = $matchdata['players'][$j]['buyback_count'];
            $t_adv_matchlines[$i]['pings'] = isset($matchdata['players'][$j]['pings']) ? $matchdata['players'][$j]['pings'] : 0;
            $t_adv_matchlines[$i]['stuns'] = $matchdata['players'][$j]['stuns'];
            $t_adv_matchlines[$i]['teamfight_part'] = $matchdata['players'][$j]['teamfight_participation'];
            $t_adv_matchlines[$i]['damage_taken'] = 0;
            foreach($matchdata['players'][$j]['damage_inflictor_received'] as $key => $instance) {
              $t_adv_matchlines[$i]['damage_taken'] += $instance;
            }
          }
          $i++;
      }

      $i = sizeof($t_draft);

      # OpenDota doesn't have information about draft for Ranked All Pick
      # Game Mode IDs:
      # 2  = Captain's Mode
      # 9  = Reverse Captain's Mode
      # 16 = Captain's Draft
      #
      # versions:
      # <= 20 = before 7.07
      # > 20 = after 7.07
      # TODO Draft information from Stratz for ranked all pick (22)


      if ($matchdata['game_mode'] == 2 || $matchdata['game_mode'] == 9) {
          $stages = array (
              # (isPick + 1)*((-1)*isRadiant)
              1 => 0, # dire bans
              2 => 0, # dire picks
              -1 => 0,# radi bans
              -2 => 0 # radi bans
          );
          foreach ($matchdata['picks_bans'] as $draft_instance) {
              if (!isset($draft_instance['hero_id']) || !$draft_instance['hero_id'])
                continue;
              
              $stage_sum = (1+(int)$draft_instance['is_pick'])*($draft_instance['team'] ? 1 : -1);
              $draft_stage = 0;

              if ($matchdata['version'] < 21) {
                if    (++$stages[$stage_sum] < 3) $draft_stage = 1;
                else if ($stages[$stage_sum] < 5) $draft_stage = 2;
                else $draft_stage = 3;
              } else {
                if($draft_instance['is_pick']) {
                  if    (++$stages[$stage_sum] < 3) $draft_stage = 1;
                  else if ($stages[$stage_sum] < 5) $draft_stage = 2;
                  else $draft_stage = 3;
                } else {
                  if    (++$stages[$stage_sum] < 4) $draft_stage = 1;
                  else if ($stages[$stage_sum] < 6) $draft_stage = 2;
                  else $draft_stage = 3;
                }
              }

              $t_draft[$i]['matchid'] = $match;
              $t_draft[$i]['is_radiant'] = $draft_instance['team'] ? 0 : 1;
              $t_draft[$i]['is_pick'] = $draft_instance['is_pick'];
              $t_draft[$i]['hero_id'] = $draft_instance['hero_id'];
              $t_draft[$i]['stage'] = $draft_stage;

              $i++;
          }
      } else if ($matchdata['game_mode'] == 16) {
          foreach ($matchdata['picks_bans'] as $draft_instance) {
              if (!isset($draft_instance['hero_id']) || !$draft_instance['hero_id'])
                continue;
          
              $t_draft[$i]['matchid'] = $match;
              $t_draft[$i]['is_radiant'] = $draft_instance['team'] ? 0 : 1;
              $t_draft[$i]['is_pick'] = $draft_instance['is_pick'];
              $t_draft[$i]['hero_id'] = $draft_instance['hero_id'];

              if ($draft_instance['is_pick']) {
                  if ($draft_instance['order'] < 11) $t_draft[$i]['stage'] = 1;
                  else if ($draft_instance['order'] < 15) $t_draft[$i]['stage'] = 2;
                  else $t_draft[$i]['stage'] = 3;
              } else {
                  $t_draft[$i]['stage'] = 1;
              }
              $i++;
          }
      } else if (!empty($matchdata['picks_bans_stratz']) && ($matchdata['game_mode'] == 22 || $matchdata['game_mode'] == 3)) {
        foreach ($matchdata['picks_bans_stratz'] as $draft_instance) {
          // I'm skipping opendota picks_bans information since it lacks information on
          // who nominated hero to ban and is missing pick order info
          // altho I guess I should reconsider adding it later
          if (!$draft_instance['isPick']) {
            if(!$draft_instance['wasBannedSuccessfully']) continue;
            $t_draft[$i]['matchid'] = $match;
            $t_draft[$i]['is_radiant'] = $draft_instance['isRadiant'];
            $t_draft[$i]['is_pick'] = 0;
            $t_draft[$i]['hero_id'] = $draft_instance['bannedHeroId'];
            $t_draft[$i]['stage'] = 1;
          } else {
            $t_draft[$i]['matchid'] = $match;
            $t_draft[$i]['is_radiant'] = $draft_instance['isRadiant'];
            $t_draft[$i]['is_pick'] = 1;
            $t_draft[$i]['hero_id'] = $draft_instance['heroId'];
            if ($draft_instance['order'] < 4) $t_draft[$i]['stage'] = 1;
            else if ($draft_instance['order'] < 8) $t_draft[$i]['stage'] = 2;
            else $t_draft[$i]['stage'] = 3;
          }
          $i++;
        }
      }
      
      if (empty($t_draft)) {
        foreach($matchdata['players'] as $draft_instance) {
            if (!isset($draft_instance['hero_id']) || !$draft_instance['hero_id'])
              continue;
            $t_draft[$i]['matchid'] = $match;
            $t_draft[$i]['is_radiant'] = $draft_instance['isRadiant'];
            $t_draft[$i]['is_pick'] = 1;
            $t_draft[$i]['hero_id'] = $draft_instance['hero_id'];
            $t_draft[$i]['stage'] = 1;
            $i++;
        }
      }

      echo "..Recording.";

      $sql = ""; $err_query = "";

      foreach ($t_new_players as $id => $player) {
        if ($player === true) continue;
        $player = mb_substr($player, 0, 127);
        $sql = "INSERT INTO players (playerID, nickname) VALUES (".$id.",\"".addslashes($player)."\");";
        if ($conn->query($sql) === TRUE) $t_players[$id] = true;
        else echo $conn->error."\n";
      }

      $sql = "INSERT INTO matches (matchid, radiantWin, duration, modeID, leagueID, start_date, stomp, comeback, cluster, version) VALUES "
        ."(".$t_match['matchid'].",".($t_match['radiantWin'] ? "true" : "false" ).",".$t_match['duration'].","
        .$t_match['modeID'].",".$t_match['leagueID'].",".$t_match['date'].","
        .$t_match['stomp'].",".$t_match['comeback'].",".$t_match['cluster'].",".$t_match['version'].");";
      $err_query = "delete from matches where matchid = ".$t_match['matchid'].";";

      if ($conn->multi_query($sql) === TRUE);
      else {
        echo "ERROR (".$conn->error."), reverting match.\n";
        $failed_matches[] = $t_match['matchid'];
        $conn->multi_query($err_query);
        do {
          $conn->store_result();
        } while($conn->next_result());
        continue;
      }

      $sql = "INSERT INTO matchlines (matchid, playerid, heroid, level, isRadiant, kills, deaths, assists, networth,".
              "gpm, xpm, heal, heroDamage, towerDamage, lastHits, denies) VALUES ";
      foreach($t_matchlines as $ml) {
          $sql .= "\n\t(".$ml['matchid'].",".$ml['playerid'].",".$ml['heroid'].",".
              $ml['level'].",".($ml['isRadiant'] ? "true" : "false").",".$ml['kills'].",".
              $ml['deaths'].",".$ml['assists'].",".$ml['networth'].",".
              $ml['gpm'].",".$ml['xpm'].",".$ml['heal'].",".
              $ml['heroDamage'].",".$ml['towerDamage'].",".$ml['lasthits'].",".
              $ml['denies']."),";
      }
      $sql[strlen($sql)-1] = ";";

      $err_query = "DELETE from matchlines where matchid = ".$t_match['matchid'].";".$err_query;

      if ($conn->multi_query($sql) === TRUE);
      else {
        echo "ERROR (".$conn->error."), reverting match.\n";
        $failed_matches[] = $t_match['matchid'];
        $conn->multi_query($err_query);
        do {
          $conn->store_result();
        } while($conn->next_result());
        continue;
      }

      if (!$bad_replay) {
        $sql = " INSERT INTO adv_matchlines (matchid, playerid, heroid, lh_at10, isCore, lane, efficiency_at10, wards, sentries,".
              "couriers_killed, roshans_killed, wards_destroyed, multi_kill, streak, stacks, time_dead, buybacks, pings, stuns, teamfight_part, damage_taken) VALUES ";

        foreach($t_adv_matchlines as $aml) {
          $sql .= "\n\t(".$aml['matchid'].",".$aml['playerid'].",".$aml['heroid'].",".
                      $aml['lh10'].",".$aml['is_core'].",".$aml['lane'].",".
                      $aml['lane_efficiency'].",".$aml['observers'].",".$aml['sentries'].",".
                      $aml['couriers_killed'].",".$aml['roshans_killed'].",".$aml['wards_destroyed'].",".
                      $aml['max_multikill'].",".$aml['max_streak'].",".$aml['stacks'].",".
                      $aml['time_dead'].",".$aml['buybacks'].",".$aml['pings'].",".
                      $aml['stuns'].",".$aml['teamfight_part'].",".$aml['damage_taken']."),";
        }
        $sql[strlen($sql)-1] = ";";

        $err_query = "DELETE from adv_matchlines where matchid = ".$t_match['matchid'].";".$err_query;

        if ($conn->multi_query($sql) === TRUE);
        else {
          echo "ERROR (".$conn->error."), reverting match.\n";
          $failed_matches[] = $t_match['matchid'];
          $conn->multi_query($err_query);
          do {
            $conn->store_result();
          } while($conn->next_result());
          continue;
        }
      }

      if(!empty($t_draft)) {
          $sql = " INSERT INTO draft (matchid, is_radiant, is_pick, hero_id, stage) VALUES ";
          $len = sizeof($t_draft);
          for($i = 0; $i < $len; $i++) {
              $sql .= "\n\t(".$t_draft[$i]['matchid'].",".($t_draft[$i]['is_radiant'] ? "true" : "false").",".
                          ($t_draft[$i]['is_pick'] ? "true" : "false").",".
                          $t_draft[$i]['hero_id'].",".$t_draft[$i]['stage']."),";
          }
          $sql[strlen($sql)-1] = ";";

          $err_query = "DELETE from draft where matchid = ".$t_match['matchid'].";".$err_query;

          if ($conn->multi_query($sql) === TRUE);
          else {
            echo "ERROR (".$conn->error."), reverting match.\n";
            $failed_matches[] = $t_match['matchid'];
            $conn->multi_query($err_query);
            do {
              $conn->store_result();
            } while($conn->next_result());
            continue;
          }
      }

      if ($lg_settings['main']['teams'] && sizeof($t_team_matches)) {
        $sql = "INSERT INTO teams_matches (matchid, teamid, is_radiant) VALUES ";

        foreach($t_team_matches as $match) {
            if($match['is_radiant'] > 1) {
              echo "[W] Error when adding teams-matches data: is_radiant flag has higher value than 1\n".
                  "[ ]\t".$match['matchid']." - ".$match['teamid']." - ".$match['is_radiant']."\n";
                  continue;
            }
            $sql .= "\n\t(".$match['matchid'].",".$match['teamid'].",".$match['is_radiant']."),";
        }
        $sql[strlen($sql)-1] = ";";

        $err_query = "DELETE from teams_matches where matchid = ".$t_match['matchid'].";".$err_query;

        if ($conn->multi_query($sql) === TRUE);
        else {
          echo "ERROR (".$conn->error."), reverting match.\n";
          $failed_matches[] = $t_match['matchid'];
          $conn->multi_query($err_query);
          do {
            $conn->store_result();
          } while($conn->next_result());
          continue;
        }
      }

      echo "..OK.\n";
  }
}

fetch($matches);
if($request_unparsed) {
  if (time() - $first_scheduled < $scheduled_wait_period)
    sleep($scheduled_wait_period);
  $first_scheduled = null;
  fetch($scheduled);
}
if($request_unparsed && $use_stratz) {
  if (time() - $first_scheduled < $scheduled_wait_period)
    sleep($scheduled_wait_period);
  $first_scheduled = null;
  fetch($scheduled_stratz);
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

if ($lg_settings['main']['teams']) {
  echo "[ ] Adding teams data\n";

  $newteams = array();
  foreach($t_teams as $id => $team) {
    if($team['added']) continue;
    $newteams[$id] = $team;
  }
  if(sizeof($newteams)) {
    $sql = "INSERT INTO teams (teamid, name, tag) VALUES \n";
    foreach ($newteams as $id => $team) {
      $sql .= "\n\t(".$id.",\"".addslashes($team['name'])."\",\"".addslashes($team['tag'])."\"),";
    }
    $sql[strlen($sql)-1] = ";";

        if ($conn->multi_query($sql) === TRUE) echo "[S] Successfully recorded new teams data to database.\n";
        else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

    if($lg_settings['ana']['teams']['rosters']) {
      echo "[ ] Getting teams rosters\n";

      $sql = "";

      foreach ($newteams as $id => $team) {
        $json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetTeamInfoByTeamID/v001/?key='.$steamapikey.'&teams_requested=1&start_at_team_id='.$id);
        $matchdata = json_decode($json, true);
        # it may return more than 5 players, but we actually care only about the first 5 players
        # others are probably coach and standins, they aren't part of official active roster

        # initial idea about positions was to detect player position somehow and use it in team competitions
        # to detect heros stats based on player positions
        # right now it's placeholder
        # TODO
        $position = 0;

        for($i=0; isset($matchdata['result']['teams'][0]['player_'.$i.'_account_id']); $i++)
            $sql .= "\n\t(".$id.",".$matchdata['result']['teams'][0]['player_'.$i.'_account_id'].", ".$position."),";
      }

      if(!empty($sql)) {
        $sql[strlen($sql)-1] = ";";
        $sql = "INSERT INTO teams_rosters (teamid, playerid, position) VALUES ".$sql;

            if ($conn->multi_query($sql) === TRUE) echo "[S] Successfully recorded new teams rosters to database.\n";
            else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n");
      }
    }
  }

  # TODO
  # teamID, playerID, position
} else {
  echo "[ ] Skipping team stats for PvP competition\n";
}

echo "[S] Fetch complete.\n";

?>
