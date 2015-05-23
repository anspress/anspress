<?php

namespace Imgur;

class Packager {

  function onInject($container) {
    $container
      ->factory('imgurRequest'       ,  'Imgur\Request')
      ->singleton('imgurCredentials' ,  'Imgur\Credentials')
      ->singleton('imgurAdapter'     ,  'Imgur\Adapter')
      ->singleton('imgurImageRepo'   ,  'Imgur\ImageRepo')
      ->singleton('imgurAlbumRepo'   ,  'Imgur\AlbumRepo');
  }

}
