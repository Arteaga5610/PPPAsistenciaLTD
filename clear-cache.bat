@echo off
cd /d %~dp0
echo Limpiando cache de rutas...
php artisan route:clear
echo.
echo Limpiando cache de configuracion...
php artisan config:clear
echo.
echo Limpiando cache de vistas...
php artisan view:clear
echo.
echo Cache limpiada exitosamente!
echo.
echo Listando rutas de employees...
php artisan route:list --path=employees
pause
