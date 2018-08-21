<?php

function flat_rscandir($dirname) {
  $dir = scandir($dirname);
  $folders = [];

  foreach ($dir as $k => &$v) {
    if($v[0] == ".") {
      unset($dir[$k]);
      continue;
    }
    if(is_dir($dirname."/".$v)) {
      $folders[$v] = flat_rscandir($dirname."/".$v);
      unset($dir[$k]);
    }
  }

  foreach($folders as $k => $v) {
    foreach($v as &$file)
      $file = $k."/".$file;

    $dir = array_merge($dir, $v);
  }

  sort($dir);

  return $dir;
}

function rscandir($dirname) {
  $dir = scandir($dirname);
  foreach ($dir as $k => &$v) {
    if($v[0] == ".") {
      unset($dir[$k]);
      continue;
    }
    if(is_dir($dirname."/".$v)) {
      $v = [
        "name" => $v,
        "files" => rscandir($dirname."/".$v)
      ];
    }
  }
  sort($dir);

  return $dir;
}

?>
