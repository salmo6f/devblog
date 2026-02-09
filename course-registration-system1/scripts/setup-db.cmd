@echo off
setlocal

set HOST=%CRS_DB_HOST%
if "%HOST%"=="" set HOST=localhost

set USER=%CRS_DB_USER%
if "%USER%"=="" set USER=root

set PASS=%CRS_DB_PASS%
set DB=%CRS_DB_NAME%
if "%DB%"=="" set DB=course_registration

set MYSQL=C:\xampp\mysql\bin\mysql.exe
if not exist "%MYSQL%" set MYSQL=mysql

echo Creating database (if not exists): %DB%
echo CREATE DATABASE IF NOT EXISTS `%DB%` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; | "%MYSQL%" -h %HOST% -u %USER% --default-character-set=utf8mb4

echo Importing database.sql...
"%MYSQL%" -h %HOST% -u %USER% --default-character-set=utf8mb4 < "%~dp0..\database.sql"

if /I "%1"=="--modern" (
  echo Applying modern migration...
  "%MYSQL%" -h %HOST% -u %USER% --default-character-set=utf8mb4 < "%~dp0..\migrations\001_modern_school.sql"
)

echo Done.
endlocal
