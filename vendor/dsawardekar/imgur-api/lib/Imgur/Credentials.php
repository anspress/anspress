<?php

namespace Imgur;

class Credentials {

  protected $clientId     = '';
  protected $clientSecret = '';
  protected $accessToken  = '';
  protected $refreshToken;

  /* seconds */
  public $accessTokenExpiry;
  public $accessTokenDuration = 3600;
  public $expiryBuffer = 60;

  public $now = null;

  function getClientId() {
    return $this->clientId;
  }

  function setClientId($clientId) {
    $this->clientId = $clientId;
  }

  function getClientSecret() {
    return $this->clientSecret;
  }

  function setClientSecret($clientSecret) {
    $this->clientSecret = $clientSecret;
  }

  function getAccessToken() {
    return $this->accessToken;
  }

  function setAccessToken($accessToken) {
    $this->accessToken = $accessToken;
  }

  function setAccessTokenExpiry($expireIn) {
    $expiry   = strtotime("+{$expireIn} seconds");
    $this->accessTokenExpiry = $expiry;
  }

  function getAccessTokenExpiry() {
    return $this->accessTokenExpiry;
  }

  function getAccessTokenDuration() {
    return $this->accessTokenDuration;
  }

  function setAccessTokenDuration($accessTokenDuration) {
    $this->accessTokenDuration = $accessTokenDuration;
  }

  function hasAccessTokenExpired() {
    $now    = $this->currentTime();
    $expiry = $this->getAccessTokenExpiry();

    if ($expiry >= $now) {
      $diff = $expiry - $now;
      return $diff < $this->expiryBuffer;
    } else {
      return true;
    }
  }

  function getRefreshToken() {
    return $this->refreshToken;
  }

  function setRefreshToken($refreshToken) {
    $this->refreshToken = $refreshToken;
  }

  function currentTime() {
    if (is_null($this->now)) {
      return strtotime('now');
    } else {
      return $this->now;
    }
  }

  /* abstract */
  function load() {

  }

  function save() {

  }

  function loaded() {
    return false;
  }

}
