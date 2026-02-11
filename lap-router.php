<?php

class Lap
{
  public static function route(string $method, string $pattern, callable ...$handlers): void
  {
    $_REQUEST['uri'] ??= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pattern = rtrim(join('/', $_REQUEST['_s'] ?? []) . $pattern, '/') ?: '/';
    (($method === $_SERVER['REQUEST_METHOD'] || $method === '*')
      && ($pattern === $_REQUEST['uri'] ?: ((bool) preg_match("#^$pattern$#", $_REQUEST['uri'], $params) ?: false))
      && exit(join(array_map(fn($cb) => $cb($params ?? []), $handlers))));
  }
  public static function group(string $pattern, callable ...$handlers): void
  {
    $_REQUEST['_s'] ??= [];
    $_REQUEST['_s'][] = $pattern;
    (str_starts_with($_REQUEST['uri'], $pattern) && join(array_map(fn($cb) => $cb(), $handlers)));
    array_pop($_REQUEST['_s']);
  }
  public static function get(string $pattern, callable ...$handlers): void
  {
    self::route('GET', $pattern, ...$handlers);
  }
  public static function post(string $pattern, callable ...$handlers): void
  {
    self::route('POST', $pattern, ...$handlers);
  }
  public static function put(string $pattern, callable ...$handlers): void
  {
    self::route('GET', $pattern, ...$handlers);
  }
  public static function patch(string $pattern, callable ...$handlers): void
  {
    self::route('PUT', $pattern, ...$handlers);
  }
  public static function delete(string $pattern, callable ...$handlers): void
  {
    self::route('DELETE', $pattern, ...$handlers);
  }
  public static function any(string $pattern, callable ...$handlers): void
  {
    self::route('*', $pattern, ...$handlers);
  }
  public static function fallback(callable $handler): void
  {
    (http_response_code(404) && exit($handler()));
  }
}

// Usage ===========================

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

define('SESSIONS', [
  '9s76fg9876s9876df98g7d9fsd7f' => (object) [
    'id' => '23',
    'username' => 'gingertek'
  ]
]);

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

// Routes --------------------------

Lap::get('/', fn() => '<h1>Hello!</h1>' . print_r($_REQUEST, true));

Lap::group('/api', Mw::id(...), function () {
  Lap::get('/', fn() => Res::json(['version' => '1.0']));
  Lap::get('/posts/(?<id>\d+)', fn($p) => Res::json(['test' => $p['id']]));
  Lap::get('/users/(?<id>\d+)', Mw::auth(...), fn($p) => $p['id']);
  Lap::fallback(fn() => Res::json(['error' => 'Not found']));
});

Lap::fallback(fn() => '<h1>Page not found</h1>');
