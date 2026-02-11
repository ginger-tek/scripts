<?php

/**
 * Lap Router
 * @author GingerTek
 */
class Lap
{
  private static array $stack = [];
  public static string $uri;

  /**
   * Define a route on which to match the incoming request
   * @param string $method
   * @param string $pattern
   * @param callable[] $handlers
   * @return void
   */
  public static function route(string $method, string $pattern, callable ...$handlers): void
  {
    self::$uri ??= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pattern = rtrim(join('/', self::$stack) . $pattern, '/') ?: '/';
    (($method === $_SERVER['REQUEST_METHOD'] || $method === '*')
      && ($pattern === self::$uri ?: ((bool) preg_match("#^$pattern$#", self::$uri, $params) ?: false))
      && exit(join(array_map(fn($cb) => $cb($params ?? []), $handlers))));
  }
  /**
   * Group routes with a common base pattern
   * @param string $pattern
   * @param callable[] $handlers
   * @return void
   */
  public static function group(string $pattern, callable ...$handlers): void
  {
    self::$stack[] = $pattern;
    (str_starts_with(self::$uri, $pattern) && join(array_map(fn($cb) => $cb(), $handlers)));
    array_pop(self::$stack);
  }
  /**
   * Define an HTTP GET route
   * @param string $pattern
   * @param callable[] $handlers
   * @return void
   */
  public static function get(string $pattern, callable ...$handlers): void
  {
    self::route('GET', $pattern, ...$handlers);
  }
  /**
   * Define an HTTP POST route
   * @param string $pattern
   * @param callable[] $handlers
   * @return void
   */
  public static function post(string $pattern, callable ...$handlers): void
  {
    self::route('POST', $pattern, ...$handlers);
  }
  /**
   * Define an HTTP PUT route
   * @param string $pattern
   * @param callable[] $handlers
   * @return void
   */
  public static function put(string $pattern, callable ...$handlers): void
  {
    self::route('GET', $pattern, ...$handlers);
  }
  /**
   * Define an HTTP PATCH route
   * @param string $pattern
   * @param callable[] $handlers
   * @return void
   */
  public static function patch(string $pattern, callable ...$handlers): void
  {
    self::route('PUT', $pattern, ...$handlers);
  }
  /**
   * Define an HTTP DELETE route
   * @param string $pattern
   * @param callable[] $handlers
   * @return void
   */
  public static function delete(string $pattern, callable ...$handlers): void
  {
    self::route('DELETE', $pattern, ...$handlers);
  }
  /**
   * Define a route for any HTTP method
   * @param string $pattern
   * @param callable[] $handlers
   * @return void
   */
  public static function any(string $pattern, callable ...$handlers): void
  {
    self::route('*', $pattern, ...$handlers);
  }
  /**
   * Define a fallback route; sets response code to 404
   * @param callable $handler
   * @return void
   */
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

Lap::get('/', fn() => <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <div id="app">Loading...</div>
  <script src="https://unpkg.com/vue"></script>
  <script>
    Vue.createApp({
      setup() {
        Vue.onMounted(() => {
          fetch('/api/posts',{headers:{Authorization:'9s76fg9876s9876df98g7d9fsd7f'}})
            .then(r=>r.json())
            .then(console.log)
        })
      }
    }).mount('#app')
  </script>
</body>
</html>
HTML);

Lap::group('/api', Mw::id(...), function () {
  Lap::get('/', fn() => Res::json(['version' => '1.0']));
  Lap::get('/posts', fn() => Res::json([]));
  Lap::get('/posts/(?<id>\d+)', fn($p) => Res::json(['test' => $p['id']]));
  Lap::get('/users/(?<id>\d+)', Mw::auth(...), fn($p) => $p['id']);
  Lap::post('/posts', fn() => 'asdf');
  Lap::fallback(fn() => Res::json(['error' => 'Not found']));
});

Lap::fallback(fn() => '<h1>Page not found</h1>');
