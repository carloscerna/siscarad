@echo off
echo ============================================
echo  Migrando proyecto Laravel Mix -> Vite
echo ============================================

:: Paso 1 - eliminar node_modules y package-lock.json
echo Eliminando node_modules y package-lock.json...
rmdir /s /q node_modules
del /f /q package-lock.json

:: Paso 2 - desinstalar laravel-mix y webpack
echo Desinstalando laravel-mix y webpack...
npm uninstall laravel-mix webpack webpack-cli sass-loader

:: Paso 3 - instalar vite y plugin de laravel
echo Instalando Vite y laravel-vite-plugin...
npm install vite laravel-vite-plugin --save-dev

:: Paso 4 - instalar bootstrap y sass
echo Instalando Bootstrap 5 y Sass...
npm install bootstrap@5.3.3 sass --save-dev

:: Paso 5 - reinstalar todas las dependencias
echo Reinstalando todas las dependencias...
npm install

echo ============================================
echo  Migración completada. Ajusta tu layout Blade:
echo  Reemplaza {{ mix('css/app.css') }} por:
echo  @vite(['resources/css/app.css','resources/js/app.js'])
echo ============================================
pause
