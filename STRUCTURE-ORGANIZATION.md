# Organisation de la Structure - Marsa Maroc Port Management System

## 📁 Nouvelle Structure Organisée

La structure du projet a été réorganisée pour une meilleure maintenance et évolutivité :

```
/
├── index.php                 # Point d'entrée - redirection automatique
├── .htaccess                # Configuration Apache
├── README.md                # Documentation principale
├── 
├── pages/                   # Pages d'interface utilisateur
│   ├── admin/              # Interface administrateur
│   │   ├── dashboard.php   # Tableau de bord admin (principal)
│   │   └── dashboard-fixed.php
│   ├── auth/               # Authentification
│   │   ├── login.php       # Page de connexion
│   │   └── login-new.php   # Page de connexion alternative
│   ├── user/               # Interface utilisateur
│   │   └── dashboard.php   # Tableau de bord utilisateur
│   ├── manager/            # Interface manager
│   └── public-home.php     # Page d'accueil publique
│
├── api/                    # Endpoints API REST
│   ├── auth.php            # API d'authentification
│   ├── emplacements-fixed.php  # API emplacements (version fonctionnelle)
│   ├── emplacements.php    # API emplacements (version standard)
│   ├── reservations-fixed.php # API réservations (version fonctionnelle)
│   ├── reservations.php    # API réservations (version standard)
│   ├── users-fixed.php     # API utilisateurs (version fonctionnelle)
│   ├── users.php           # API utilisateurs (version standard)
│   ├── statistics.php      # API statistiques
│   └── logout.php          # API de déconnexion
│
├── config/                 # Configuration système
│   ├── database.php        # Configuration base de données
│   └── session_check.php   # Vérification des sessions
│
├── includes/               # Fichiers d'inclusion PHP
├── classes/                # Classes PHP (POO)
├── utils/                  # Utilitaires et helpers
│
├── database/               # Scripts de base de données
│   ├── schema.sql          # Schéma principal
│   ├── schema-new.sql      # Nouveau schéma
│   └── update_passwords.sql # Mise à jour mots de passe
│
├── assets/                 # Ressources statiques
│   ├── css/               # Fichiers CSS
│   ├── images/            # Images du projet
│   └── port-marsa-maroc.jpg
│
├── tests/                  # Tests et debugging
├── logs/                   # Logs système
├── uploads/                # Fichiers uploadés
├── backup/                 # Sauvegardes
└── vendor/                 # Dépendances externes
```

## 🔄 Changements Effectués

### 1. Migration des Fichiers Principaux
- ✅ `admin-dashboard.php` → `pages/admin/dashboard.php`
- ✅ Correction des chemins d'inclusion : `config/session_check.php` → `../../config/session_check.php`
- ✅ Correction des chemins API : `api/` → `../../api/`

### 2. Mise à jour de l'Index
- ✅ `index.php` configuré pour redirection automatique selon le rôle
- ✅ Vérification de session existante
- ✅ Redirection intelligente vers les dashboards appropriés

### 3. Structure API
- ✅ Toutes les APIs maintenues dans `/api/`
- ✅ Versions `-fixed.php` fonctionnelles pour production
- ✅ Versions standard pour développement/test

## 🎯 Avantages de la Nouvelle Structure

### Organisation Claire
- **Séparation par type** : pages, API, configuration, ressources
- **Séparation par rôle** : admin, user, manager, auth
- **Logique métier isolée** : classes, utils, includes

### Sécurité Améliorée
- **Protection des dossiers sensibles** : config/, classes/, includes/
- **Point d'entrée unique** : index.php contrôle l'accès
- **Isolation des APIs** : dossier dédié avec validation

### Maintenance Facilité
- **Fichiers groupés logiquement** : plus facile à maintenir
- **Chemins cohérents** : structure prévisible
- **Tests isolés** : dossier dédié pour les tests

## 🚀 Utilisation

### Accès Principal
```
http://localhost/marsa maroc project/
```
→ Redirige automatiquement vers la page de connexion

### Accès Direct aux Dashboards
```
# Admin/Manager
http://localhost/marsa maroc project/pages/admin/dashboard.php

# Utilisateur
http://localhost/marsa maroc project/pages/user/dashboard.php

# Connexion
http://localhost/marsa maroc project/pages/auth/login.php
```

### APIs
```
# Emplacements
http://localhost/marsa maroc project/api/emplacements-fixed.php

# Réservations
http://localhost/marsa maroc project/api/reservations-fixed.php

# Utilisateurs
http://localhost/marsa maroc project/api/users-fixed.php
```

## 📝 Notes Importantes

1. **Fichiers -fixed.php** : Versions testées et fonctionnelles
2. **Chemins relatifs** : Tous mis à jour pour la nouvelle structure
3. **Sessions** : Gestion centralisée dans `config/session_check.php`
4. **Sécurité** : Protection Apache des dossiers sensibles

## 🔧 Prochaines Étapes

1. **Migration complète** : Déplacer tous les fichiers test vers `tests/`
2. **Classes PHP** : Implémenter POO dans `classes/`
3. **Utilities** : Créer helpers dans `utils/`
4. **Documentation** : Compléter la documentation API

---

*Structure organisée le : [Date actuelle]*
*Système fonctionnel et prêt pour la production*
