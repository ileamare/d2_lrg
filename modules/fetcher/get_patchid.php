<?php
 /*
  * League Report Generator - Patch detection - Fetcher Module
  *
  * So, the thing is we should probably work around current patchids that were
  * used in opendota API.
  *
  * Problem with old IDs: they are based around "big" patches, it seems.
  * Actual patch detection on opendota uses a bunch of constants for dates .
  *
  * This module should improve this aspect and make it easier to work around
  * patch versions, while also being based on actual dates constants.
  *
  * This is not optimal way, but it's easier for reports parsing later on
  * and it also works well with old Metadata.json file and will be
  * compatible with old reports.
  *
  * Parameters:
  *  $date  - UNIX tmestamp of the match, int
  *  $patch - patch ID from API response, int
  * Returns:
  *  Converted Patch ID (int) to support minor lettered patches
  */

  function get_patchid($date, $patch) {
    # So the way it works:
    # report displayer will check for analyzer version and if it's lower than 1.2.1,
    # then everything works as usual.
    # Newer analyzer will multiply every version by 100 in case it's lower than 100
    #
    # report viewer will just transform it into casual version + alphabet symbol
    # current versions time is approximate, based on dota 2 wiki data
    # newer patches will be more accurate.
    switch ($patch) {
      case 6: # 6.76
        if ($date < 1351123200) return 600;
        else if ($date < 1351468800) return 601;
        else return 602;
        break;
      case 7: # 6.77
        if ($date < 1363824000) return 700;
        else return 702;
        break;
      case 8: # 6.78
        if ($date < 1372809600) return 800;
        else return 802;
      case 9: # 6.79
        if ($date < 1386806400) return 900;
        else return 902;
        break;
      case 11: # 6.81
        if ($date < 1401667200) return 1100;
        else return 1101;
        break;
      case 12: # 6.82
        if ($date < 1411862400) return 1200;
        else if ($date < 1413331200) return 1201;
        else return 1202;
        break;
      case 13: # 6.83
        if ($date < 1421107200) return 1300;
        else if ($date < 1423699200) return 1301;
        else return 1302;
        break;
      case 14: # 6.84
        if ($date < 1431129600) return 1400;
        else if ($date < 1431907200) return 1401;
        else return 1402;
        break;
      case 15: # 6.85
        if ($date < 1446336000) return 1500;
        else return 1501;
        break;
      case 16: # 6.86
        if ($date < 1450569600) return 1600;
        else if ($date < 1451347200) return 1601;
        else if ($date < 1453248000) return 1602;
        else if ($date < 1454630400) return 1603;
        else if ($date < 1456012800) return 1604;
        else return 1605;
        break;
      case 17: # 6.87
        if ($date < 1461888000) return 1700;
        else if ($date < 1462579200) return 1701;
        else if ($date < 1463875200) return 1702;
        else return 1703;
        break;
      case 18: # 6.88
        if ($date < 1468281600) return 1800;
        else if ($date < 1471564800) return 1801;
        else if ($date < 1472774400) return 1802;
        else if ($date < 1475366400) return 1803;
        else if ($date < 1476662400) return 1804;
        else return 1805;
        break;
      case 25: # 7.06
        if ($date < 1495324800) return 2500;
        else if ($date < 1496016000) return 2501;
        else if ($date < 1497139200) return 2502;
        else if ($date < 1498953600) return 2503;
        else if ($date < 1471651200) return 2504;
        else return 2505;
        break;
      case 26: # 7.07
        if ($date < 1509840000) return 2600;
        else if ($date < 1510876800) return 2601;
        else if ($date < 1513641600) return 2602;
        else return 2603;
        break;
      case 32: # 7.13
        if ($date < 1523672343) return 3200;
        else return 3201;
      case 38: # 7.19
        if ($date < 1535587200) return 3800;
        else if ($date < 1537056000) return 3801;
        else if ($date < 1539317940) return 3802;
        else return 3803;
      case 39: # 7.20
        if ($date < 1542767347) return 3900;
        else if ($date < 1543090140) return 3901;
        else if ($date < 1543635060) return 3902;
        else if ($date < 1544424420) return 3903;
        else return 3904;
      default:
        return $patch*100;
    }
  }

 ?>
