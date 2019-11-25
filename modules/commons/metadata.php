<?php

class lrg_metadata implements ArrayAccess {
  private $metadata = [];
  private $dir;

  public function __construct($dir = "metadata") {
    $this->dir = $dir;
  }

  private function load($metafile) {
    if (!file_exists("{$this->dir}/$metafile.json"))
      throw new \Exception("wrong metadata endpoint"."-- {$this->dir}/$metafile.json");
    $content = file_get_contents("{$this->dir}/$metafile.json");
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
