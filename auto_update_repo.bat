@echo off
title Auto Update Repo Pengaduan
echo ===========================================
echo     AUTO UPDATE REPO PENGADUAN (GitHub)
echo ===========================================
echo.

cd /d "C:\xampp\htdocs\pengaduan"

:: Buat pesan commit otomatis berdasarkan waktu
for /f "tokens=1-4 delims=/ " %%a in ('date /t') do set tanggal=%%a-%%b-%%c
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set jam=%%a-%%b
set commitmsg=Auto update on %tanggal%_%jam%

echo Menambahkan semua perubahan...
git add .
echo.

echo Membuat commit otomatis...
git commit -m "%commitmsg%"
echo.

echo Mengirim ke GitHub...
git push origin main
echo.

echo ===========================================
echo Update selesai! Periksa di GitHub:
echo https://github.com/Comoss969/pengaduan
echo ===========================================

pause
