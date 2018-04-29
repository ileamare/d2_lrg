#!/bin/php
<?php
ini_set('memory_limit', '4000M');

include_once("head.php");
include_once("modules/fetcher/get_patchid.php");
include_once("modules/functions/migrate_params.php");
include_once("modules/functions/generate_tag.php");

echo("\nInitialising...\n");

#$file_query = "";
$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);
$meta = json_decode(file_get_contents('res/metadata.json'), true);

$stratz_old_api_endpoint = 3707179408;

$opendota = new odota_api();

if ($conn->connect_error) die("[F] Connection to SQL server failed: ".$conn->connect_error."\n");

$lrg_input  = "matchlists/".$lrg_league_tag.".list";

$matches = array ();
$failed_matches = array ();

$input_cont = file_get_contents($lrg_input);
$input_cont = str_replace("\r\n", "\n", $input_cont);

$matches    = explode("\n", trim($input_cont));

$matches = array_unique($matches);

$json = "";
$t_matches = array ();
$t_matchlines = array();
$t_adv_matchlines = array ();
$t_draft = array ();
if ($lg_settings['main']['teams']) {
    $t_team_matches = array();
    $t_teams = array();

    $sql = "SELECT teamid, name, tag FROM teams";
    if ($conn->multi_query($sql)) {
      $res = $conn->store_result();

      while ($row = $res->fetch_row()) {
        $t_teams[$row[0]] = array(
          "name"  => $row[1],
          "tag"   => $row[2],
          "added" => true
        );
      }
      $res->free();
    }
}

$t_players = array();
$sql = "SELECT playerid FROM players";
if ($conn->multi_query($sql)) {
  $res = $conn->store_result();

  while ($row = $res->fetch_row()) {
    $t_players[$row[0]] = true;
  }
  $res->free();
}


