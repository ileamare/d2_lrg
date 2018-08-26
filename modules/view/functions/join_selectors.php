<?php

function join_selectors($modules, $level, $parent="") {
  if (!is_array($modules)) return $modules;

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
    $mn = (empty($parent) ? "" : $parent."-" ).$modname;
    $startline_check_res = stripos($mod, $mn) === 0 && (
          (strlen($mod) == strlen($mn)) ||
          (strlen($mod) > strlen($mn) && $mod[strlen($mn)] == '-')
        );

    if ($selectors_num < $max_tabs) {
      if($lrg_use_get && $lrg_get_depth > $level) {
        if ( $startline_check_res )
          $selectors[] = "<span class=\"selector active\">".locale_string($modname)."</span>";
        else
          $selectors[] = "<span class=\"selector".($unset_selector ? " active" : "").
                            "\"><a href=\"?league=".$leaguetag."&mod=".$mn.
                            (empty($linkvars) ? "" : "&".$linkvars).
                            "\">".locale_string($modname)."</a></span>";
      } else {
        $selectors[] = "<span class=\"mod-".$level_codes[$level][1]."-selector selector".
                            ($first ? " active" : "")."\" onclick=\"switchTab(event, 'module-".$mn."', 'mod-".$level_codes[$level][1]."');\">".locale_string($modname)."</span>";
      }
    } else {
      if($lrg_use_get && $lrg_get_depth > $level) {

        if (stripos($mod, $mn) === 0 && (
              (strlen($mod) == strlen($mn)) ||
              (strlen($mod) > strlen($mn) && $mod[strlen($mn)] == '-')
            ) )
          $selectors[] = "<option selected=\"selected\" value=\"?league=".$leaguetag."&mod=".$mn."&".
          (empty($linkvars) ? "" : "&".$linkvars)
          ."\">".locale_string($modname)."</option>";
        else
          $selectors[] = "<option".($unset_selector ? "selected=\"selected\"" : "")." value=\"?league=".$leaguetag."&mod=".$mn.(empty($linkvars) ? "" : "&".$linkvars)
          ."\">".locale_string($modname)."</option>";
      } else {
        $selectors[] = "<option value=\"module-".$mn."\">".locale_string($modname)."</option>";
      }
    }
    if($startline_check_res || !$lrg_use_get || $lrg_get_depth < $level+1 || $unset_selector) {
      if(is_array($module)) {
        $module = join_selectors($module, $level+1, $mn);
      }
      $out .= "<div id=\"module-".$mn."\" class=\"selector-module mod-".$level_codes[$level][1].($first ? " active" : "")."\">".$module."</div>";
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
