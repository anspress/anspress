## Encase [![Build Status][1]][2]

### Lightweight IOC Container for PHP.

Encase is a library for using dependency injection within PHP
applications. It provides a lightweight IOC Container that manages
dependencies between your classes. It was written to assist with wiring
of domain objects outside Frameworks to build faster test suites.

A Ruby implementation is [here][3].

# Features

* Stores objects, factories, and singletons
* Declarative syntax for specifying dependencies
* Simple API for configuring the container
* Support for nested containers
* Support for lazy initialization
* Initializers

## Usage

Consider a `Worker` class that has a dependency on a `Logger`. We would
like this dependency to be available to the worker when it is created.

First we create a container object and declare the dependencies.

```php
<?php
use Encase\Container;

$container = new Container();
$container->object('logger', new Logger())
          ->factory('worker', 'Worker');
```

Then we declare that the Worker has a dependency on `logger` using the
`needs` function. It returns an array of keys that correspond to keys we
registered on the container.

```php
<?php
class Worker {

  function needs() {
    return array('logger');
  }

}
```

That's it! Now we can create a new `Worker` by looking up the `worker`
key on the Container.

```php
<?php
$myWorker = $container->lookup('worker');
$myWorker instanceof Worker; // true
```

The `Worker` instance gets a reference to the `logger` automatically.

```php
<?php
$myWorker->logger instanceof Logger; // true
```

## Container Configuration

Containers are configured using a functions `object`, `factory` or
`singleton`. Container items are stored in an associative array inside the container
and are used to lookup the object later.

```php
<?php
$container->object(...);
$container->factory(...);
$container->singleton(...);
```

The types of items your can store in the Container are listed below.

### Object

Objects are pre existing values that have already been created in your
system. They can be `strings`, `numbers` or any value that does not
require instantiation.

They are stored in the Container and returned as is without any modification. To
store objects call the `object` function. And pass a string key to store
under, along with the object itself.

```php
<?php
$container->object('key', existingValue);
```

### Factory

A Factory container item is a `Classpath` string. On every lookup a new
instance of this `Class` will be created with it's dependencies
auto-injected.

To store such classes use the `factory` function. And pass the full
classpath to the `Class` to be instantiated.

```php
<?php
$container->factory('key', 'FactoryClass');
```

### Singleton

A Singleton is similar to a Factory. However it caches the instance
created on the first lookup and returns that instance on subsequent lookups.

Use the `singleton` function to store the path to the singleton `Class`.

```php
<?php
$container->singleton('key', 'SingletonClass');
```

## Declaring Dependencies

To specify dependencies of a class, you use the `needs` function. It
must return an array of strings corresponding to the keys stored in the container.

```php
<?php
class Worker {

  function needs() {
    return array('one', 'two', 'three');
  }

}
```

You can optionally use the `INeeds` interface to declare the needs.

```php
<?php
class Worker implements INeeds {

  function needs() {
    return array('one', 'two', 'three');
  }

}
```

## Lazy Initialization

Encase allows storage of dependencies lazily. This can be useful if the
dependencies aren't ready at the time of container configuration. But
will be ready before lookup.

Lazy initialization is done by passing a `closure` or `callable` to the
container instead of a value. Here `key`'s `callable` will be evaluated before the
first lookup.

```php
<?php
// with $callables
$container->object('key', $callable);

// with an anonymous function
$container->object('key', function($container) {
  return 'value';
});
```

The `closure` or `callable` takes an argument equal to the container object
itself. You can use this to conditionally resolve the value based on
other objects in the container or elsewhere in the system.

## Initializers

Initializers are useful when working with objects from external
libraries that don't use the Encase Container. Such objects don't
declare their `needs`, but still have to be initialized before they can
be used.

The `initializer` method takes the key of object to initialize and a
`$callable` that will initialize the object. The callable will receive 2
arguments, the value of the object being looked up from the Container and the container
itself.

For `object` and `singleton` item types initialization happens on first lookup only.
While `factory` item types will have their initializers run every time a
new instance is looked up from the Container.