foreach ($matches as $match) {
    if (empty($match) || $match[0] == "#") continue;

    $query = $conn->query("SELECT matchid FROM matches WHERE matchid = ".$match.";");

    if ($query->num_rows) {
        echo("[E] Match $match: Already have it in database, skipping\n");
        continue;
    }

    if($lrg_use_cache && file_exists("cache/".$match.".json")) {
      echo("[_] Match $match: Found local cached version, reusing it\n");
      $json = file_get_contents("cache/".$match.".json");
      $matchdata = json_decode($json, true);
    } else {
      echo("[_] Match $match: Requesting OpenDota API\n");
      $matchdata = $opendota->match($match);
      echo("[S] Match $match: Request OK\n");
      if ($matchdata == null || !isset($matchdata['duration'])) {
          echo("[E] Match $match: Can't parse JSON from OpenDota, skipping\n");
          //if (!isset($matchdata['duration'])) var_dump($matchdata);
          $failed_matches[sizeof($failed_matches)] = $match;
          continue;
      } else {
        if ($matchdata['players'][0]['lh_t'] == null) {
            echo("[E] Match $match: Replay is not parsed, skipping\n");
            $failed_matches[sizeof($failed_matches)] = $match;
            continue;
        }
      }
    }

    if($matchdata['duration'] < 600) {
        echo("[ ] Match duration is less than 10 minutes, skipping...\n");
        // Initially it used to be 5 minutes, but sice a lot of stuff is hardly
        // binded with 10 min mark, it's better to use 10 min as a benchmark.
        continue;
    }
    if($matchdata['radiant_score'] < 5 && $matchdata['dire_score'] < 5) {
        echo("[ ] Match score is less than 5 - 5, skipping...\n");
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
        echo("[ ] Abandon detected, skipping...\n");
        continue;
    }

    if(!file_exists("cache/".$match.".json")) {
      if($matchdata['lobby_type'] != 1 && $matchdata['lobby_type'] != 2) {
        echo("[ ] Requesting Stratz for additional match data.\n");

        // Not all matches in Stratz database have PickBan support for /match? endpoint
        // so there will be kind of workaround for it.

        $request = "https://api.stratz.com/api/v1/match?include=Player,PickBan&matchid=$match";

        $json = file_get_contents($request);

        if(empty($json)) {
            echo("[E] Match $match: Missing Stratz report, skipping\n");
          $failed_matches[sizeof($failed_matches)] = $match;
          continue;
        }

        $stratz = json_decode($json, true);

        if(!isset($stratz['results'][0]['parsedDate'])) {
          echo("[E] Match $match: Missing Stratz analysis, skipping\n");
          $failed_matches[sizeof($failed_matches)] = $match;
          continue;
        }

        $full_request = false;
        if($matchdata['game_mode'] == 22 || $matchdata['game_mode'] == 3) {
          while(!isset($stratz['results'][0]['pickBans']) || $stratz['results'][0]['pickBans'] === NULL) {
            echo "[E] $match: Stratz draft data error. Retrying request in 5 seconds...\n";
            `sleep 5`;

            if (!isset($stratz['results'][0]['pickBans'])) {
                $request = "https://api.stratz.com/api/v1/match/$match";
                $full_request = true;
            }

            $json = file_get_contents($request);

            if ($full_request)
                $stratz = array( "results" => array ( json_decode($json, true) ) );
            else $stratz = json_decode($json, true);

            if($full_request && strlen($json) < 6500) {
                echo("[E] Match $match: Missing Stratz analysis, try again later\n");
                $failed_matches[sizeof($failed_matches)] = $match;
                break;
            }
          }
          if(in_array($match, $failed_matches)) continue;

          $matchdata['picks_bans'] = $stratz['results'][0]['pickBans'];
        }

        for($i=0; $i<10; $i++) {
          if(!isset($matchdata['players'][$i]['account_id']) || $matchdata['players'][$i]['account_id'] === null) {
            $matchdata['players'][$i]['account_id'] = $stratz['results'][0]['players'][$i]['steamId'];
            $tmp = $opendota->player($matchdata['players'][$i]['account_id']);

            $matchdata['players'][$i]["name"] = $stratz['results'][0]['players'][$i]['name'];
            if(isset($tmp['profile']['personaname']))
              $matchdata['players'][$i]["personaname"] = $tmp['profile']['personaname'];
          }
        }

        echo("[ ] Stratz data merged.\n");

        unset($stratz);
        unset($full_request);
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
                    $matchdata['dire_team']['tag'] = generate_tag($tmp['result']['radiant_name']);
                else
                    $matchdata['dire_team']['tag'] = $team['result']['teams'][0]['tag'];
              }
            }
          }
      }

      unset($matchdata['chat']);
      unset($matchdata['cosmetics']);

      $json = json_encode($matchdata);
      if($lrg_use_cache) {
        $f = fopen("cache/".$match.".json", "w");
        fwrite($f, $json);
        fclose($f);

        echo("[S] Match $match: Saved cached version\n");
      }
    }

    unset($json);

    $i = sizeof($t_matches);
    $t_matches[$i]['matchid'] = $match;
    $t_matches[$i]['version'] = get_patchid($matchdata['start_time'], $matchdata['patch']);
    $t_matches[$i]['radiantWin'] = $matchdata['radiant_win'];
    $t_matches[$i]['duration'] = $matchdata['duration'];
    $t_matches[$i]['modeID'] = $matchdata['game_mode'];
    $t_matches[$i]['leagueID'] = $matchdata['leagueid'];
    $t_matches[$i]['cluster']  = $matchdata['cluster'];
    $t_matches[$i]['date'] = $matchdata['start_time'];
    if (isset($matchdata['stomp']))
         $t_matches[$i]['stomp'] = $matchdata['stomp'];
    else $t_matches[$i]['stomp'] = $matchdata['loss'];
    if (isset($matchdata['comeback']))
         $t_matches[$i]['comeback'] = $matchdata['comeback'];
    else $t_matches[$i]['comeback'] = $matchdata['throw'];

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
    for ($j = 0; $j < 10; $j++, $i++) {
        $t_matchlines[$i]['matchid'] = $match;
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
            $t_players[$matchdata['players'][$j]['account_id']] = "Bot ".$meta['heroes'][$matchdata['players'][$j]['hero_id']]['name'];
          } else {
            if (isset($matchdata['players'][$j]["name"]) && $matchdata['players'][$j]["name"] != null) {
              $t_players[$matchdata['players'][$j]['account_id']] = $matchdata['players'][$j]["name"];
            } else if ($matchdata['players'][$j]["personaname"] != null) {
              $t_players[$matchdata['players'][$j]['account_id']] = $matchdata['players'][$j]["personaname"];
            } else
              $t_players[$matchdata['players'][$j]['account_id']] = "Player ".$matchdata['players'][$j]['account_id'];
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
        $t_adv_matchlines[$i]['lh10'] = $matchdata['players'][$j]['lh_t'][10];
        if ($matchdata['players'][$j]['lane_role'] == 5)
            $matchdata['players'][$j]['lane_role'] = 4; # we don't care about different jungles
        //if ($matchdata['players'][$j]['is_roaming'])
        //    $matchdata['players'][$j]['lane_role'] = 5;
        $t_adv_matchlines[$i]['lane'] = $matchdata['players'][$j]['lane_role'];

        # trying to decide, is it a core
        $support_indicators = 0;
        //if ($matchdata['players'][$j]['lane_role'] == 4) $support_indicators+=2;
        if ($matchdata['players'][$j]['lh_t'][5] <= 6) $support_indicators++;
        if ($matchdata['players'][$j]['lh_t'][3] <= 2) $support_indicators++;
        if ($matchdata['players'][$j]['obs_placed'] > 1) $support_indicators++;
        if ($matchdata['players'][$j]['obs_placed'] > 5) $support_indicators++;
        if ($matchdata['players'][$j]['obs_placed'] > 10) $support_indicators++;
        if ($matchdata['players'][$j]['gold_per_min'] < 355) $support_indicators++;
        if ($matchdata['players'][$j]['gold_per_min'] < 290) $support_indicators++;
        if ($matchdata['players'][$j]['lane_efficiency'] < 0.45) $support_indicators++;
        if ($matchdata['players'][$j]['lane_efficiency'] < 0.35) $support_indicators++;
        if ($matchdata['players'][$j]['hero_damage']*60/$matchdata['duration'] < 350) $support_indicators++;
        if ($matchdata['players'][$j]['last_hits']*60/$matchdata['duration'] < 2.2) $support_indicators++;

        if ($matchdata['players'][$j]['is_roaming']) {
            $support_indicators+=3;
        }

        if ($support_indicators > 4) $t_adv_matchlines[$i]['is_core'] = 0;
        else $t_adv_matchlines[$i]['is_core'] = 1;

        if ($t_adv_matchlines[$i]['is_core'] && $matchdata['players'][$j]['is_roaming'])
            $t_adv_matchlines[$i]['lane'] = $matchdata['players'][$j]['lane_role'];
        else if (!$t_adv_matchlines[$i]['is_core'] && $matchdata['players'][$j]['is_roaming'])
            $t_adv_matchlines[$i]['lane'] = 5;
            # Gonna put roaming cores into junglers for now


        $t_adv_matchlines[$i]['lane_efficiency'] = $matchdata['players'][$j]['lane_efficiency'];
        $t_adv_matchlines[$i]['observers'] = $matchdata['players'][$j]['obs_placed'];
        $t_adv_matchlines[$i]['sentries'] = $matchdata['players'][$j]['sen_placed'];
        $t_adv_matchlines[$i]['couriers_killed'] = $matchdata['players'][$j]['courier_kills'];
        $t_adv_matchlines[$i]['roshans_killed'] = $matchdata['players'][$j]['roshan_kills'];
        $t_adv_matchlines[$i]['wards_destroyed'] = $matchdata['players'][$j]['observer_kills'];
        if (count($matchdata['players'][$j]['multi_kills']) == 0) $t_adv_matchlines[$i]['max_multikill'] = 0;
        else $t_adv_matchlines[$i]['max_multikill'] = end(array_keys($matchdata['players'][$j]['multi_kills']));
        if (count($matchdata['players'][$j]['kill_streaks']) == 0) $t_adv_matchlines[$i]['max_streak'] = 0;
        else $t_adv_matchlines[$i]['max_streak'] = end(array_keys($matchdata['players'][$j]['kill_streaks']));
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
    } else if ($matchdata['game_mode'] == 22) {
      foreach ($matchdata['picks_bans'] as $draft_instance) {
        # ban nominants counts as bans ? Need to thing about that.
        if (!$draft_instance['isPick']) {
          if(!$draft_instance['wasBannedSuccessfully']) continue;
          $t_draft[$i]['matchid'] = $match;
          $t_draft[$i]['is_radiant'] = ($draft_instance['playerIndex'] < 5) ? 1 : 0;
          $t_draft[$i]['is_pick'] = 0;
          $t_draft[$i]['hero_id'] = $draft_instance['heroId'];
          $t_draft[$i]['stage'] = 1;
        } else {
          $t_draft[$i]['matchid'] = $match;
          $t_draft[$i]['is_radiant'] = ($draft_instance['playerIndex'] < 5) ? 1 : 0;
          $t_draft[$i]['is_pick'] = 1;
          $t_draft[$i]['hero_id'] = $draft_instance['heroId'];
          if ($draft_instance['order'] < 2) $t_draft[$i]['stage'] = 1;
          else if ($draft_instance['order'] < 4) $t_draft[$i]['stage'] = 2;
          else $t_draft[$i]['stage'] = 3;
        }
        $i++;
      }
    } else if ($matchdata['game_mode'] == 3) {
      foreach ($matchdata['picks_bans'] as $draft_instance) {
        $t_draft[$i]['matchid'] = $match;
        $t_draft[$i]['is_radiant'] = ($draft_instance['playerIndex'] < 5) ? 1 : 0;
        $t_draft[$i]['is_pick'] = 1;
        $t_draft[$i]['hero_id'] = $draft_instance['heroId'];
        if ($draft_instance['order'] < 2) $t_draft[$i]['stage'] = 1;
        else if ($draft_instance['order'] < 4) $t_draft[$i]['stage'] = 2;
        else $t_draft[$i]['stage'] = 3;
        $i++;
      }
    } else {
        foreach($matchdata['players'] as $draft_instance) {
            $t_draft[$i]['matchid'] = $match;
            $t_draft[$i]['is_radiant'] = $draft_instance['isRadiant'];
            $t_draft[$i]['is_pick'] = 1;
            $t_draft[$i]['hero_id'] = $draft_instance['hero_id'];
            $t_draft[$i]['stage'] = 1;
            $i++;
        }
    }


}

