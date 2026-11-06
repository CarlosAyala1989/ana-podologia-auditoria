@echo off
title Podologia Ana Ramirez - Servidor Local
echo ====================================================
echo   INICIANDO SISTEMA CLINICO: PODOLOGIA ANA RAMIREZ
echo ====================================================
echo.

:: Verificar si existe PHP en XAMPP o en PATH
set PHP_BIN=php
if exist "C:\xampp\php\php.exe" (
    set PHP_BIN=C:\xampp\php\php.exe
)

:: Mostrar IP local para conectar desde tablets/celulares
for /f "tokens=4" %%a in ('route print ^| findstr 0.0.0.0.*0.0.0.0') do (
    set LOCAL_IP=%%a
    goto :found_ip
)
:found_ip

echo [*] Servidor ejecutandose...
echo [*] Acceso local (esta PC):        http://localhost:8000
if defined LOCAL_IP (
    echo [*] Acceso desde Tablet / iPad:    http://%LOCAL_IP%:8000
)
echo.
echo Presione Ctrl + C para detener el servidor.
echo.

"%PHP_BIN%" -S 0.0.0.0:8000 -t public
pause
