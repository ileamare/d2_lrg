<?php

function utf8ize($d) {
  if (is_array($d))
      foreach ($d as $k => $v)
          $d[$k] = utf8ize($v);
   else if(is_object($d))
      foreach ($d as $k => $v)
          $d->$k = utf8ize($v);
   else if(is_string($d))
      return mb_convert_encoding($d, "utf-8");

  return $d;
}

?>