# recording to database

if (sizeof($t_matches) == 0) die ("[W] No matches to record, exitting...\n");

echo "[ ] Collecting players data\n";

$new_players = false;

$sql = "INSERT INTO players (playerID, nickname) VALUES ";
foreach ($t_players as $id => $player) {
  if ($player === true) continue;
  if (!$new_players) $new_players = true;
  $sql .= "\n\t(".$id.",\"".addslashes($player)."\"),";
}
$sql[strlen($sql)-1] = ";";

if ($new_players) {
    if(isset($file_query)) $file_query .= $sql."\n\n\n";
    else
    if ($conn->multi_query($sql) === TRUE) echo "[S] Successfully recorded new players data to database.\n";
    else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n$sql\n");
}

$len = sizeof($t_matches);
$sql = "INSERT INTO matches (matchid, radiantWin, duration, modeID, leagueID, start_date, stomp, comeback, cluster, version) VALUES ";
for($i = 0; $i < $len; $i++) {
    $sql .= "\n\t(".$t_matches[$i]['matchid'].",".($t_matches[$i]['radiantWin'] ? "true" : "false" ).",".$t_matches[$i]['duration'].","
               .$t_matches[$i]['modeID'].",".$t_matches[$i]['leagueID'].",".$t_matches[$i]['date'].","
               .$t_matches[$i]['stomp'].",".$t_matches[$i]['comeback'].",".$t_matches[$i]['cluster'].",".$t_matches[$i]['version']."),";
}
$sql[strlen($sql)-1] = ";";

