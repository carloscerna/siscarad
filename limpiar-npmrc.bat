@echo off
echo ============================================
echo  Limpiando configuraciones viejas de npmrc
echo ============================================

REM Ruta del proyecto
set PROYECTO=C:\wamp64\www\siscarad\.npmrc

REM Ruta del usuario
set USUARIO=%USERPROFILE%\.npmrc

echo Revisando archivo de proyecto...
if exist "%PROYECTO%" (
    echo Eliminando lineas de npm.fontawesome.com en %PROYECTO%
    findstr /v "npm.fontawesome.com" "%PROYECTO%" > "%PROYECTO%.tmp"
    move /y "%PROYECTO%.tmp" "%PROYECTO%"
) else (
    echo No existe .npmrc en el proyecto.
)

echo Revisando archivo de usuario...
if exist "%USUARIO%" (
    echo Eliminando lineas de npm.fontawesome.com en %USUARIO%
    findstr /v "npm.fontawesome.com" "%USUARIO%" > "%USUARIO%.tmp"
    move /y "%USUARIO%.tmp" "%USUARIO%"
) else (
    echo No existe .npmrc en el perfil de usuario.
)

echo ============================================
echo  Limpieza terminada. Ahora ejecuta:
echo     npm install
echo     npm run dev
echo ============================================
pause
