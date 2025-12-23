@echo off
cd /d "%~dp0"
node index.js > gateway.log 2>&1
exit