if(isset($file_query)) $file_query .= $sql."\n\n\n";
else
    if ($conn->multi_query($sql) === TRUE) echo "[S] Successfully recorded matches to database.\n";
    else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n$sql\n");

$sql = " INSERT INTO matchlines (matchid, playerid, heroid, level, isRadiant, kills, deaths, assists, networth,".
        "gpm, xpm, heal, heroDamage, towerDamage, lastHits, denies) VALUES ";
$len = sizeof($t_matchlines);
for($i = 0; $i < $len; $i++) {
    $sql .= "\n\t(".$t_matchlines[$i]['matchid'].",".$t_matchlines[$i]['playerid'].",".$t_matchlines[$i]['heroid'].",".
                $t_matchlines[$i]['level'].",".($t_matchlines[$i]['isRadiant'] ? "true" : "false").",".$t_matchlines[$i]['kills'].",".
                $t_matchlines[$i]['deaths'].",".$t_matchlines[$i]['assists'].",".$t_matchlines[$i]['networth'].",".
                $t_matchlines[$i]['gpm'].",".$t_matchlines[$i]['xpm'].",".$t_matchlines[$i]['heal'].",".
                $t_matchlines[$i]['heroDamage'].",".$t_matchlines[$i]['towerDamage'].",".$t_matchlines[$i]['lasthits'].",".
                $t_matchlines[$i]['denies']."),";
}
$sql[strlen($sql)-1] = ";";