The code below stores a `Currency` object in the container. An
initializer is added for this object that ensures that a formatter is
set on this object every time it is instantiated.

```php
<?php
$container->factory('currency', 'Currency');
$container->factory('formatter', 'NumberFormatter');
$container->initializer('currency', array($this, 'initCurrency'));

function initCurrency($currency, $container) {
  $currency->setFormatter($container->lookup('formatter'));
}
```

## Packagers

Packagers are convenience helpers for grouping the dependencies of a
feature as a unit. For instance if an `Options` features is comprised of the
classes, `OptionsStore`, `OptionsPage` and `OptionsValidator` you would
need to declare this dependencies on the container each time you need
to use said `Options` feature.

Using a Packager object allows you to add these dependencies to the
container. A packager should setup the dependencies in it's `onInject`
function as shown below,

```php
<?php
class OptionsPackager {

  function onInject($container) {
    $this->container
      ->factory('optionsStore', 'OptionsStore')
      ->factory('optionsValidator', 'OptionsValidator')
      ->factory('optionsPage', 'OptionsPage');
  }

}
```

Now when you need the feature you only need to add the
packager to the container.

```php
<?php
$container->packager('optionsPackager', 'OptionsPackager')
```

The `packager` itself will be registered with the container as a
singleton, and can be looked up by it's key.

## Nested Containers

Containers can also be nested within other containers. This allows grouping
dependencies within different contexts of your application. When looking
up keys, parent containers are queried when a key is not found in a
child container.

```php
<?php
$parent = new Container();
$parent->object('logger', new Logger())
  ->factory('worker', 'Worker');

$child = $parent->child();
$child->factory('worker', 'CustomWorker');

$child->lookup('logger'); // from parent container
$child->lookup('worker'); // from child container
```

Here the `child` will use `CustomWorker` for resolving
`worker`. While the `logger` will be looked up from the `parent`
container.

## Public Properties

For each declared `need`, Encase will create corresponding public properties on the
object injected. A `container` property is also injected into the class for
looking up other dependencies at runtime.

```php
<?php
class Worker {

  function needs() {
    return array('one', 'two', 'three');
  }

}

worker = container->lookup('worker');
worker->one;
worker->two;
worker->three;
worker->container; // reference to the container object
```

## Lifecycle

An `onInject` event hook is provided to container items after their
dependencies are injected. Any post injection initialization can be
carried out here.

```php
<?php
class Worker implements {

  function needs() {
    return array('logger');
  }

  function onInject($container) {
    $this->logger->log('Worker is ready');
  }

}
```

## Testing

Encase simplifies testability of objects. Since objects are stored in
the container you can reach deep inside an object's dependency graph and
swap out an expensive dependency with a dummy one.

In the earlier example in order to test that the logger is indeed called by the worker
we can register the worker as a `mock` object. Then verify that this
mock was called appropriately.

```php
<?php
function test_it_logs_message_to_the_logger() {
  $mock = $this->getMock('Logger', 'log');
  $mock->expects($this->once())
    ->method('log')
    ->with($this->equalTo('something'));

  $container = new Container();
  $container->factory('worker', 'Worker');
  $container->object('logger', $mock);

  $worker = $container->lookup('worker');
  $worker->start();
}
```

## Installation

Add this line to your `composer.json`.

```json
{
  "require": {
    "dsawardekar/encase-php": "~0.1.0"
  }
}
```

And then execute:

    $ composer install

# System Requirements

Encase has been tested to work on these platforms.

* PHP 5.3
* PHP 5.3.3
* PHP 5.4
* PHP 5.5
* PHP 5.6

## Contributing

See contributing guidelines for [Portkey][4].

## License

MIT License. Copyright © 2014 Darshan Sawardekar

[1]: https://travis-ci.org/dsawardekar/encase-php.png
[2]: https://travis-ci.org/dsawardekar/encase-php
[3]: https://github.com/dsawardekar/encase
[4]: https://github.com/dsawardekar/portkey
