@echo off
echo ============================================
echo  Reiniciando entorno Vite + Bootstrap
echo ============================================

REM Paso 1 - eliminar node_modules y package-lock.json
echo Eliminando node_modules y package-lock.json...
rmdir /s /q node_modules
del /f /q package-lock.json

REM Paso 2 - instalar dependencias principales
echo Instalando dependencias principales...
npm install vite laravel-vite-plugin --save-dev
npm install bootstrap @fortawesome/fontawesome-free

REM Paso 3 - instalar dependencias de desarrollo (sass opcional)
echo Instalando Sass para estilos personalizados...
npm install sass --save-dev

REM Paso 4 - reinstalar todas las dependencias
echo Reinstalando todas las dependencias...
npm install

echo ============================================
echo  Entorno listo. Ejecuta ahora:
echo     npm run dev
echo ============================================
pause
