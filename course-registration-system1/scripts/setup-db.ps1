param(
  [string]$DbHost = $env:CRS_DB_HOST,
  [string]$User = $env:CRS_DB_USER,
  [string]$Password = $env:CRS_DB_PASS,
  [string]$Database = $env:CRS_DB_NAME,
  [switch]$Modern
)

$ErrorActionPreference = "Stop"

if ([string]::IsNullOrWhiteSpace($DbHost)) { $DbHost = "localhost" }
if ([string]::IsNullOrWhiteSpace($User)) { $User = "root" }
if ($null -eq $Password) { $Password = "" }
if ([string]::IsNullOrWhiteSpace($Database)) { $Database = "course_registration" }

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
$sqlFile = Join-Path $repoRoot "database.sql"

if (!(Test-Path $sqlFile)) {
  throw "Missing database.sql at: $sqlFile"
}

$mysql = "C:\xampp\mysql\bin\mysql.exe"
if (!(Test-Path $mysql)) {
  $mysql = "mysql"
}

$args = @("-h", $DbHost, "-u", $User, "--default-character-set=utf8mb4")
if ($Password -ne "") {
  $args += "-p$Password"
}

Write-Host "Creating database (if not exists): $Database"
@"
CREATE DATABASE IF NOT EXISTS `$Database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
"@ | & $mysql @args
if ($LASTEXITCODE -ne 0) { throw "mysql failed while creating database." }

Write-Host "Importing schema/data from: $sqlFile"
Get-Content -Raw $sqlFile | & $mysql @args
if ($LASTEXITCODE -ne 0) { throw "mysql failed while importing database.sql." }

if ($Modern) {
  $mig = Join-Path $repoRoot "migrations\\001_modern_school.sql"
  if (!(Test-Path $mig)) { throw "Missing migration: $mig" }
  Write-Host "Applying modern school migration: $mig"
  Get-Content -Raw $mig | & $mysql @args
  if ($LASTEXITCODE -ne 0) { throw "mysql failed while applying migration." }
}

Write-Host "Done."
