<?php

namespace Imgur;

class AlbumRepo extends Repo {

  public $model = 'album';

  function addImages($id, $images) {
    $params = array(
      'ids' => $images
    );

    $request = $this->lookup('imgurRequest');
    $request->setMethod('POST');
    $request->setParams($params);
    $request->setRoute($this->routeFor($this->getModel(), $id, 'add'));

    return $this->imgurAdapter->invoke($request);
  }

}
