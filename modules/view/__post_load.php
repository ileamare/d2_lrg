<?php

if (isset($get_positions_strings) || stripos($mod, "positions") !== FALSE) {
  for ($i=1; $i>=0; $i--) {
    for ($j=1; $j<6 && $j>0; $j++) {
      if (!$i) { $j = 0; }
      if(!isset($strings['en']["positions_$i.$j"]))
        $strings['en']["positions_$i.$j"] = ($i ? locale_string("core") : locale_string("support"))." ".locale_string("lane_$j");

      if (!$i) { break; }
    }
  }
}

# legacy name for Radiant Winrate
if (compare_ver($report['ana_version'], array(1,1,1,-4,0)) < 0) {
    $strings[$locale]['rad_wr'] = $strings[$locale]['radiant_wr'];
}

if(isset($report['versions'])) {
    foreach($report['versions'] as $k => $v) {
        $mode = (int)($k/100);
        if(!isset($meta['versions'][$mode])) {
            for($i = $mode; $i > 0; $i--) {
                if(isset($meta['versions'][$i])) {
                    break;
                }
            }
            $diff = $mode - $i;
            $parent_patch = explode(".", $meta['versions'][$i]);
            $parent_patch[1] = (int)$parent_patch[1] + $diff;
            if ($parent_patch[1] < 10)
                $parent_patch[1] = "0".$parent_patch[1];
            $meta['versions'][$mode] = implode(".", $parent_patch);

            unset($diff);
            unset($parent_patch);
        }
    }
}

?>
