<?php
// Behold! The world's most-compact yet standard-featured PHP router!
// Supports dynamic parameter routes and middleware/multiple callbacks
function route(string $pattern, callable ...$callbacks): void {
    $req = $_SERVER['REQUEST_METHOD'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    ($pattern === $req ?: (preg_match("#^$pattern$#", $req, $params) ?: false)) && exit(join(array_map(fn($cb) => $cb($params ?? []), $callbacks)));
}

// Rendering with a layout
function view(string $view, ?array $params = []): string {
    $params['view'] ??= "views/$view.php";
    ob_start();
    extract($params);
    require 'views/_layout.php';
    return ob_get_clean();
}

route('GET/', fn() => view('home'));

// Example with middleware callable
route('GET/things/(?<id>\d+)', fn($p) => $p['id'] != 23 && exit(http_response_code(403)), fn($p) => $p['id']);

// Fallback route
route('.*', fn() => '<h1>Page not found</h1>');
