param(
  [string]$TargetDir = '.',
  [switch]$DoIt
)
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
$TargetDir = $TargetDir.TrimEnd('\')
gci "$TargetDir\*.mp3" | % {
  $folder = $shell.NameSpace($_.DirectoryName)
  $file = $folder.ParseName($_.Name)
  $meta = [ordered]@{'FileName' = $_.Name }
  foreach ($key in $props.Keys) {
    $meta[$key] = $folder.GetDetailsOf($file, $props[$key])
  }
  try {
    $dest = "$TargetDir\$(($meta['Album'] -replace ':', '' -replace '\/|–','-').Trim())"
    if (!(Test-Path $dest)) {
      if ($DoIt.IsPresent) {
        Write-Host "Creating directory '$dest'"
        New-Item -ItemType Directory -Path $dest | Out-Null
      }
      else {
        Write-Host "Dry run: would create directory '$dest'"
      }
    }
    if ($DoIt.IsPresent) {
      Write-Host "Moving '$($_.FullName)' to '$dest'"
      Move-Item $_.FullName $dest
    }
    else {
      Write-Host "Dry run: would move '$($_.FullName)' to '$dest'"
    }
  }
  catch {
    Write-Host "Error moving '$($_.FullName)': $_"
  }
}