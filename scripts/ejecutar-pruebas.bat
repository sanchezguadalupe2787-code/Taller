@echo off
setlocal
set "PHP=C:\xampp\php\php.exe"
if not exist "%PHP%" (
  echo ERROR: No se encontro PHP en %PHP%.
  exit /b 1
)
"%PHP%" tests\smoke_test.php
if errorlevel 1 exit /b 1
exit /b 0
