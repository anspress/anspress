<?php

namespace Imgur;

class Repo {

  public $container;
  public $parentModel = null;
  public $parentId    = null;
  public $model       = null;

  function needs() {
    return array('imgurAdapter');
  }

  function setModel($model) {
    $this->model = $model;
  }

  function getModel() {
    return $this->model;
  }

  function getParentModel() {
    return $this->parentModel;
  }

  function getParentId() {
    return $this->parentId;
  }

  function setParent($model, $id = null) {
    $this->parentModel = $model;
    $this->parentId = $id;
  }

  function lookup($key) {
    return $this->container->lookup($key);
  }

  function find($id) {
    $request = $this->lookup('imgurRequest');
    $request->setMethod('GET');
    $request->setRoute($this->routeFor($this->getModel(), $id));

    return $this->imgurAdapter->invoke($request);
  }

  function create($params) {
    $request = $this->lookup('imgurRequest');
    $request->setMethod('POST');
    $request->setParams($params);
    $request->setRoute($this->routeFor($this->getModel()));

    return $this->imgurAdapter->invoke($request);
  }

  function update($id, $params) {
    $request = $this->lookup('imgurRequest');
    $request->setMethod('PUT');
    $request->setParams($params);
    $request->setRoute($this->routeFor($this->getModel(), $id));

    return $this->imgurAdapter->invoke($request);
  }

  function delete($id) {
    $request = $this->lookup('imgurRequest');
    $request->setMethod('DELETE');
    $request->setRoute($this->routeFor($this->getModel(), $id));

    return $this->imgurAdapter->invoke($request);
  }

  /* helpers */
  function getRoutePrefix() {
    $prefix      = '';
    $parentModel = $this->getParentModel();
    $parentId    = $this->getParentId();

    if (!is_null($parentModel)) {
      $prefix .= $parentModel;
    }

    if (!is_null($parentId)) {
      $prefix .= '/' . $parentId;
    }

    return $prefix;
  }

  function routeFor() {
    $parts = func_get_args();
    $prefix = $this->getRoutePrefix();
    $route = implode('/', $parts);

    if ($prefix === '') {
      return $route;
    } else {
      return $prefix . '/' . $route;
    }
  }

}
