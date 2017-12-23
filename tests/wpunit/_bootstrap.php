<?php
// Here you can initialize variables that will be available to your tests
use Codeception\Configuration;
use Codeception\Util\Autoload;
$support = Configuration::supportDir();

Autoload::addNamespace( '\AnsPress\Tests', $support );

