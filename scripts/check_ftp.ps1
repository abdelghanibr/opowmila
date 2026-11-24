# check_ftp.ps1
# Teste la connexion FTP (dev et/ou prod) à partir des variables FTP_* du fichier .env.
# Les mots de passe ne sont jamais affichés.
#
# Usage (PowerShell, dans le dossier du projet) :
#   powershell -ExecutionPolicy Bypass -File .\scripts\check_ftp.ps1
#   powershell -ExecutionPolicy Bypass -File .\scripts\check_ftp.ps1 -Target dev
#   powershell -ExecutionPolicy Bypass -File .\scripts\check_ftp.ps1 -Target prod

param(
    [string]$EnvFile = (Join-Path $PSScriptRoot "..\.env"),
    [ValidateSet("dev", "prod", "both")]
    [string]$Target = "both"
)

function Read-EnvFile {
    param([string]$Path)

    $vars = @{}
    foreach ($raw in Get-Content -Path $Path -Encoding UTF8) {
        $line = $raw.Trim()
        if ($line -eq "" -or $line.StartsWith("#")) { continue }

        $idx = $line.IndexOf("=")
        if ($idx -lt 1) { continue }

        $key = $line.Substring(0, $idx).Trim()
        $val = $line.Substring($idx + 1).Trim()

        if (($val.StartsWith('"') -and $val.EndsWith('"')) -or
            ($val.StartsWith("'") -and $val.EndsWith("'"))) {
            $val = $val.Substring(1, $val.Length - 2)
        }
        $vars[$key] = $val
    }
    return $vars
}

function Test-FtpConnection {
    param(
        [string]$Label,
        [string]$FtpHost,
        [string]$Port,
        [string]$User,
        [string]$Password,
        [string]$Tls
    )

    if (-not $FtpHost) {
        Write-Host "[ERREUR] $Label : FTP_${Label}_HOST absent du .env" -ForegroundColor Red
        return
    }

    $uri = "ftp://${FtpHost}:${Port}/"
    Write-Host "Test $Label -> $uri (utilisateur: $User, TLS: $Tls)" -ForegroundColor Cyan

    try {
        $req = [System.Net.FtpWebRequest]::Create($uri)
        $req.Method      = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $req.Credentials = New-Object System.Net.NetworkCredential($User, $Password)
        $req.EnableSsl   = ($Tls -eq "true")
        $req.UsePassive  = $true
        $req.Timeout     = 15000

        $resp   = $req.GetResponse()
        $reader = New-Object System.IO.StreamReader($resp.GetResponseStream())
        $items  = @($reader.ReadToEnd() -split "`r?`n" | Where-Object { $_ -ne "" })
        $reader.Close()
        $resp.Close()

        Write-Host "[OK] $Label : connexion réussie, $($items.Count) élément(s) à la racine." -ForegroundColor Green
        $items | Select-Object -First 10 | ForEach-Object { Write-Host "     $_" }
    }
    catch {
        Write-Host "[ERREUR] $Label : $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.InnerException) {
            Write-Host "         Détail : $($_.Exception.InnerException.Message)" -ForegroundColor DarkRed
        }
    }
    Write-Host ""
}

# ---- Programme principal ----
if (-not (Test-Path $EnvFile)) {
    Write-Host "[ERREUR] Fichier .env introuvable : $EnvFile" -ForegroundColor Red
    exit 1
}

$cfg = Read-EnvFile -Path (Resolve-Path $EnvFile)

if ($Target -in @("dev", "both")) {
    Test-FtpConnection -Label "DEV" `
        -FtpHost $cfg["FTP_DEV_HOST"] -Port $cfg["FTP_DEV_PORT"] `
        -User $cfg["FTP_DEV_USERNAME"] -Password $cfg["FTP_DEV_PASSWORD"] `
        -Tls $cfg["FTP_DEV_TLS"]
}

if ($Target -in @("prod", "both")) {
    Test-FtpConnection -Label "PROD" `
        -FtpHost $cfg["FTP_PROD_HOST"] -Port $cfg["FTP_PROD_PORT"] `
        -User $cfg["FTP_PROD_USERNAME"] -Password $cfg["FTP_PROD_PASSWORD"] `
        -Tls $cfg["FTP_PROD_TLS"]
}
