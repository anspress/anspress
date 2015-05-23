<?php

namespace Encase;

use Encase\INeeds;

class Container {

  protected $items;

  public $parent = null;
  public $itemFactory = null;

  function __construct($parent = null) {
    $this->parent = $parent;
    $this->items = array();
  }

  function itemFactory() {
    if (is_null($this->itemFactory)) {
      $this->itemFactory = new ContainerItemFactory();
    }

    return $this->itemFactory;
  }

  function itemFor($type) {
    return $this->itemFactory()->build($type, $this);
  }

  function contains($key) {
    return array_key_exists($key, $this->items);
  }

  function register($type, $key, $value) {
    $item = $this->itemFor($type);
    $item->store($key, $value);

    $this->items[$key] = $item;
    return $this;
  }

  function unregister($key) {
    unset($this->items[$key]);
  }

  function clear() {
    $this->items = array();
  }

  function object($key, $value) {
    return $this->register('object', $key, $value);
  }

  function factory($key, $value) {
    return $this->register('factory', $key, $value);
  }

  function singleton($key, $value) {
    return $this->register('singleton', $key, $value);
  }

  function initializer($key, $callable) {
    $item = $this->items[$key];
    $item->initializer = $callable;

    return $this;
  }

  function packager($key, $value) {
    $this->singleton($key, $value);
    $this->lookup($key);

    return $this;
  }

  function instanceFor($key, $origin = null) {
    $item = $this->items[$key];
    return $item->instance($origin);
  }

  function lookup($key, $origin = null) {
    if ($this->contains($key)) {
      return $this->instanceFor($key, $origin);
    } else if (!is_null($this->parent)) {
      if (is_null($origin)) {
        $origin = $this;
      }
      return $this->parent->lookup($key, $origin);
    } else {
      throw new \RuntimeException("Container does not have key: $key");
    }
  }

  function child() {
    return new Container($this);
  }

  function inject($object) {
    $needs = $this->needsFor($object);
    if (!is_null($needs)) {
      foreach ($needs as $need) {
        $object->$need = $this->lookup($need);
      }

      $object->container = $this;
      $this->notify($object);

      return true;
    } else {
      $this->notify($object);
      return false;
    }
  }

  function needsFor($object) {
    if (is_object($object) && method_exists($object, 'needs')) {
      return $object->needs();
    } else {
      return null;
    }
  }

  function notify($object) {
    if (is_object($object) && method_exists($object, 'onInject')) {
      return $object->onInject($this);
    }
  }
}

?>
