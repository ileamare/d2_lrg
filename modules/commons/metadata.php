<?php

class lrg_metadata implements ArrayAccess {
  private $metadata = [];

  private function load($metafile) {
    $content = file_get_contents("metadata/$metafile.json") or die("[F] Can't open metadata\n");
    $this->metadata[ $metafile ] = json_decode($content, true);
  }

  public function & get($metafile) {
    if (!isset($this->metadata[ $metafile ]))
      $this->load($metafile);

    return $this->metadata[ $metafile ];
  }

  public function & __get($name) {
    return $this->get($name);
  }


  public function offsetSet($offset, $value) {
      return $this->get($offset);
  }

  public function offsetExists($offset) {
      return isset($this->metadata[$offset]);
  }

  public function offsetUnset($offset) {
      unset($this->metadata[$offset]);
  }

  public function & offsetGet($offset) {
      return $this->get($offset);
  }
}

?>
