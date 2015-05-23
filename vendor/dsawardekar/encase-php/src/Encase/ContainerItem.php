<?php

namespace Encase;

class ContainerItem {

  public $container;
  public $key;
  public $value;
  public $reifiedValue = null;
  public $initializer = null;

  function __construct($container) {
    $this->container = $container;
  }

  function store($key, $value) {
    $this->key = $key;
    $this->value = $value;
  }

  function inject($object, $origin = null) {
    $container = is_null($origin) ? $this->container : $origin;
    return $container->inject($object);
  }

  function reified() {
    return !is_null($this->reifiedValue);
  }

  function reify() {
    if ($this->reified()) {
      return false;
    }

    if (is_callable($this->value)) {
      $this->reifiedValue = call_user_func($this->value, $this->container);
    } else {
      $this->reifiedValue = $this->value;
    }

    return true;
  }

  function fetch() {
    return $this->reifiedValue;
  }

  /* Public API */
  function instance($origin = null) {
    if (!$this->reified()) {
      $this->reify();
    }

    $object = $this->fetch();
    $this->inject($object, $origin);

    if ($this->hasInitializer()) {
      $this->initialize($object, $origin);
    }

    return $object;
  }

  function hasInitializer() {
    return is_null($this->initializer) === false;
  }

  function initialize($object, $origin = null) {
    if (is_null($origin)) {
      $origin = $this->container;
    }

    call_user_func($this->initializer, $object, $origin);
  }
}

?>
