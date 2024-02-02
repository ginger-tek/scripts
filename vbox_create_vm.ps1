param(
  [Parameter(Mandatory=$true)][string]$Name,
  [Parameter(Mandatory=$true)][string]$OSType,
  [Parameter(Mandatory=$true)][string]$ISOPath,
  [int]$CPUs = 1,
  [int]$RAM = 1024,
  [int]$VRAM = 128,
  [int]$DiskSizeGB = 20,
  [switch]$3DVideoAcceleration,
  [switch]$Unattended,
  [string]$Username,
  [string]$Pass,
  [string]$Locale = 'en_US',
  [string]$Timezone = 'EST',
  [string]$StartMode,
  [string]$BasePath
)

if ($env:Path -notmatch 'VirtualBox') {
  Write-Warning 'VirtualBox CLI not found'
  break
}

if (!(Test-Path $ISOPath)) {
  Write-Warning 'ISO not found'
  break
}

$DiskSizeGB *= 1024

# Create VM
if ($BasePath) {
  VBoxManage createvm --name $Name --ostype $OSType --register --basefolder $BasePath
}
else {
  VBoxManage createvm --name $Name --ostype $OSType --register
  $BasePath = '{0}\VirtualBox VMs\{1}' -f $env:USERPROFILE, $Name
}
VBoxManage modifyvm $Name --ioapic on --memory $RAM --vram $VRAM --cpus $CPUs --accelerate-2d-video on --graphicscontroller vmsvga --vrde on --vrdemulticon on --vrdeport 10001
if ($3DVideoAcceleration.IsPresent) {
  VBoxManage modifyvm $Name --accelerate-3d on
}
$disk = '{0}\{1}_disk.vdi' -f $BasePath, $Name
VBoxManage createhd --filename $disk --size $DiskSizeGB --format VDI
VBoxManage storagectl $Name --name 'SATA Controller' --add SATA --controller IntelAhci
VBoxManage storageattach $Name --storagectl 'SATA Controller' --port 0 --device 0 --type hdd --medium $disk
VBoxManage storageattach $Name --storagectl 'SATA Controller' --port 1 --device 0 --type dvddrive --medium $ISOPath
VBoxManage modifyvm $Name --boot1 dvd --boot2 disk --boot3 none --boot4 none
VBoxManage showvminfo $Name
if ($Unattended.IsPresent) {
  if (!$Username -and !$Pass) {
    Write-Warning 'Missing username and password for account creation'
    break
  }
  VBoxManage unattended install $Name --iso $ISOPath --user $Username.ToLower() --password $Pass --full-user-name $Username --locale $Locale --time-zone $Timezone
  if ($StartMode) {
    VBoxManage startvm $Name --type $StartMode
  }
  else {
    VBoxManage startvm $Name --type gui
  }
}
