<?php

$_SERVER = [
	'REQUEST_METHOD' => 'GET',
	'REQUEST_URI' => '/api/234'
];
$_REQUEST = [];

class Lap {
  public static function uri() {
  	return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  }
  public static function route(string $pattern, callable ...$callbacks): void {
    $req = strtolower($_SERVER['REQUEST_METHOD']) . self::uri();
    ($pattern === $req ?: ((bool)preg_match("#^$pattern#", $req, $params) ?: false)) && exit(join(array_map(fn($cb) => $cb($params ?? []), $callbacks)));
  }
  public static function fallback(callable $cb): void {
  	(http_response_code(404) && exit($cb()));
  }
  public static function __callStatic(string $name, array $args): void {
    (isset($_REQUEST["fn_$name"]) && exit($_REQUEST["fn_$name"](...$args))) ?: (in_array($name,['get','post','put','patch','delete','any']) && self::route($name=='any'?".*{$args[0]}":"$name{$args[0]}$", ...array_slice($args,1)));
  }
  public static function set(string $key, $val = null):void {
    if (is_callable($val)) $key = "fn_$key";
    $_REQUEST[$key] = $val;
  }
}

// Rendering with a layout
Lap::set('view', function (string $view, ?array $params = []): string {
  return $view;
  $params['view'] ??= "views/$view.php";
  ob_start();
  extract($params);
  require 'views/_layout.php';
  return ob_get_clean();
});

Lap::set('json', function ($data) {
  header('content-type: application/json');
  return json_encode($data);
});

Lap::get('/api/(?<id>\d+)', fn($p) => Lap::json(['test' => $p['id']]));

// Lap::get('/', fn() => Lap::view('home'));

// Example with middleware callable
Lap::get('/things/(?<id>\d+)', fn($p) => $p['id'] != 23 && exit(http_response_code(403)), fn($p) => $p['id']);

// Fallback route
Lap::fallback(fn() => '<h1>Page not found</h1>');
