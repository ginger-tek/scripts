param(
  [Parameter(Mandatory = $true)][string]$Path,
  [switch]$Update,
  [string]$Version = 'latest',
  [switch]$CleanTools,
  [string]$MinMem = '512M',
  [string]$MaxMem = '1G'
)

@"
////////////////////////////
//                        //
//  MCBuildTools          //
//  - v1.4                //
//                        //
//  Written By Jeremy M.  //
//                        //
//  A PowerShell script   //
//  to easily deploy a    //
//  new Spigot/Bukkit     //
//  Minecraft server :)   // 
//                        //
////////////////////////////

"@

if ($path -notmatch '^[A-Z]:') {
  Write-Warning 'Not an absolute path'
  break
}

if (!(Test-Path $path)) {
  Write-Warning 'That directory does not exist'
  if ((Read-Host 'Would you like to create it? [y/n]') -eq 'y') {
    New-Item $Path -ItemType Directory -Force | out-null
  }
  else {
    break
  }
}

if ($CleanTools) {
  Remove-Item $Path\build\* -Recurse -Force
}

$btJar = 'https://hub.spigotmc.org/jenkins/job/BuildTools/lastSuccessfulBuild/artifact/target/BuildTools.jar'
$btExists = Test-Path $Path\build

if (!$btExists) {
  New-Item $Path\build -ItemType Directory -Force | out-null
}

if ($Update -or !$btExists) {
  'Cleaning build files...'
  Remove-Item $Path\build\* -Force -Recurse
  'Downloading the latest BuildTools.jar from Spigot...'
  Invoke-WebRequest -Uri $btJar -OutFile "$Path\build\BuildTools.jar"
}

'Running BuildTools.jar...'

if ($Version -eq 'latest') {
  'Getting latest server version...'
}
else {
  if ($Version -notmatch '\d+\.\d+\.\d+') {
    Write-Warning 'Invalid version format. Must be in format #.#.#'
    break
  }
  else {
    "Getting server version $Version..."
  }
}

$install = "java -Xms512M -jar BuildTools.jar --rev $Version"
$wd = Get-Location
Set-Location $Path\build
Invoke-Expression -Command $install -ErrorAction SilentlyContinue -Verbose
Set-Location $wd

if (Test-Path "$Path\build\spigot-*.jar") {
  $spigot = (Get-ChildItem "$Path\build\spigot-*.jar")[0]
  Copy-Item $spigot $Path
  $spigot = "$Path\$($spigot.Name)"
  $verStr = ($spigot -split '-')[1]

  $start = @"
@echo off
title Minecraft Server v$verStr
cd $Path
start javaw -Xms$MinMem -Xmx$MaxMem -jar $spigot -nogui
"@
    
  $start | Out-File $Path\start_server.bat -Encoding default -Force

  if ($Update) {
    'Regenerating new eula.txt and setting to true...'
    Remove-Item $Path\eula.txt -Force
    & $Path\start_server.bat
    $eula = Get-Content $Path\eula.txt -Raw
    $eula = $eula -replace 'eula=false', 'eula=true'
    $eula | Out-File $Path\eula.txt -Encoding default -Force
  }
    
  'Ready to run!'
}
