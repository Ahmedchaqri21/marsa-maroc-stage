# Marsa Maroc - Système de Gestion des Emplacements Portuaires

## Description
Système complet de gestion des emplacements portuaires avec tableau de bord administrateur, gestion des utilisateurs, réservations et statistiques.

## Fonctionnalités

### 🏗️ Gestion des Emplacements Portuaires
- Ajouter, modifier, supprimer des emplacements
- Gérer les informations (nom, superficie, tarif, état)
- Suivre le statut (disponible, occupé, maintenance)

### 📋 Gestion des Réservations
- Valider ou refuser les demandes de réservation
- Consulter l'historique des locations
- Calcul automatique des montants

### 👥 Gestion des Utilisateurs
- Ajouter, modifier, supprimer des utilisateurs
- Gestion des rôles (admin/user)
- Informations de contact complètes

### 📊 Tableau de Bord et Statistiques
- Revenus générés
- Taux d'occupation
- Statistiques détaillées

## Installation et Configuration

### Prérequis
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur

### 1. Configuration de la Base de Données

#### Option A: Utiliser le script SQL fourni
1. Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
2. Créez une nouvelle base de données nommée `marsa_maroc_db`
3. Importez le fichier `database/schema.sql`

#### Option B: Créer manuellement les tables
```sql
-- Créer la base de données
CREATE DATABASE marsa_maroc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE marsa_maroc_db;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des emplacements
CREATE TABLE emplacements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    superficie DECIMAL(10,2) NOT NULL,
    tarif DECIMAL(10,2) NOT NULL,
    etat ENUM('disponible', 'occupe', 'maintenance') DEFAULT 'disponible',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des réservations
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    emplacement_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('en_attente', 'validee', 'refusee', 'terminee') DEFAULT 'en_attente',
    montant_total DECIMAL(10,2) NOT NULL,
    commentaire TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (emplacement_id) REFERENCES emplacements(id) ON DELETE CASCADE
);
```

### 2. Configuration de la Connexion

1. Modifiez le fichier `config/database.php` selon votre configuration :
```php
define('DB_HOST', 'localhost');        // Votre hôte MySQL
define('DB_NAME', 'marsa_maroc_db');   // Nom de votre base de données
define('DB_USER', 'root');             // Votre nom d'utilisateur MySQL
define('DB_PASS', '');                 // Votre mot de passe MySQL
```

### 3. Données Initiales

Le script SQL inclut des données de test :
- **Utilisateur admin par défaut** :
  - Username: `admin`
  - Password: `password`
  - Email: `admin@marsamaroc.ma`

- **Emplacements de test** :
  - Quai A1, A2, B1, B2 avec différents états

- **Réservations de test** pour démonstration

## Structure des Fichiers

```
marsa maroc project/
├── index.php                 # Page d'accueil
├── login.php                 # Page de connexion
├── admin-dashboard.php       # Tableau de bord administrateur
├── config/
│   └── database.php         # Configuration de la base de données
├── api/
│   ├── auth.php             # Authentification
│   ├── emplacements.php     # Gestion des emplacements
│   ├── reservations.php     # Gestion des réservations
│   ├── users.php            # Gestion des utilisateurs
│   ├── statistics.php       # Statistiques
│   └── logout.php           # Déconnexion
└── database/
    └── schema.sql           # Structure de la base de données
```

## Utilisation

### 1. Accès au Système
1. Démarrez XAMPP (Apache + MySQL)
2. Ouvrez http://localhost/marsa%20maroc%20project/
3. Cliquez sur "Se Connecter" ou allez directement à http://localhost/marsa%20maroc%20project/login.php

### 2. Connexion Administrateur
- **Username**: `admin`
- **Password**: `password`
- **Rôle**: Sélectionnez "Administrateur"

### 3. Navigation dans le Tableau de Bord
- **Vue d'ensemble** : Statistiques générales
- **Emplacements** : Gestion des emplacements portuaires
- **Réservations** : Validation et suivi des réservations
- **Utilisateurs** : Gestion des comptes utilisateurs
- **Statistiques** : Rapports détaillés

## API Endpoints

### Authentification
- `POST /api/auth.php` - Connexion utilisateur
- `POST /api/logout.php` - Déconnexion

### Emplacements
- `GET /api/emplacements.php` - Liste des emplacements
- `GET /api/emplacements.php?id={id}` - Détails d'un emplacement
- `POST /api/emplacements.php` - Créer un emplacement
- `PUT /api/emplacements.php?id={id}` - Modifier un emplacement
- `DELETE /api/emplacements.php?id={id}` - Supprimer un emplacement

### Réservations
- `GET /api/reservations.php` - Liste des réservations
- `GET /api/reservations.php?id={id}` - Détails d'une réservation
- `POST /api/reservations.php` - Créer une réservation
- `PUT /api/reservations.php?id={id}` - Modifier le statut
- `DELETE /api/reservations.php?id={id}` - Supprimer une réservation

### Utilisateurs
- `GET /api/users.php` - Liste des utilisateurs
- `GET /api/users.php?id={id}` - Détails d'un utilisateur
- `POST /api/users.php` - Créer un utilisateur
- `PUT /api/users.php?id={id}` - Modifier un utilisateur
- `DELETE /api/users.php?id={id}` - Supprimer un utilisateur

### Statistiques
- `GET /api/statistics.php` - Données statistiques complètes

## Sécurité

- **Mots de passe** : Hachés avec `password_hash()`
- **Préparation des requêtes** : Protection contre les injections SQL
- **Validation des données** : Vérification côté serveur
- **Gestion des sessions** : Authentification sécurisée

## Dépannage

### Erreur de Connexion à la Base de Données
1. Vérifiez que MySQL est démarré dans XAMPP
2. Vérifiez les paramètres de connexion dans `config/database.php`
3. Testez la connexion avec `config/database.php` dans le navigateur

### Erreur 500 (Internal Server Error)
1. Vérifiez les logs d'erreur Apache
2. Assurez-vous que PHP est activé dans XAMPP
3. Vérifiez la syntaxe des fichiers PHP

### Problèmes d'Authentification
1. Vérifiez que la table `users` contient des données
2. Utilisez le compte admin par défaut : `admin` / `password`
3. Vérifiez que les sessions PHP fonctionnent

## Support

Pour toute question ou problème :
1. Vérifiez la configuration de votre environnement XAMPP
2. Consultez les logs d'erreur
3. Testez les endpoints API individuellement

## Licence

Ce projet est développé pour Marsa Maroc. Tous droits réservés.

