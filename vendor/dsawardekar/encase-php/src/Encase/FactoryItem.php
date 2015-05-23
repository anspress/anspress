<?php

namespace Encase;

use Encase\Container;
use Encase\ContainerItem;

class FactoryItem extends ContainerItem {

  function fetch() {
    return new $this->reifiedValue;
  }

}

?>
