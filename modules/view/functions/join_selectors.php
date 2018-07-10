<?php

function join_selectors($modules, $level, $parent="") {
  global $lrg_use_get;
  global $lrg_get_depth;
  global $level_codes;
  global $mod;
  global $strings;
  global $leaguetag;
  global $max_tabs;
  global $linkvars;

  $out = "";
  $first = true;
  $unset_selector = false;

  if(empty($parent)) {
    $selectors = explode("-", $mod);
    if(!isset($modules[$selectors[0]])) $unset_selector = true;
  } else {
    $selectors = explode("-", str_replace($parent."-", "", $mod));
    if(!isset($modules[$selectors[0]])) $unset_selector = true;
  }
  # reusing assets Kappa
  $selectors = array();
  $selectors_num = sizeof($modules);

  foreach($modules as $modname => $module) {
    if ($selectors_num < $max_tabs) {
      if($lrg_use_get && $lrg_get_depth > $level) {
        if (stripos($mod, (empty($parent) ? "" : $parent."-" ).$modname) === 0)
          $selectors[] = "<span class=\"selector active\">".locale_string($modname)."</span>";
        else
          $selectors[] = "<span class=\"selector".($unset_selector ? " active" : "").
                            "\"><a href=\"?league=".$leaguetag."&mod=".(empty($parent) ? "" : $parent."-" ).$modname.
                            (empty($linkvars) ? "" : "&".$linkvars).
                            "\">".locale_string($modname)."</a></span>";
      } else {
        $selectors[] = "<span class=\"mod-".$level_codes[$level][1]."-selector selector".
                            ($first ? " active" : "")."\" onclick=\"switchTab(event, 'module-".(empty($parent) ? "" : $parent."-" ).$modname."', 'mod-".$level_codes[$level][1]."');\">".locale_string($modname)."</span>";
      }
    } else {
      if($lrg_use_get && $lrg_get_depth > $level) {
        if (stripos($mod, (empty($parent) ? "" : $parent."-" ).$modname) === 0)
          $selectors[] = "<option selected=\"selected\" value=\"?league=".$leaguetag."&mod=".(empty($parent) ? "" : $parent."-" ).$modname."&".
          (empty($linkvars) ? "" : "&".$linkvars)
          ."\">".locale_string($modname)."</option>";
        else
          $selectors[] = "<option".($unset_selector ? "selected=\"selected\"" : "")." value=\"?league=".$leaguetag."&mod=".(empty($parent) ? "" : $parent."-" ).$modname.(empty($linkvars) ? "" : "&".$linkvars)
          ."\">".locale_string($modname)."</option>";
      } else {
        $selectors[] = "<option value=\"module-".(empty($parent) ? "" : $parent."-" ).$modname."\">".locale_string($modname)."</option>";
      }
    }
    if(($lrg_use_get && stripos($mod, (empty($parent) ? "" : $parent."-" ).$modname) === 0) || !$lrg_use_get || $lrg_get_depth < $level+1 || $unset_selector) {
      if(is_array($module)) {
        $module = join_selectors($module, $level+1, (empty($parent) ? "" : $parent."-" ).$modname);
      }
      $out .= "<div id=\"module-".(empty($parent) ? "" : $parent."-" ).$modname."\" class=\"selector-module mod-".$level_codes[$level][1].($first ? " active" : "")."\">".$module."</div>";
      $first = false;
      $unset_selector = false;
    }
  }
  if ($selectors_num < $max_tabs)
    return "<div class=\"selector-modules".(empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".implode($selectors, " | ")."</div>".$out;
  else
  if($lrg_use_get && $lrg_get_depth > $level)
    return "<div class=\"selector-modules".(empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".
        "<select onchange=\"select_modules_link(this);\" class=\"select-selectors select-selectors".(empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".
        implode($selectors, "")."</select></div>".$out;
    else
    return "<div class=\"selector-modules".(empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".
        "<select onchange=\"switchTab(event, this.value, 'mod-".$level_codes[$level][1]."');\" class=\"select-selectors select-selectors".
        (empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".
        implode($selectors, "")."</select></div>".$out;
}

?>
