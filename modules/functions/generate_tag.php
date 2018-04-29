<?php
/*
 * League Report Generator - Generate Team Tag - Fetcher Module
 *
 * Parameters:
 *   $name - string, team's name
 * Returns:
 *   String with team tag, based on team's name
 */


function generate_tag($name) {
  $name = ucwords($name);
  $tag = "";

  for ($i=0, $sz=strlen($name); $i < $sz; $i++) {
    if (ctype_upper($name[$i]))
      $tag .= $name[$i];
  }

  return $tag;
}

?>
