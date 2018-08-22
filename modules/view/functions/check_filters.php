<?php

define( 'LRG_CAT_FILTER_TAG', 0 );
define( 'LRG_TAG_FILTER_NAME', 1 );
define( 'LRG_TAG_FILTER_DESC', 2 );
define( 'LRG_TAG_FILTER_ID', 3 );
define( 'LRG_TAG_FILTER_DATE_START', 4 );
define( 'LRG_TAG_FILTER_DATE_END', 5 );
define( 'LRG_TAG_FILTER_MID_START', 6 );
define( 'LRG_TAG_FILTER_MID_END', 7 );
define( 'LRG_TAG_FILTER_TEAM_IN', 8 );
define( 'LRG_TAG_FILTER_PLAYER_IN', 9 );
define( 'LRG_TAG_FILTER_REGION_IN', 10 );
define( 'LRG_TAG_FILTER_REPORT_TYPE_TEAMS', 11 );
define( 'LRG_TAG_FILTER_FOLDER', 12 );
define( 'LRG_TAG_FILTER_MATCHES_TOTAL_LESS', 13 );
define( 'LRG_TAG_FILTER_MATCHES_TOTAL_MORE', 14 );

/*
filters:
  name - mask (preg)
  desc - mask (preg)
  id
  dates - check dates range (unix timestamps)
  matchids - check matchids range
  regions - check included regions
  report type (team/player/centric)
  folder
  matc
*/



function check_filters($rep, $filters) {
  if(empty($filters)) return false;

  $result = false;
  foreach($filters as $group) {
    if(empty($group)) {
      continue;
    }

    $group_result = true;
    foreach($group as $filter) {
      if(empty($filter)) {
        $group_result = false;
        break;
      }

      switch ($filter[0]) {
        case LRG_CAT_FILTER_TAG:
          $group_result = $group_result && preg_match($filter[1], $rep['tag']);
          break;
        case LRG_TAG_FILTER_NAME:
          $group_result = $group_result && preg_match($filter[1], $rep['name']);
          break;
        case LRG_TAG_FILTER_DESC:
          $group_result = $group_result && preg_match($filter[1], $rep['desc']);
          break;
        case LRG_TAG_FILTER_ID:
          $group_result = $group_result && ($filter[1] == $rep['id']);
          break;
        case LRG_TAG_FILTER_DATE_START:
          $group_result = $group_result && ($filter[1] <= $rep['first_match']['date']);
          break;
        case LRG_TAG_FILTER_DATE_END:
          $group_result = $group_result && ($filter[1] >= $rep['last_match']['date']);
          break;
        case LRG_TAG_FILTER_MID_START:
          $group_result = $group_result && ($filter[1] <= $rep['first_match']['mid']);
          break;
        case LRG_TAG_FILTER_MID_END:
          $group_result = $group_result && ($filter[1] >= $rep['last_match']['mid']);
          break;
        case LRG_TAG_FILTER_TEAM_IN:
          $group_result = $group_result && (isset($rep['teams']) && in_array($filter[1], $rep['teams']));
          break;
        case LRG_TAG_FILTER_PLAYER_IN:
          $group_result = $group_result && (isset($rep['players']) && in_array($filter[1], $rep['players']));
          break;
        case LRG_TAG_FILTER_REGION_IN:
          $group_result = $group_result && (isset($rep['regions']) && in_array($filter[1], $rep['regions']));
          break;
        case LRG_TAG_FILTER_REPORT_TYPE_TEAMS:
          $group_result = $group_result && $rep['tvt'];
          break;
        case LRG_TAG_FILTER_FOLDER:
          if(isset($rep['file'])) {
            $tmp = explode("/", $rep['file']);
            $group_result = $group_result && in_array($filter[1], $tmp);
          } else $group_result = false;
          break;
        case LRG_TAG_FILTER_MATCHES_TOTAL_LESS:
          $group_result = $group_result && ($filter[1] > $rep['matches']);
          break;
        case LRG_TAG_FILTER_MATCHES_TOTAL_MORE:
          $group_result = $group_result && ($filter[1] < $rep['matches']);
          break;
      }
    }

    $result = $result || $group_result;
  }

  return $result;
}

?>
