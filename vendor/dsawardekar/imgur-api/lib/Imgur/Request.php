<?php

namespace Imgur;

class Request {

  public $route;
  public $method = 'GET';
  public $headers = array();
  public $params  = array();

  function getRoute() {
    return $this->route;
  }

  function setRoute($route) {
    $this->route = $route;
  }

  function getMethod() {
    return $this->method;
  }

  function setMethod($method) {
    $this->method = $method;
  }

  function getHeaders() {
    return $this->headers;
  }

  function setHeaders(&$headers) {
    $this->headers = $headers;
  }

  function getParams() {
    return $this->params;
  }

  function setParams(&$params) {
    $this->params = $params;
  }

}
