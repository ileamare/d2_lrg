<?php
function generate_tag($name) {
  $name = ucwords($name);
  $tag = "";
  for ($i=0, $sz=strlen($name); $i < $sz; $i++) {
    if (ctype_upper($name[$i])) $tag .= $name[$i];
  }

  return $tag;
}

?>
