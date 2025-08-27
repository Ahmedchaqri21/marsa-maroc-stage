@echo off
echo ========================================
echo    Push vers GitHub - Marsa Maroc
echo ========================================

cd /d "d:\xampp\htdocs\marsa maroc project"

echo.
echo Statut Git actuel:
git status

echo.
echo Verification des remotes:
git remote -v

echo.
echo Verification de la branche:
git branch

echo.
echo Dernier commit local:
git log --oneline -1

echo.
echo ===========================================
echo Tentative de push vers origin master...
echo ===========================================
git push -u origin master

if %errorlevel% neq 0 (
    echo.
    echo Erreur avec master, renommage vers main...
    git branch -M main
    echo Tentative avec main...
    git push -u origin main
)

echo.
echo Verification des branches distantes:
git branch -a

echo.
echo Script termine! Verifiez sur GitHub.
pause
