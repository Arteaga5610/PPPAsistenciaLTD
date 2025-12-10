@echo off
cd /d %~dp0
echo ========================================
echo Limpiando cachés...
echo ========================================
call php artisan route:clear
call php artisan config:clear
call php artisan cache:clear
echo.
echo ========================================
echo Rutas de Employees registradas:
echo ========================================
call php artisan route:list | findstr "employees"
echo.
echo ========================================
echo Verificando metodo bulkDestroy:
echo ========================================
call php artisan route:list | findstr "bulk"
echo.
pause
