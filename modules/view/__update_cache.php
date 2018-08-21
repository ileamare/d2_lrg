<?php 

$dir = flat_rscandir($reports_dir);

# checkdir recursively
foreach($dir as $fname) {
  if(!preg_match($report_mask, $fname)) continue;
  $full_fname = $reports_dir."/".$fname;
  if(isset($cache['files'][$fname])) {
    try {
      if($cache['files'][$fname][1] != filesize($full_fname) ||
          $cache['files'][$fname][2] != filemtime($full_fname) ||
          !isset($cache['reps'][ $cache['files'][$fname][0] ])
        ) {
        $cache_update = true;
        $tmp = file_get_contents($full_fname);
        $tmp = json_decode($tmp, true);
        $tmp = get_report_descriptor($tmp);
        $tmp['file'] = $fname;
        $cache['files'][$fname] = [
          $tmp['tag'],
          filesize($full_fname),
          filemtime($full_fname)
        ];
        $cache['reps'][ $cache['files'][$fname][0] ] = $tmp;
        unset($tmp);
      }
    } catch (Exception $e) {
      $cache_update = true;
      $cache['files'][$fname] = [
        NULL,
        filesize($full_fname),
        filemtime($full_fname)
      ];
    }
  } else {
    $cache_update = true;
    try {
      $tmp = file_get_contents($full_fname);
      $tmp = json_decode($tmp, true);
      $tmp = get_report_descriptor($tmp);
      $tmp['file'] = $fname;
      $cache['files'][$fname] = [
        $tmp['tag'],
        filesize($full_fname),
        filemtime($full_fname)
      ];
      if(!isset($cache['reps'][ $cache['files'][$fname][0] ]))
        $cache['reps'][ $cache['files'][$fname][0] ] = $tmp;
      else {
        $cache['files'][$fname][0] = str_replace("/", "_", $cache['files'][$fname][0]);
        $cache['files'][$fname][0] = str_replace(".json", "", $cache['files'][$fname][0]);
        $cache['reps'][ $cache['files'][$fname][0] ] = $tmp;
      }
      unset($tmp);
    } catch (Exception $e) {
      $cache['files'][$fname] = [
        NULL,
        filesize($full_fname),
        filemtime($full_fname)
      ];
    };
  }
}

if(sizeof($cache["files"]) > sizeof($dir)) {
  foreach($cache["files"] as $k => $v) {
    if(!in_array($k, $dir)) {
      if($v[0] !== NULL) {
        foreach($cache["files"] as $kk => $vv) {
          if($kk == $k) continue;
          if($vv[0] == $v[0]) {
            $v[0] = NULL;
          }
        }
      }
      if($v[0] !== NULL)
        unset($cache["reps"][$v[0]]);
      unset($cache["files"][$k]);
    }
  }
}

if(sizeof($cache["files"]) < sizeof($cache["reps"])) {
  foreach($cache["reps"] as $k => $v) {
    if(!in_array($v['file'], $dir)) {
      unset($cache["reps"][$k]);
    }
  }
}

# end

if ($cache_update) {
  file_put_contents($cache_file, json_encode($cache));
}

?>
