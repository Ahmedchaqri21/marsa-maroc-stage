#!/bin/bash
# Script de vérification et push Git

echo "🔍 Vérification de l'état du repository Git..."
echo "================================================"

# Navigation vers le projet
cd "d:\xampp\htdocs\marsa maroc project"

echo "📁 Répertoire actuel: $(pwd)"
echo ""

echo "🌿 Branches locales:"
git branch

echo ""
echo "🔗 Remotes configurés:"
git remote -v

echo ""
echo "📝 Dernier commit:"
git log --oneline -1

echo ""
echo "📊 Statut du repository:"
git status --porcelain

echo ""
echo "🔄 Tentative de push vers origin master..."
git push -u origin master

if [ $? -eq 0 ]; then
    echo "✅ Push réussi!"
else
    echo "❌ Échec du push. Tentative avec main..."
    git branch -M main
    git push -u origin main
fi

echo ""
echo "🔍 Vérification des branches distantes:"
git branch -a

echo ""
echo "✨ Script terminé."
