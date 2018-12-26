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
  *  $meta  - metadata object, lrg_metadata or array
  * Returns:
  *  Converted Patch ID (int) to support minor lettered patches
  */

  # open metadata

  function get_patchid($date, $patch, $meta) {
    # So the way it works:
    # report displayer will check for analyzer version and if it's lower than 1.2.1,
    # then everything works as usual.
    # Newer analyzer will multiply every version by 100 in case it's lower than 100
    #
    # report viewer will just transform it into casual version + alphabet symbol
    # current versions time is approximate, based on dota 2 wiki data
    # newer patches will be more accurate.

    if(isset($meta['patchdates'][$patch])) {
      sort($meta['patchdates'][$patch]['dates']);
      for ($i = 0, $sz = sizeof($meta['patchdates'][$patch]['dates']); $i < $sz; $i++) {
        if ($date < $meta['patchdates'][$patch]['dates'][$i]) break;
      }
      return $patch*100 + $i;
    }
    return $patch*100;
  }

 ?>
