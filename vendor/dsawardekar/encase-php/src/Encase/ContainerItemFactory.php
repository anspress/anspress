<?php

namespace Encase;

use Encase\ContainerItem;
use Encase\ObjectItem;
use Encase\FactoryItem;
use Encase\SingletonItem;
use Encase\Container;

class ContainerItemFactory {

  function build($type, $container) {
    $containerItem = $this->containerItemFor($type);
    return new $containerItem($container);
  }

  function containerItemFor($type) {
    switch ($type) {
      case 'object':
        return 'Encase\ObjectItem';
      case 'factory':
        return 'Encase\FactoryItem';
      case 'singleton':
        return 'Encase\SingletonItem';
      default:
        return 'Encase\ContainerItem';
    }
  }

}

?>
