<?php
// Behold! The world's most-compact yet standard-featured PHP router!
// Supports dynamic parameter routes and middleware/multiple callbacks
class Lap {
  public static function route(string $pattern, callable ...$callbacks): void {
    $req = strtolower($_SERVER['REQUEST_METHOD']) . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    ($pattern === $req ?: (preg_match("#^$pattern$#", $req, $params) ?: false)) && exit(join(array_map(fn($cb) => $cb($params ?? []), $callbacks)));
  }
  public static function __callStatic($name, $args): void {
    (in_array($name,['get','post','put','patch','delete','any']) && self::route($name.($name=='any'?'.*':$args[0]), ...array_slice($args,1)));
  }
}

// Rendering with a layout
function view(string $view, ?array $params = []): string {
    $params['view'] ??= "views/$view.php";
    ob_start();
    extract($params);
    require 'views/_layout.php';
    return ob_get_clean();
}

Lap::get('/', fn() => 'home');

// Example with middleware callable
Lap::get('/things/(?<id>\d+)', fn($p) => $p['id'] != 23 && exit(http_response_code(403)), fn($p) => $p['id']);

// Fallback route
Lap::any(fn() => '<h1>Page not found</h1>');
