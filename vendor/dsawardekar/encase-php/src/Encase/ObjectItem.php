<?php

namespace Encase;

use Encase\ContainerItem;

class ObjectItem extends ContainerItem {

  public $injected = false;
  public $initialized = false;

  function inject($object, $origin = null) {
    if ($this->injected) {
      return false;
    } else {
      parent::inject($object, $origin);
      $this->injected = true;
      return true;
    }
  }

  function initialize($object, $origin = null) {
    if ($this->initialized === false) {
      parent::initialize($object, $origin);
      $this->initialized = true;
    }
  }
}

?>
