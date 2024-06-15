if ((choco list | ? { $_ -match 'php|caddy' }).Count -ne 2) {
  choco install caddy
  choco install php
}

$cwd = Get-Location
if (!(Test-Path $cwd\CaddyFile)) {
  @"
(php) {
  php_fastcgi 127.0.0.1:9000 {
    try_files {path} {path}/index.php
    capture_stderr
  }
}

(php_spa) {
  php_fastcgi 127.0.0.1:9000 {
    try_files {path} /index.php
    capture_stderr
  }
}

localhost {
  import php
  file_server
}
"@ | out-file $cwd\CaddyFile
}

if (!(Test-Path $cwd\start_svr.bat)) {
  @"
cd $(Get-Location)
start /B php-cgi -b 9000
caddy start
"@ | out-file $cwd\start_svr.bat
}

if (!(Test-Path $cwd\stop_svr.bat)) {
  @"
cd $(Get-Location)
taskkill /f /im php-cgi.exe
caddy stop
"@ | out-file $cwd\stop_svr.bat
}
