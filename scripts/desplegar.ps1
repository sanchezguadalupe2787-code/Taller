$ErrorActionPreference = 'Stop'
$source = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$target = 'C:\xampp\htdocs\taller_mecanico'
if (!(Test-Path 'C:\xampp\htdocs')) { throw 'No existe C:\xampp\htdocs. Comprueba que XAMPP esté instalado en C:\xampp.' }
if ($source.TrimEnd('\') -eq $target.TrimEnd('\')) { throw 'El origen y destino son iguales; se cancela el despliegue.' }
# Respaldar la configuración local existente para conservar credenciales.
$dbConfig = Join-Path $target 'config\db.php'
$backupConfig = $null
if (Test-Path $dbConfig) {
    $backupConfig = Join-Path $env:TEMP ('djuniors-db-' + [guid]::NewGuid().ToString('N') + '.php')
    Copy-Item $dbConfig $backupConfig -Force
}
New-Item -ItemType Directory -Path $target -Force | Out-Null
# Copia la aplicación; omite SQL y .env para no alterar datos ni secretos.
$robocopyArgs = @($source, $target, '/E', '/R:2', '/W:2', '/NFL', '/NDL', '/NP', '/XD', '.git', '.github', '.idea', '/XF', '*.sql', '.env')
& robocopy @robocopyArgs
$rc = $LASTEXITCODE
if ($rc -ge 8) { throw "Robocopy falló con código $rc." }
if ($backupConfig) {
    Copy-Item $backupConfig $dbConfig -Force
    Remove-Item $backupConfig -Force
    Write-Host 'Se conservó config/db.php del entorno local.'
}
Write-Host "Despliegue de archivos completado en $target. No se ejecutaron scripts SQL."
Write-Host 'Comprueba Apache y MySQL en XAMPP y abre http://localhost/taller_mecanico.'
