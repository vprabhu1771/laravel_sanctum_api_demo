@echo off
REM Get the IPv4 address of the Wi-Fi or Ethernet adapter
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /R "IPv4"') do set IP=%%a

REM Remove any leading spaces from the IP address
set IP=%IP: =%

REM Check if IP address was found
if "%IP%"=="" (
    echo No IPv4 address found. Make sure you're connected to a network.
    exit /b
)

REM Start the Laravel development server
echo Starting Laravel server on IP address: %IP%
php artisan serve --host=%IP% --port=8000
