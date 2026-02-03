param([switch]$isHomeServer)
#Requires -RunAsAdministrator
Set-ExecutionPolicy Unrestricted
winget install Google.Chrome
choco install pwsh -y
choco install winfetch -y
choco install hwinfo.install -y
choco install git.install -y
choco install vscode.install -y
choco install vscode.powershell -y
choco install 7zip.install -y
choco install composer -y
choco install php --version 8.3 -y
choco install caddy -y
choco install klogg -y
choco install notepadplusplus.install -y
choco install python -y
choco install vlc.install -y
choco install crystaldiskinfo.install -y
choco install sqlitebrowser.install -y
choco install nvm.install -y
choco install ffmpeg -y
nvm install lts
nvm use lts

if ($isHomeServer.IsPresent) {
  choco install jellyfin -y
  choco install plexmediaserver -y
  choco install mp3tag -y
  choco install mariadb.install HeidiSQL -y
  choco install deluge -y
  choco install yt-dlp -y
  choco install powerchute-personal -y
  choco install virtualbox -y
  choco install virtualbox-guest-additions-guest.install -y
}
