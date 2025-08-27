@echo off
echo ==============================================
echo  Verification finale - Repository GitHub
echo ==============================================

cd /d "d:\xampp\htdocs\marsa maroc project"

echo.
echo 1. Branche actuelle:
git branch

echo.
echo 2. Remote configure:
git remote -v

echo.
echo 3. Dernier commit:
git log --oneline -1

echo.
echo 4. Statut du repository:
git status

echo.
echo 5. Tentative de push force (si necessaire):
git push origin main --force

echo.
echo ==============================================
echo Verification terminee!
echo Consultez: https://github.com/Ahmedchaqri21/marsa-maroc-stage
echo ==============================================
pause
