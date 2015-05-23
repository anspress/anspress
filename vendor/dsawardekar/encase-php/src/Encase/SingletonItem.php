<?php

namespace Encase;

use Encase\Container;
use Encase\ContainerItem;

class SingletonItem extends ContainerItem {

  public $singleton = null;
  public $initialized = false;
  public $injected = false;

  function fetch() {
    if (is_null($this->singleton)) {
      $this->singleton = new $this->reifiedValue;
    }

    return $this->singleton;
  }

  function inject($object, $origin = null) {
    if ($this->injected === false) {
      parent::inject($object, $origin);
      $this->injected = true;
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
