<?php

namespace App;

class Mw
{
  public static function id()
  {
    ($token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['AUTHORIZATION'] ?? $_COOKIE['access_token'])
      && ($_REQUEST['user'] ??= SESSIONS[str_replace('Bearer ', '', $token)])
      ?: exit(http_response_code(401));
  }
  public static function auth($params)
  {
    $params['id'] != $_REQUEST['user']?->id && exit(http_response_code(403));
  }
}
