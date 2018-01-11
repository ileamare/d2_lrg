<?php

function locale_string($string_id, $vars=array()) {
  global $strings;
  global $locale;

  if(isset($locale) && isset($strings[$locale][$string_id]))
    $string = $strings[$locale][$string_id];
  else if (isset($strings['en'][$string_id]))
    $string = $strings['en'][$string_id];
  else $string = $string_id;

  foreach($vars as $k => $v) {
    str_ireplace("%$k%", $v, $string);
  }

  return $string;
}



 ?>
