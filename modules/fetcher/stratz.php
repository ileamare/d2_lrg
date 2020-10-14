<?php 

function get_stratz_response($match) {
  global $stratztoken, $meta;

  $ROSHAN = [133, 134, 135, 263, 264, 265, 324, 325, 326, 371, 593, 594, 595, 640];
  $OBS = [110, 499, 768];
  $SENTRY = [500, 769, 111];

  $data = [
    'query' => <<<Q
{ match(id: $match) {
    clusterId
    gameMode
    gameVersionId
    startDateTime
    leagueId
    durationSeconds
    parsedDateTime
    sequenceNum
    replaySalt
    regionId
    lobbyType
    id
    isStats
    stats {
      matchId
      radiantNetworthLeads
      radiantKills
      direKills
      pickBans {
        bannedHeroId
        heroId
        isPick
        isRadiant
        order
        playerIndex
        wasBannedSuccessfully
        team
      }
    }
    league {
      name
    }
    numHumanPlayers
    didRadiantWin
    players {
      steamAccountId
      heroId
      level
      isRadiant
      leaverStatus
      stats {
        campStack
        heroDamageReport {
          receivedTotal {
            magicalDamage
            physicalDamage
            pureDamage
          }
          dealtTotal {
            stunDuration
            disableDuration
          }
        }
        deniesPerMinute
        courierKills {
          time
        }
        lastHitsPerMinute
        networthPerMinute
        wards {
          type
        }
        deathEvents {
          timeDead
          time
          goldFed
        }
        killEvents {
          time
        }
        farmDistributionReport {
          creepType {
            count
            id
          }
          other {
            count
            id
          }
        }
        actionReport {
          pingUsed
        }
      }
      assists
      deaths
      experiencePerMinute
      heroDamage
      heroHealing
      lane
      kills
      goldPerMinute
      gold
      goldSpent
      networth
      role
      numLastHits
      numDenies
      towerDamage
      roleBasic
      playbackData {
        buyBackEvents {
          time
        }
      }
      steamAccount {
        name
      }
    }
    direTeam {
      name
      tag
    }
    direTeamId
    radiantTeamId
    radiantTeam {
      name
      tag
    }
  }
}
Q
  ];
  $data['query'] = str_replace("  ", "", $data['query']);
  $data['query'] = str_replace("\n", " ", $data['query']);

  if (!empty($stratztoken)) $data['token'] = $stratztoken;
    
  $stratz_request = "https://api.stratz.com/graphql";

  $q = http_build_query($data);
    
  // $context  = stream_context_create([
  //   'https' => [
  //     'method' => 'POST',
  //     'header'  => 'Content-Type: application/x-www-form-urlencoded'. 
  //       "\r\ncontent-length: ".strlen($q)."\r\ncontent-type: application/json",
  //     'content' => $q
  //   ]
  // ]);

  // $json = file_get_contents($stratz_request, false, $context);
  $json = @file_get_contents($stratz_request.'?'.$q);
  
  if (empty($json)) return null;

  $stratz = json_decode($json, true);
  
  if (empty($stratz['data']) && !empty($stratz['errors'])) {
    return null;
  }

  $r = [];

  $r['matches'] = [];
  $r['matches']['matchid'] = $stratz['data']['match']['id'];
  $r['matches']['radiantWin'] = $stratz['data']['match']['didRadiantWin'];
  $r['matches']['duration'] = $stratz['data']['match']['durationSeconds'];
  $r['matches']['modeID'] = $stratz['data']['match']['gameMode'];
  $r['matches']['cluster'] = $stratz['data']['match']['clusterId'];
  $r['matches']['start_date'] = $stratz['data']['match']['startDateTime'];
  $r['matches']['leagueID'] = $stratz['data']['match']['leagueId'] ?? 0;
  $r['matches']['version'] = get_patchid($r['matches']['start_date'], convert_patch_id($r['matches']['start_date']), $meta);

  if ($stratz['data']['match']['parsedDateTime']) {
    $throwVal = $stratz['data']['match']['didRadiantWin'] ? max($stratz['data']['match']['stats']['radiantNetworthLeads']) : min($stratz['data']['match']['stats']['radiantNetworthLeads']) * -1;
    $comebackVal = $stratz['data']['match']['didRadiantWin'] ? min($stratz['data']['match']['stats']['radiantNetworthLeads']) * -1 : max($stratz['data']['match']['stats']['radiantNetworthLeads']);
  
    $r['matches']['stomp'] = $stratz['data']['match']['didRadiantWin'] ? $throwVal : $comebackVal;
    $r['matches']['comeback'] = $stratz['data']['match']['didRadiantWin'] ? $comebackVal : $throwVal;
  } else {
    $r['matches']['stomp'] = 0;
    $r['matches']['comeback'] = 0;
  }

  $r['payload'] = [
    'score_radiant' => 0,
    'score_dire' => 0,
    'leavers' => 0
  ];

  $r['matchlines'] = [];
  $r['adv_matchlines'] = [];
  $r['players'] = [];

  foreach ($stratz['data']['match']['players'] as $i => $pl) {
    $r['payload']['score_radiant'] += $pl['isRadiant'] ? $pl['kills'] : 0;
    $r['payload']['score_dire'] += !$pl['isRadiant'] ? $pl['kills'] : 0;
    $r['payload']['leavers'] += $pl['leaverStatus'];

    $ml = [];
    $ml['matchid'] = $stratz['data']['match']['id'];
    $ml['playerid'] = $pl['steamAccountId'];
    $ml['heroid'] = $pl['heroId'];
    $ml['isRadiant'] = $pl['isRadiant'];
    $ml['level'] = $pl['level'];
    $ml['kills'] = $pl['kills'];
    $ml['deaths'] = $pl['deaths'];
    $ml['assists'] = $pl['assists'];
    $ml['networth'] = $pl['networth'];
    $ml['gpm'] = $pl['goldPerMinute'];
    $ml['xpm'] = $pl['experiencePerMinute'];
    $ml['heal'] = $pl['heroHealing'];
    $ml['heroDamage'] = $pl['heroDamage'];
    $ml['towerDamage'] = $pl['towerDamage'];
    $ml['lastHits'] = $pl['numLastHits'];
    $ml['denies'] = $pl['numDenies'];

    $r['matchlines'][] = $ml;

    $r['players'][] = [
      'playerID' => $pl['steamAccountId'],
      'nickname' => $pl['steamAccount']['name']
    ];

    if ($stratz['data']['match']['parsedDateTime']) {
      $aml = [];

      $aml['matchid'] = $stratz['data']['match']['id'];
      $aml['playerid'] = $pl['steamAccountId'];
      $aml['heroid'] = $pl['heroId'];

      $aml['lh_at10'] = array_sum(
        array_slice($pl['stats']['lastHitsPerMinute'], 0, 10)
      );
      $aml['lane'] = $pl['lane'] > 3 || !$pl['lane'] ? 4 : $pl['lane'];
      $aml['isCore'] = $pl['roleBasic'] ? 0 : 1;
      
      $melee = (40 * 60);
      $ranged = (45 * 20);
      $siege = (74 * 2);
      $passive = (600 * 1.5);
      $starting = 625;
      $tenMinute = $melee + $ranged + $siege + $passive + $starting;
      $aml['efficiency_at10'] = $pl['stats']['networthPerMinute'][10] / $tenMinute;
      
      $aml['wards'] = count(
        array_filter($pl['stats']['wards'], function($a) { return !$a['type']; })
      );
      $aml['wards'] = count(
        array_filter($pl['stats']['wards'], function($a) { return $a['type']; })
      );

      $aml['couriers_killed'] = count($pl['stats']['courierKills']);

      $aml['roshans_killed'] = 0;
      $aml['wards_destroyed'] = 0;

      foreach ($pl['stats']['farmDistributionReport'] as $f) {
        foreach ($f['creepType'] as $fc) {
          if (in_array($fc['id'], $ROSHAN)) $aml['roshans_killed'] += $fc['count'];
        }
        foreach ($f['other'] as $fc) {
          if (in_array($fc['id'], $OBS)) $aml['wards_destroyed'] += $fc['count'];
        }
      }
      
      $kde = [];
      foreach ($pl['stats']['killEvents'] as $s) {
        $kde[] = [
          'time' => $s['time'],
          'kill' => true
        ];
      }
      foreach ($pl['stats']['deathEvents'] as $s) {
        if (!$s['goldFed']) continue;
        $kde[] = [
          'time' => $s['time'],
          'kill' => false
        ];
      }
      usort($kde, function($a, $b) { return $a['time'] <=> $b['time']; });

      $streaks = [];
      $multis = [];
      $cur_streak = 0;
      $cur_multi = 1;
      $last = 0;
      foreach ($kde as $e) {
        if ($e['kill']) {
          $cur_streak++;

          if ($e['time'] - $last < 18) {
            $cur_multi++;
          } else {
            $multis[] = $cur_multi;
            $cur_multi = 1;
          }

          $last = $e['time'];
        } else {
          $streaks[] = $cur_streak;
          $cur_streak = 0;
        }
      }
      $streaks[] = $cur_streak;
      $multis[] = count($kde) ? $cur_multi : 0;

      $aml['multi_kill'] = !empty($multis) ? max($multis) : 0;
      var_dump($multis);
      $aml['streak'] = !empty($streaks) ? max($streaks) : 0;
      var_dump($streaks);
      var_dump("");
      
      $aml['stacks'] = max($pl['stats']['campStack']);
      
      $aml['time_dead'] = array_reduce($pl['stats']['deathEvents'], function($c, $a) { return $c + $a['timeDead']; }, 0);
      $aml['buybacks'] = 0;//!empty($pl['playbackData']['buyBackEvents']) ? count($pl['playbackData']['buyBackEvents']) : 0;
      $aml['pings'] = $pl['stats']['actionReport']['pingUsed'] ?? 0;
      
      $aml['stuns'] = ($pl['stats']['heroDamageReport']['dealtTotal']['stunDuration'] + $pl['stats']['heroDamageReport']['dealtTotal']['disableDuration'])/100;

      $aml['teamfight_part'] = ($pl['kills']+$pl['assists']) / ( $pl['isRadiant'] ? array_sum($stratz['data']['match']['stats']['radiantKills']) : array_sum($stratz['data']['match']['stats']['direKills']));
      $aml['damage_taken'] = array_sum($pl['stats']['heroDamageReport']['receivedTotal']);

      $r['adv_matchlines'][] = $aml;
    }
  }

  $r['draft'] = [];
  if (!empty($pl['stats']['pickBans'])) {
    foreach ($pl['stats']['pickBans'] as $dr) {
      $d = [];

      $d['matchid'] = $match;
      $d['is_radiant'] = $dr['isRadiant'] ? 1 : 0;
      $d['is_pick'] = $dr['isPick'] ? 1 : 0;
      $d['hero_id'] = $dr['heroId'];

      if ($r['matches']['modeID'] == 2 || $r['matches']['modeID'] == 9) {
        $last_stage_pick = null;
        if ($last_stage_pick !== $pick && !$pick) {
          $stage++;
        }
        $d['stage'] = $stage;
      } else if ($r['matches']['modeID'] == 16) {
        if ($dr['isPick']) {
          if ($dr['order'] < 11) $d['stage'] = 1;
          else if ($dr['order'] < 15) $d['stage'] = 2;
          else $d['stage'] = 3;
        } else {
            $d['stage'] = 1;
        }
      } else if ($r['matches']['modeID'] == 22 || $r['matches']['modeID'] == 3) {
        if ($dr['isPick']) {
          if ($dr['order'] < 4) $d['stage'] = 1;
          else if ($dr['order'] < 8) $d['stage'] = 2;
          else $d['stage'] = 3;
        } else $d['stage'] = 1;
      } else {
        $d['stage'] = 1;
      }

      $r['draft'][] = $d;
    }
  } else {
    foreach($stratz['data']['match']['players'] as $draft_instance) {
      if (!isset($draft_instance['hero_id']) || !$draft_instance['hero_id'])
        continue;
      $d['matchid'] = $match;
      $d['is_radiant'] = $draft_instance['isRadiant'];
      $d['is_pick'] = 1;
      $d['hero_id'] = $draft_instance['heroId'];
      $d['stage'] = 1;
      
      $r['draft'][] = $d;
    }
  }

  if (!empty($stratz['data']['match']['radiantTeamId']) || !empty($stratz['data']['match']['direTeamId'])) {
    $r['teams_matches'] = [];
    $r['teams'] = [];

    if (!empty($stratz['data']['match']['direTeamId'])) {
      $r['teams_matches'][] = [
        'matchid' => $stratz['data']['match']['id'],
        'teamid' => $stratz['data']['match']['direTeamId'],
        'is_radiant' => 0
      ];

      $r['teams'][] = [
        'teamid' => $stratz['data']['match']['direTeamId'],
        'name' => $stratz['data']['match']['direTeam']['name'] ?? "Team ".$stratz['data']['match']['direTeamId'],
        'tag' => $stratz['data']['match']['direTeam']['tag'] ?? generate_tag($stratz['data']['match']['direTeam']['name'] ?? "Team ".$stratz['data']['match']['direTeamId']),
      ];
    }

    if (!empty($stratz['data']['match']['radiantTeamId'])) {
      $r['teams_matches'][] = [
        'matchid' => $stratz['data']['match']['id'],
        'teamid' => $stratz['data']['match']['radiantTeamId'],
        'is_radiant' => 1
      ];

      $r['teams'][] = [
        'teamid' => $stratz['data']['match']['radiantTeamId'],
        'name' => $stratz['data']['match']['radiantTeam']['name'] ?? "Team ".$stratz['data']['match']['radiantTeamId'],
        'tag' => $stratz['data']['match']['radiantTeam']['tag'] ?? generate_tag($stratz['data']['match']['radiantTeam']['name'] ?? "Team ".$stratz['data']['match']['radiantTeamId']),
      ];
    }
  }

  return $r;
}