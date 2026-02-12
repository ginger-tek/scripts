<?php

require './Lap.php';
require './Mw.php';
require './Res.php';

use GingerTek\Lap;
use App\Mw;
use App\Res;

define('SESSIONS', [
  '9s76fg9876s9876df98g7d9fsd7f' => (object) [
    'id' => '23',
    'username' => 'gingertek'
  ]
]);

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
      template:`{{posts}}`,
      setup() {
        const posts = Vue.ref([])
        Vue.onMounted(() => {
          fetch('/api/posts',
            {
            headers: {
              Authorization: '9s76fg9876s9876df98g7d9fsd7f'
            }
          })
            .then(r=>r.json())
            .then(d=>posts.value=d)
        })
        return {posts}
      }
    }).mount('#app')
  </script>
</body>
</html>
HTML);

Lap::group('/api', Mw::id(...), function () {
  Lap::get('/', fn() => Res::json(['version' => '1.0']));
  Lap::get('/posts', fn() => Res::json([
    ['id'=>1,'content'=>'my post!']
  ]));
  Lap::get('/posts/(?<id>\d+)', fn($p) => Res::json(['test' => $p['id']]));
  Lap::get('/users/(?<id>\d+)', Mw::auth(...), fn($p) => $p['id']);
  Lap::post('/posts', fn() => 'asdf');
  Lap::fallback(fn() => Res::json(['error' => 'Not found']));
});

Lap::fallback(fn() => '<h1>Page not found</h1>');
