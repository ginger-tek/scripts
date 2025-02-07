param([string]$TargetDirectory = (Get-Location))

if ((choco list | ? { $_ -match 'php|caddy' }).Count -ne 2) {
  choco install caddy
  choco install php
}

if (!(Test-Path $TargetDirectory)) {
  New-Item -ItemType Directory $TargetDirectory
}

$cwd = $TargetDirectory
$caddyFilePath = "$cwd/Caddyfile"
$phpFarmPath = "$cwd/php_farm.ps1"
$runCaddyPath = "$cwd/run_caddy.bat"

if (!(Test-Path $phpFarmPath)) {
  @"
param([int]`$StartPort = 9001,[int]`$PoolSize = 8)
`$env:PHP_FCGI_MAX_REQUESTS = 0
'Starting PHP-CGI farm'
`$pids = @()
`$StartPort..(`$StartPort + `$PoolSize - 1) | % { `$pids += Start-Process php-cgi.exe -ArgumentList "-b localhost:`$_" -NoNewWindow -PassThru; "Listening on localhost: `$_" }
pause
'Stopping farm...'
Stop-Process -Id `$pids.Id -Force
"@ | out-file $phpFarmPath
}

if (!(Test-Path $caddyFilePath)) {
  @"
(php_farm) {
  php_fastcgi {
    to 127.0.0.1:9001
    to 127.0.0.1:9002
    to 127.0.0.1:9003
    to 127.0.0.1:9004
    to 127.0.0.1:9005
    to 127.0.0.1:9006
    to 127.0.0.1:9007
    to 127.0.0.1:9008
    capture_stderr
  }
}

(spa) {
  try_files {path} /index.php /index.html
}

http://localhost:8080 {
  root "$cwd"
  import php_farm
  file_server
}
"@ | out-file $caddyFilePath
}

if (!(Test-Path $runCaddyPath)) {
  @"
caddy fmt "$cwd/Caddyfile" --overwrite
caddy run --config="$cwd/Caddyfile"
"@ | out-file $runCaddyPath
}

if (!(Test-Path "$cwd/index.php")) {
  @"
<?= phpinfo() ?>
"@ | out-file "$cwd/index.php"
}
