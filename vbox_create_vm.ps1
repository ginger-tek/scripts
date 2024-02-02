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
  [string]$Password,
  [string]$Locale = 'en_US',
  [string]$Timezone = 'EST',
  [string]$StartMode,
  [string]$BasePath
)

if (!(VBoxManage)) {
  Write-Warning 'VirtualBox CLI not found'
  break
}

$diskSizeGB *= 1024

# Create VM
if ($basePath) {
  VBoxManage createvm --name $name --ostype $osType --register --basefolder $basePath
}
else {
  VBoxManage createvm --name $name --ostype $osType --register
  $basePath = '{0}\VirtualBox VMs\{1}' -f $env:USERPROFILE, $name
}
VBoxManage modifyvm $Name --ioapic on --memory $ram --vram $vram --cpus $cpus --accelerate-2d-video on --graphicscontroller vmsvga --vrde on --vrdemulticon on --vrdeport 10001
if ($3DVideoAcceleration.IsPresent) {
  VBoxManage modifyvm $Name --accelerate-3d on
}
$disk = '{0}\{1}_disk.vdi' -f $basePath, $name
VBoxManage createhd --filename $disk --size $diskSizeGB --format VDI
VBoxManage storagectl $name --name 'SATA Controller' --add SATA --controller IntelAhci
VBoxManage storageattach $name --storagectl 'SATA Controller' --port 0 --device 0 --type hdd --medium $disk
VBoxManage storageattach $name --storagectl 'SATA Controller' --port 1 --device 0 --type dvddrive --medium $isoPath
VBoxManage modifyvm $name --boot1 dvd --boot2 disk --boot3 none --boot4 none
VBoxManage showvminfo $name
if ($Unattended.IsPresent) {
  if (!$Username -and !$Password) {
    Write-Warning 'Missing username and password for account creation'
    break
  }
  VBoxManage unattended install $name --iso $isoPath --user $username.ToLower() --password $password --full-user-name $username --locale $locale --time-zone $timezone
  if ($StartMode) {
    VBoxManage startvm $name --type $startMode
  }
  else {
    VBoxManage startvm $name --type gui
  }
}
