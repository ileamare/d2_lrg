<?php

function migrate_params(&$host, $vals) {
  foreach ($vals as $k => $v) {
    if (is_array($v)) {
      if(!isset($host[$k])) $host[$k] = $v;
      else migrate_params($host[$k], $v);
    } else $host[$k] = $v;
  }
}

?>
