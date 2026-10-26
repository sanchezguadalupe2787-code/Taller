pipeline {
    agent any
    options { timestamps(); disableConcurrentBuilds() }
    parameters {
        booleanParam(name: 'DESPLEGAR_LOCAL', defaultValue: false,
            description: 'Activa solo después de revisar las pruebas. Copia el código a C:\\xampp\\htdocs\\taller_mecanico.')
    }
    environment {
        PHP_BIN = 'C:\\xampp\\php\\php.exe'
    }
    stages {
        stage('Preparar entorno') {
            steps { bat 'scripts\\preparar-entorno.bat' }
        }
        stage('Validar sintaxis PHP') {
            steps {
                powershell '''
                    $ErrorActionPreference = 'Stop'
                    $php = $env:PHP_BIN
                    if (!(Test-Path $php)) { throw "No se encontró PHP en $php. Ajusta PHP_BIN en Jenkinsfile." }
                    $archivos = @(Get-ChildItem -Path $env:WORKSPACE -Recurse -File -Filter *.php |
                        Where-Object { $_.FullName -notmatch '[\\\\](vendor|\.git|\.jenkins)[\\\\]' })
                    if ($archivos.Count -eq 0) { throw 'No se encontraron archivos PHP para validar.' }
                    $errores = @()
                    foreach ($archivo in $archivos) {
                        & $php -l $archivo.FullName
                        if ($LASTEXITCODE -ne 0) { $errores += $archivo.FullName }
                    }
                    if ($errores.Count -gt 0) { throw ('Falló la sintaxis en: ' + ($errores -join ', ')) }
                    Write-Host "Sintaxis correcta en $($archivos.Count) archivos PHP."
                '''
            }
        }
        stage('Pruebas de entorno') {
            steps { bat 'scripts\\ejecutar-pruebas.bat' }
        }
        stage('Despliegue local XAMPP') {
            when { expression { return params.DESPLEGAR_LOCAL } }
            steps { bat 'scripts\\desplegar.bat' }
        }
    }
    post {
        success { echo 'Pipeline D’Juniors finalizado correctamente.' }
        failure { echo 'Pipeline fallido. Revisa la consola antes de intentar desplegar.' }
    }
}
