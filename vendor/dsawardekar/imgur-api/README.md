# Imgur-API

PHP Library for using the Imgur API.

## Usage

```php
<?php

$container
  ->singleton('imgurCredentials', 'Imgur\Credentials')
  ->singleton('imgurAdapter', 'Imgur\Adapter')
  ->singleton('imgurImageRepo', 'Imgur\ImageRepo');

$imageRepo = $container->lookup('imgurImageRepo');
$image = $imageRepo->create(
  array(
    'image' => 'http://path/to/image',
    'title' => 'My Image'
  )
);

echo $image['link'];

```

## System Requirements

* PHP 5.3.3+

## License

MIT License. Copyright © 2014 Darshan Sawardekar
