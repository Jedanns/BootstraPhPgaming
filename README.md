# BootstraPhPgaming

![PHP Version](https://img.shields.io/badge/PHP-8.2-blue)
![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.3-purple)
![Docker](https://img.shields.io/badge/Docker-Enabled-brightgreen)
![Project](https://img.shields.io/badge/Project-Educational-orange)

Une application web moderne et modulaire développée avec PHP, MVC, Bootstrap et Docker, conçue pour héberger des jeux, compteurs et outils collaboratifs. Projet personnel et scolaire.

## 📋 Sommaire

- [Fonctionnalités](#-fonctionnalités)
- [Technologies](#-technologies)
- [Architecture](#-architecture)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Utilisation](#-utilisation)
- [Personnalisation](#-personnalisation)
- [Accès réseau](#-accès-réseau)
- [Dépannage](#-dépannage)
- [Contribution](#-contribution)
- [Création avec PowerShell](#-création-avec-powershell)

## ✨ Fonctionnalités

- **Authentification complète** : Inscription, connexion et gestion des utilisateurs
- **Tableau de bord personnalisé** : Interface utilisateur intuitive et responsive
- **Architecture modulaire** : Facilite l'ajout de nouveaux modules
- **Interface moderne** : Design élégant avec Bootstrap 5
- **Gestion des erreurs** : Pages personnalisées pour les erreurs 404, 403, 500
- **Sécurité intégrée** : Protection contre les injections SQL, XSS et CSRF
- **Système extensible** : Prêt pour l'ajout de fonctionnalités (jeux, compteurs, gestionnaire de tâches)

## 🚀 Technologies

- **Backend** : PHP 8.2, Architecture MVC
- **Frontend** : HTML5, CSS3, JavaScript, Bootstrap 5
- **Base de données** : MySQL 8.0
- **Environnement** : Docker, Docker Compose
- **Outils** : PHPMyAdmin, Composer
- **Sécurité** : PDO avec requêtes préparées, validation des formulaires

## 🏗 Architecture

Le projet suit une architecture MVC (Modèle-Vue-Contrôleur) rigoureuse :

```
project/
├── app/                  # Code source principal
│   ├── config/           # Configuration de l'application
│   ├── controllers/      # Contrôleurs MVC
│   ├── models/           # Modèles pour l'accès aux données
│   ├── views/            # Vues et templates
│   │   ├── layouts/      # Layouts principaux
│   │   └── pages/        # Pages spécifiques
│   └── helpers/          # Fonctions utilitaires
├── public/               # Point d'entrée public
│   ├── index.php         # Front controller
│   ├── css/              # Styles CSS
│   ├── js/               # Scripts JavaScript
│   └── img/              # Images
├── docker/               # Configuration Docker
│   ├── php/              # Configuration PHP
│   └── mysql/            # Configuration MySQL
├── vendor/               # Dépendances (gérées par Composer)
├── .env                  # Variables d'environnement
└── docker-compose.yml    # Configuration des conteneurs
```

## 📋 Prérequis

- Docker Desktop (Windows/Mac) ou Docker Engine + Docker Compose (Linux)
- Git (optionnel)
- Navigateur web moderne (Chrome, Firefox, Edge, Safari)

## 🔧 Installation

### 1. Cloner le dépôt (ou télécharger les fichiers)

```bash
git clone https://github.com/votre-nom/bootstraphpgaming.git
cd bootstraphpgaming
```

### 2. Créer les dossiers requis

Pour Linux/macOS :
```bash
mkdir -p app/config app/controllers app/models app/views/layouts app/views/pages/home app/views/pages/auth app/views/pages/error app/helpers
mkdir -p public/css public/js public/img
mkdir -p docker/php docker/mysql/init
```

Pour Windows (PowerShell) :
```powershell
mkdir -Force -Path app/config, app/controllers, app/models, app/views/layouts, app/views/pages/home, app/views/pages/auth, app/views/pages/error, app/helpers
mkdir -Force -Path public/css, public/js, public/img
mkdir -Force -Path docker/php, docker/mysql/init
```

### 3. Configurer les variables d'environnement

Modifier le fichier `.env` selon vos besoins :

```dotenv
# Configuration de la base de données
DB_NAME=myapp
DB_USER=user
DB_PASSWORD=password
DB_ROOT_PASSWORD=rootpassword

# Configuration de l'application
APP_NAME="Mon Application"
APP_ENV=development
APP_DEBUG=true

# Clé de sécurité (à changer pour la production)
APP_KEY=base64:6w12345678901234567890123456789=
```

### 4. Démarrer les conteneurs Docker

```bash
docker-compose up -d
```

### 5. Installer les dépendances

```bash
docker exec -it php_app composer install
```

### 6. Accéder à l'application

Ouvrez votre navigateur et accédez à : http://localhost:8080

## 🖥 Utilisation

### Identifiants par défaut

Un utilisateur administrateur est créé par défaut avec les identifiants suivants :
- **Utilisateur** : admin
- **Email** : admin@example.com
- **Mot de passe** : admin123

### Navigation

- **Page d'accueil** : Présentation de l'application
- **Inscription/Connexion** : Création et gestion de compte
- **Tableau de bord** : Centre de contrôle personnel avec modules disponibles
- **Profil** : Gestion des informations personnelles

### PHPMyAdmin

Pour gérer la base de données, accédez à : http://localhost:8081
- **Serveur** : db
- **Utilisateur** : root ou celui défini dans .env
- **Mot de passe** : celui défini dans .env

## 🎨 Personnalisation

### Ajouter un nouveau module

1. Créer un nouveau contrôleur dans `app/controllers/`
2. Ajouter des modèles associés dans `app/models/`
3. Créer les vues dans `app/views/pages/nom-du-module/`
4. Ajouter les liens de navigation dans `app/views/layouts/default.php`

### Modifier l'apparence

- Personnaliser les styles CSS dans `public/css/styles.css`
- Modifier le layout principal dans `app/views/layouts/default.php`
- Ajouter des scripts JavaScript dans `public/js/scripts.js`

## 🌐 Accès réseau

Pour permettre à d'autres utilisateurs sur votre réseau local d'accéder à l'application :

1. Découvrez l'adresse IP de votre machine sur le réseau local
   ```bash
   # Windows
   ipconfig
   
   # macOS / Linux
   ifconfig
   ```

2. Partagez l'URL avec vos amis : `http://VOTRE_IP:8080`
   (ex: http://192.168.1.10:8080)

## ❓ Dépannage

### Problèmes courants

- **Erreur de connexion à la base de données** :
  - Vérifiez les identifiants dans le fichier `.env`
  - Assurez-vous que le conteneur MySQL est en cours d'exécution
  
- **Page blanche ou erreur 500** :
  - Vérifiez les logs Apache : `docker exec -it php_app cat /var/log/apache2/error.log`
  - Activez le mode debug dans `.env` : `APP_DEBUG=true`

- **Problèmes d'autoload** :
  - Exécutez : `docker exec -it php_app composer dump-autoload`

### Commandes utiles

- **Redémarrer les conteneurs** : `docker-compose restart`
- **Voir les logs** : `docker-compose logs -f`
- **Accéder au shell PHP** : `docker exec -it php_app bash`

## 👥 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/amazing-feature`)
3. Committez vos changements (`git commit -m 'Add some amazing feature'`)
4. Poussez vers la branche (`git push origin feature/amazing-feature`)
5. Ouvrez une Pull Request

## 🖥️ Création avec PowerShell

Vous pouvez utiliser PowerShell pour créer rapidement tous les fichiers nécessaires. Voici un exemple de script :

```powershell
# Création des dossiers
mkdir -Force -Path app/config, app/controllers, app/models, app/views/layouts, app/views/pages/home, app/views/pages/auth, app/views/pages/error, app/helpers
mkdir -Force -Path public/css, public/js, public/img
mkdir -Force -Path docker/php, docker/mysql/init

# Création des fichiers de configuration
New-Item -Force -Path docker/php/Dockerfile -ItemType File
New-Item -Force -Path docker/php/apache-config.conf -ItemType File
New-Item -Force -Path docker-compose.yml -ItemType File
New-Item -Force -Path .env -ItemType File
New-Item -Force -Path composer.json -ItemType File

# Création des fichiers PHP principaux
New-Item -Force -Path app/config/config.php -ItemType File
New-Item -Force -Path app/config/Router.php -ItemType File
New-Item -Force -Path public/index.php -ItemType File
New-Item -Force -Path public/.htaccess -ItemType File

# Création des contrôleurs
New-Item -Force -Path app/controllers/BaseController.php -ItemType File
New-Item -Force -Path app/controllers/HomeController.php -ItemType File
New-Item -Force -Path app/controllers/AuthController.php -ItemType File
New-Item -Force -Path app/controllers/ErrorController.php -ItemType File

# Création des modèles
New-Item -Force -Path app/models/Database.php -ItemType File
New-Item -Force -Path app/models/BaseModel.php -ItemType File
New-Item -Force -Path app/models/UserModel.php -ItemType File

# Création des vues
New-Item -Force -Path app/views/layouts/default.php -ItemType File
New-Item -Force -Path app/views/pages/home/index.php -ItemType File
New-Item -Force -Path app/views/pages/home/dashboard.php -ItemType File
New-Item -Force -Path app/views/pages/auth/login.php -ItemType File
New-Item -Force -Path app/views/pages/auth/register.php -ItemType File
New-Item -Force -Path app/views/pages/error/404.php -ItemType File
New-Item -Force -Path app/views/pages/error/403.php -ItemType File
New-Item -Force -Path app/views/pages/error/500.php -ItemType File

# Création des fichiers SQL
New-Item -Force -Path docker/mysql/init/01-users.sql -ItemType File

# Création des assets
New-Item -Force -Path public/css/styles.css -ItemType File
New-Item -Force -Path public/js/scripts.js -ItemType File
```

Après avoir créé les fichiers, vous devrez copier le contenu approprié dans chacun d'eux.

---

Projet personnel et scolaire développé avec ❤️ par Lucas Dias