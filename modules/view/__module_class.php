<?php

/*
 * League Report Generator - Model-View implementation - View Module
 *
 * Basic implementatio of MVC-like principles.
 *
 * Object: lrg_view_element
 * Contains:
 *   type = (text, table, graph, chart, image, container) - data type of element
 *   content - element's content (string, array of elements or another element)
 *   (optional) class - HTML class for the element (string)
 *   (optional) id - element name / HTML id (string)
 *   (optional) caption - element caption on page (string)
 *      (optional) public_flag - shows is element should be listed in module list
 * Methods:
 *   set_type()
 *   set_caption()
 *   set_class()
 *   set_ID()
 *   TODO set_content()
 *
 *   get_type()
 *   get_type_str()
 *   get_caption()
 *   get_class()
 *   get_ID()
 *   get_content_raw()
 *
 *   TODO process();
 */

define( 'LRG_EL_TYPE_NULL', null );
define( 'LRG_EL_TYPE_TEXT', 0 );
define( 'LRG_EL_TYPE_TABLE', 1 );
define( 'LRG_EL_TYPE_GRAPH', 2 );
define( 'LRG_EL_TYPE_CHART', 3 );
define( 'LRG_EL_TYPE_IMAGE', 4 );
define( 'LRG_EL_TYPE_CONTAINER', 5 );

class lrg_view_element {
  private $type = null;
  private $content = null;

  private $class = null;
  private $id = null;
  private $caption = null;

  public function set_type($type) {
    if ($type >= 0 && $type <= 5 || $type == null ) {
      $this->type = $type;
      return true;
    }
    return false;
  }
  public function set_caption($caption) {
    $this->caption = $caption;
  }
  public function set_class($class) {
    $this->class = $class;
  }
  public function set_ID($id) {
    $this->id = $id;
  }


  public function get_type() {
    return $this->type;
  }
  public function get_type_str() {
    switch ($this->type) {
      case LRG_EL_TYPE_NULL:
        return "null";
        break;
      case LRG_EL_TYPE_TEXT:
        return "text";
        break;
      case LRG_EL_TYPE_TABLE:
        return "table";
        break;
      case LRG_EL_TYPE_GRAPH:
        return "graph";
        break;
      case LRG_EL_TYPE_CHART:
        return "chart";
        break;
      case LRG_EL_TYPE_IMAGE:
        return "image";
        break;
      case LRG_EL_TYPE_CONTAINER:
        return "container";
        break;
    };
  }
  public function get_caption() {
    return $this->caption;
  }
  public function get_class() {
    return $this->class;
  }
  public function get_id() {
    return $this->id;
  }
  public function get_content_raw() {
    return $this->content;
  }
}

?>
