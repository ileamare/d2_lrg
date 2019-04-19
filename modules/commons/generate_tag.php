<?php
/** 
 * League Report Generator - Generate Team Tag - Fetcher Module
 * ported from D2-LRG-lrg2 v 2.*
 * @author Darien "leamare" Fawkes
 * @license GNU GPL 3.0
 * 
 * @param string $name team's name
 * @return string team tag, based on team's name
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
