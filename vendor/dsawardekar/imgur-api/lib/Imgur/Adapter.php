<?php

namespace Imgur;

use Requests;
use Requests_Session;

class Adapter {

  public $container;
  public $imgurCredentials;

  public $apiEndpoint = 'https://api.imgur.com';
  public $apiVersion  = '3';
  public $session     = null;

  function needs() {
    return array('imgurCredentials');
  }

  /* if accessToken is present, we are assuming that
   * OAuth access was previously granted */
  function isAuthorized() {
    return $this->imgurCredentials->getAccessToken() !== '';
  }

  function authorizeUrl($responseType = 'pin') {
    $endpoint = $this->apiEndpoint . '/oauth2/authorize';
    $args = array(
      'client_id'     => $this->imgurCredentials->getClientId(),
      'response_type' => $responseType
    );

    $url = $endpoint . '?' .  http_build_query($args);

    return $url;
  }

  function verifyPin($pin) {
    return $this->exchangeTokens('pin', $pin);
  }

  function refreshAccessToken() {
    $refreshToken = $this->imgurCredentials->getRefreshToken();
    $this->exchangeTokens('refresh_token', $refreshToken);

    return $this->imgurCredentials->getAccessToken();
  }

  function exchangeTokens($type, $value) {
    $url = $this->apiEndpoint . '/oauth2/token';
    $params = array(
      'client_id'     => $this->imgurCredentials->getClientId(),
      'client_secret' => $this->imgurCredentials->getClientSecret(),
      'grant_type'    => $type
    );

    $params[$type] = $value;
    $headers = array(
      'Authorization' => 'Client-ID ' . $this->imgurCredentials->getClientId()
    );

    $session  = $this->getSession();
    $response = $session->post($url, $headers, $params);
    $json     = $this->parseBody($response->body);

    if ($response->success) {
      $this->updateCredentials($json);
      return true;
    } else {
      throw new Exception($json['data']['error']);
    }
  }

  function invoke($request) {
    $accessToken = $this->imgurCredentials->getAccessToken();
    if ($accessToken !== '' && $this->imgurCredentials->hasAccessTokenExpired()) {
      $accessToken = $this->refreshAccessToken();
    }

    $url  = $this->apiEndpoint . '/' . $this->apiVersion;
    $url .= '/' . $request->getRoute();

    $session     = $this->getSession();
    $method      = $this->toSessionMethod($request->getMethod());
    $headers     = $request->getHeaders();

    if ($accessToken !== '') {
      $headers['Authorization'] = 'Bearer ' . $accessToken;
    } else {
      $headers['Authorization'] = 'Client-ID ' . $this->imgurCredentials->getClientId();
    }

    $response = $session->$method(
      $url, $headers, $request->getParams()
    );

    $json = $this->parseBody($response->body);

    if ($response->success) {
      return $json['data'];
    } else {
      throw new Exception($json['data']['error']);
    }
  }

  function getSession() {
    if (is_null($this->session)) {
      $this->session = new Requests_Session();
      $this->session->options['timeout'] = $this->getTimeout();
    }

    return $this->session;
  }

  function getTimeout() {
    return 60;
  }

  function toSessionMethod($method) {
    return strtolower($method);
  }

  /* helpers */
  function updateCredentials(&$json) {
    if (array_key_exists('access_token', $json) &&
        array_key_exists('expires_in', $json) &&
        array_key_exists('refresh_token', $json)) {
      $cred = $this->imgurCredentials;
      $cred->setAccessToken($json['access_token']);
      $cred->setAccessTokenExpiry($json['expires_in']);
      $cred->setRefreshToken($json['refresh_token']);
      $cred->save();
    } else {
      throw new Exception('Server did not return auth tokens.');
    }
  }

  function parseBody($body) {
    $json = json_decode($body, true);
    if (is_array($json)) {
      return $json;
    } else {
      $result = preg_match_all('/(Error.*)</m', $body, $matches);

      if ($result === 1) {
        throw new Exception('Imgur API Failed with ' . $matches[1][0]);
      } else {
        throw new Exception('Invalid JSON returned from Imgur server.');
      }
    }
  }

}
