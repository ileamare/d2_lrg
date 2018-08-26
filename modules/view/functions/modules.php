<?php

function check_module($module) {
  global $lrg_get_depth;
  global $lrg_use_get;
  global $mod;

  if(unset_module()) {
    $mod = $module;
  }

  return $lrg_use_get &&
          (
            (stripos($mod, $module) === 0) && (
              (strlen($mod) == strlen($module)) ||
              (strlen($mod) > strlen($module) && $mod[strlen($module)] === '-' )
            )
          ) ||
          !$lrg_use_get ||
          !$lrg_get_depth ||
          $lrg_get_depth <= substr_count($module, "-");
}

function unset_module() {
  global $unset_module;

  if($unset_module) {
    $unset_module = false;
    return true;
  }
  return false;
}

?>
