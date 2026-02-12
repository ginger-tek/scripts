<?php

namespace App;

class Res
{
  public static function view(string $view, ?array $params = []): string
  {
    $params['view'] ??= "views/$view.php";
    return $params['view'];
    // ob_start();
    // extract($params);
    // require 'views/_layout.php';
    // return ob_get_clean();
  }
  public static function json($data)
  {
    header('content-type: application/json');
    return json_encode($data);
  }
}