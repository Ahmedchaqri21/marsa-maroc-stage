# Script de Push Git - Marsa Maroc Project
# Ce script pousse le code vers le repository GitHub

Write-Host "🚀 Push vers GitHub - Marsa Maroc Project" -ForegroundColor Cyan
Write-Host "Repository: https://github.com/Ahmedchaqri21/marsa-maroc-stage.git" -ForegroundColor Yellow

# Changer vers le répertoire du projet
Set-Location "d:\xampp\htdocs\marsa maroc project"

# Vérifier le statut Git
Write-Host "`n📊 Statut Git actuel:" -ForegroundColor Green
git status

# Vérifier les remotes
Write-Host "`n🔗 Repositories distants:" -ForegroundColor Green
git remote -v

# Vérifier les commits
Write-Host "`n📝 Dernier commit:" -ForegroundColor Green
git log --oneline -1

# Tenter le push
Write-Host "`n⬆️ Push vers GitHub:" -ForegroundColor Green
try {
    git push -u origin master
    Write-Host "✅ Push réussi!" -ForegroundColor Green
} catch {
    Write-Host "❌ Erreur lors du push: $_" -ForegroundColor Red
    
    # Tenter avec la branche main
    Write-Host "`n🔄 Tentative avec la branche main:" -ForegroundColor Yellow
    try {
        git push -u origin main
        Write-Host "✅ Push réussi vers main!" -ForegroundColor Green
    } catch {
        Write-Host "❌ Erreur avec main également: $_" -ForegroundColor Red
    }
}

# Vérifier le résultat final
Write-Host "`n🔍 Vérification finale:" -ForegroundColor Green
git branch -a

Write-Host "`n✨ Script terminé. Vérifiez votre repository GitHub!" -ForegroundColor Cyan
