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
  $words_count = substr_count($name, " ");
  if(strlen($name) && $words_count === 0) {
    return $name;
  } else if (strlen($name) && $words_count > 0  && $words_count < 2) {
    $name = ucwords($name);
    $tag = "";

    for ($i=0, $sz=strlen($name); $i < $sz; $i++) {
      if (ctype_upper($name[$i])) {
        $tag .= $name[$i];
        if(isset($name[$i+1]) && $name[$i+1] != " " && !ctype_upper($name[$i+1]))
          $tag .= $name[$i+1];
      }
    }
  } else if (strlen($name)) {
    $name = ucwords($name);
    $tag = "";

    for ($i=0, $sz=strlen($name); $i < $sz; $i++) {
      if (ctype_upper($name[$i]))
        $tag .= $name[$i];
    }
  } else {
    $tag = "( )";
  }

  return $tag;
}

?>