if(isset($file_query)) $file_query .= $sql."\n\n\n";
else
    if ($conn->multi_query($sql) === TRUE) echo "[S] Successfully recorded matchlines to database.\n";
    else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n$sql\n");

$sql = " INSERT INTO adv_matchlines (matchid, playerid, heroid, lh_at10, isCore, lane, efficiency_at10, wards, sentries,".
        "couriers_killed, roshans_killed, wards_destroyed, multi_kill, streak, stacks, time_dead, buybacks, pings, stuns, teamfight_part, damage_taken) VALUES ";
for($i = 0; $i < $len; $i++) {
    $sql .= "\n\t(".$t_adv_matchlines[$i]['matchid'].",".$t_adv_matchlines[$i]['playerid'].",".$t_adv_matchlines[$i]['heroid'].",".
                $t_adv_matchlines[$i]['lh10'].",".$t_adv_matchlines[$i]['is_core'].",".$t_adv_matchlines[$i]['lane'].",".
                $t_adv_matchlines[$i]['lane_efficiency'].",".$t_adv_matchlines[$i]['observers'].",".$t_adv_matchlines[$i]['sentries'].",".
                $t_adv_matchlines[$i]['couriers_killed'].",".$t_adv_matchlines[$i]['roshans_killed'].",".$t_adv_matchlines[$i]['wards_destroyed'].",".
                $t_adv_matchlines[$i]['max_multikill'].",".$t_adv_matchlines[$i]['max_streak'].",".$t_adv_matchlines[$i]['stacks'].",".
                $t_adv_matchlines[$i]['time_dead'].",".$t_adv_matchlines[$i]['buybacks'].",".$t_adv_matchlines[$i]['pings'].",".
                $t_adv_matchlines[$i]['stuns'].",".$t_adv_matchlines[$i]['teamfight_part'].",".$t_adv_matchlines[$i]['damage_taken']."),";
}
$sql[strlen($sql)-1] = ";";

if(isset($file_query)) $file_query .= $sql."\n\n\n";
else
    if ($conn->multi_query($sql) === TRUE) echo "[S] Successfully recorded adv matchlines to database.\n";
    else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n$sql\n");

if(!empty($t_draft)) {
    $sql = " INSERT INTO draft (matchid, is_radiant, is_pick, hero_id, stage) VALUES ";
    $len = sizeof($t_draft);
    for($i = 0; $i < $len; $i++) {
        $sql .= "\n\t(".$t_draft[$i]['matchid'].",".($t_draft[$i]['is_radiant'] ? "true" : "false").",".
                    ($t_draft[$i]['is_pick'] ? "true" : "false").",".
                    $t_draft[$i]['hero_id'].",".$t_draft[$i]['stage']."),";
    }
    $sql[strlen($sql)-1] = ";";

    if(isset($file_query)) $file_query .= $sql."\n\n\n";
    else
        if ($conn->multi_query($sql) === TRUE) echo "[S] Successfully recorded draft to database.\n";
        else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n$sql\n");
}

if (sizeof($failed_matches)) {
  echo "\nUnparsed matches: \n";
  foreach ($failed_matches as $fm)
      echo "\t$fm\n";

  echo "\n[_] Recording failed matches to file...\n";

  $output = implode("\n", $failed_matches);
  $filename = "tmp_fm".time();
  $f = fopen($filename, "w");
  fwrite($f, $output);
  fclose($f);

  echo "[S] Recorded failed matches to $filename\n";
}

if ($lg_settings['main']['teams']) {
  echo "[ ] Adding new team matches\n";

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

  if(isset($file_query)) $file_query .= $sql."\n\n\n";
    else
    if ($conn->multi_query($sql) === TRUE) echo "[S] Successfully recorded new team matches data to database.\n";
    else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

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

    if(isset($file_query)) $file_query .= $sql."\n\n\n";
    else
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

        if(isset($file_query)) $file_query .= $sql."\n\n\n";
        else
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

if (isset($file_query)) {
  $f = fopen("query.sql", "w");
  fwrite($f, $file_query);
  fclose($f);

  echo "[S] Recorded SQL query to file.\n";
}

echo "[S] Fetch complete.\n";

?>
