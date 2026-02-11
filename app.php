<?php

require './Lap.php';

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
