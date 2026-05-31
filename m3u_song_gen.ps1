param(
  [string]$TargetDir = '.',
  [string]$PlaylistName,
  [switch]$DoIt
)
if (!$PlaylistName) {
  $PlaylistName = (Split-Path $TargetDir -Leaf)
}
$shell = New-Object -ComObject Shell.Application
$folder = $shell.NameSpace($TargetDir)
$props = [ordered]@{
  'Title'    = 21
  'Artist'   = 13
  'Album'    = 14
  'Year'     = 15
  'Genre'    = 16
  'Duration' = 27
  'Bitrate'  = 223
}
$m3u8 = @('#EXTM3U')
gci "$TargetDir\*.mp3" -Recurse | % {
  $folder = $shell.NameSpace($_.DirectoryName)
  $file = $folder.ParseName($_.Name)
  $meta = [ordered]@{'FileName' = $_.Name }
  foreach ($key in $props.Keys) {
    $meta[$key] = $folder.GetDetailsOf($file, $props[$key])
  }
  $m3u8 += "#EXTINF:$($meta['Duration']),$($meta['Artist']) - $($meta['Title'])"
  $m3u8 += ".\$($_.FullName -replace [regex]::Escape($TargetDir), '')"
}

$m3u8
    
if ($DoIt.IsPresent) {
  Write-Host "Creating playlist '$TargetDir\$PlaylistName.m3u'"
  $m3u8 | Out-File "$TargetDir\$PlaylistName.m3u" -Encoding UTF8 -Force
}