<?php

namespace GingerTek;

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
  /**
   * Get request header by case-insensitive key
   * @param string $key
   * @return ?string
   */
  public static function header(string $key): ?string
  {
    return ($key=strtoupper($key)) && ($_SERVER["HTTP_$key"] ?? $_SERVER[$key] ?? null);
  }
  /**
   * Get request body content
   * @return mixed
   */
  public static function body(): mixed
  {
    return file_get_contents('php://input');
  }
  /**
   * Get object array of files by field name
   * @param string $field
   * @return array
   */
  public static function files(string $field): array
  {
    $arr = [];
    foreach($_FILES[$field] as $k => $v)
      foreach($v as $i => $f)
        $arr[$i][$k] = $_FILES[$k][$i];
    return $arr;
  }
}