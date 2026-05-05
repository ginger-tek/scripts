param(
    [Parameter(Mandatory=$true)][string]$username,
    [Parameter(Mandatory=$true)][string]$pass,
    [string]$outputDir = "$env:USERPROFILE\Downloads"
)

$ie = New-Object -com InternetExplorer.Application
$ie.visible=$true
$ie.silent = $true
$ie.navigate("https://www.ezpassnj.com/vector/account/home/accountLogin.do") 
sleep(1)
$ie.Document.IHTMLDocument3_getElementById("tt_username1").value= "$username" 
$ie.Document.IHTMLDocument3_getElementById("tt_loginPassword1").value = "$pass" 
$ie.Document.IHTMLDocument3_getElementById("jcaptcha_response1").value = Read-Host "Captcha" 
$form = $ie.Document.IHTMLDocument3_getElementsByTagName("form") | where-object {$_.ClassName -eq "form-signincus"}
$form.submit();
sleep(2)
$ie.navigate("https://www.ezpassnj.com/vector/account/transactions/batatransactionSearch.do")
sleep(2)

$sm = $smi = $em = $emi = 0
$year = (Get-Date).AddYears(-1).Year

for($i=0;$i -lt 12;$i++) {
    $sm++
    if(($sm.ToString()).length -eq 1) {
        $smi = "0$sm"
    } elseif(($em.ToString()).length -eq 2) {
        $smi = "$sm"
    }
    $sdate = "$smi/01/$year"
    $sdate

    $startdateinput = $ie.Document.IHTMLDocument3_getElementById("startDateAll")
    $startdateinput.value = $sdate

    ############################

    $em = $sm + 1
    if(($em.ToString()).length -eq 1) {
        $emi = "0$em"
    } elseif(($em.ToString()).length -eq 2) {
        if($em -eq 13) {
            $em = "01"
            $year = (Get-Date).Year
        }
        $emi = "$em"
    }
    $edate = "$emi/01/$year"
    $edate

    $enddateinput = $ie.Document.IHTMLDocument3_getElementById("endDateAll")
    $enddateinput.value = $edate

    ############################

    $view = $ie.Document.IHTMLDocument3_getElementsByTagName("button") | where-object {$_.name -eq 'btnView' -and $_.type -eq 'submit'}
    $view.click();
    sleep(2)
    $ie.navigate("https://www.ezpassnj.com/vector/account/transactions/batatransactionSearch.do?printType=xls&exclGen=true")
    sleep(2)

    ############################

    $obj = new-object -com WScript.Shell
    $obj.AppActivate('Internet Explorer')
    $obj.SendKeys('s')
    sleep(2)
}

$ie.navigate("https://www.ezpassnj.com/vector/account/home/accountLogout.do")
sleep(2)
$ie.Quit()

$year = (Get-Date).AddYears(-1).Year

$CSVFolder = "$outputDir\Transaction*.csv"
$OutputFile = "$outputDir\EZ-Pass_$year.csv"

$CSV= @()

Get-ChildItem -Path $CSVFolder -Filter *.csv | ForEach-Object { 
    $CSV += @(Import-Csv -Path $_)
}

$CSV | Export-Csv -Path $OutputFile -NoTypeInformation -Force

#Remove-Item -Path "$outputDir\Transaction*.csv"
