# 🏖️ Family Rental — Gestionnaire de réservations familial

Une application web légère en PHP pour gérer les réservations d'une villa ou d'un appartement familial. Les membres s'inscrivent, soumettent des demandes de séjour, et un administrateur les valide. Toutes les actions envoient des notifications par email.

---

## ✨ Fonctionnalités

- **Inscription & validation** — Les nouveaux membres attendent l'approbation d'un administrateur avant de pouvoir se connecter
- **Planning** — Vue calendaire des séjours confirmés et en attente
- **Réservations** — Soumission de demandes avec détection des chevauchements
- **Modification** — Un membre peut modifier les dates d'un séjour existant (repart en validation)
- **Annulation** — Un membre peut annuler un séjour en autonomie, les dates sont libérées immédiatement
- **Notifications email** — Emails automatiques à chaque étape (inscription, validation, approbation, refus, modification, annulation, réinitialisation de mot de passe)
- **Réinitialisation de mot de passe** — Via lien sécurisé à durée limitée (1 heure)
- **PWA** — Installable sur Android (Chrome) et iPhone (Safari) comme une application native
- **Interface responsive** — Bootstrap 5, thème chaud et élégant

---

## 🗂️ Structure des fichiers

```
├── config.php          ← ⚙️ Configuration (BDD, URL, nom de l'app) — à éditer
├── mailer.php          ← 📧 Fonctions d'envoi d'email (PHPMailer + Gmail SMTP)
├── schema.sql          ← 🗄️ Schéma de base de données à importer
│
├── index.php           ← Redirection selon le rôle
├── login.php           ← Connexion
├── logout.php          ← Déconnexion
├── register.php        ← Inscription
├── forgot_password.php ← Demande de réinitialisation de mot de passe
├── reset_password.php  ← Formulaire de nouveau mot de passe
│
├── planning.php        ← Planning global + section "Mes séjours"
├── reserver.php        ← Formulaire de réservation (création & modification)
├── admin.php           ← Panneau d'administration
│
├── header.php          ← En-tête HTML commun (navbar, styles)
├── footer.php          ← Pied de page HTML commun
└── manifest.json       ← Manifeste PWA
```

---

## 🚀 Installation

### Prérequis

- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Un serveur SMTP (exemple : compte Gmail avec mot de passe d'application)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)

### Étapes

**1. Déposez les fichiers** sur votre serveur web (Apache, Nginx…).

**2. Installez PHPMailer** dans le dossier du projet :

```bash
# Via Composer (recommandé)
composer require phpmailer/phpmailer

# Puis dans mailer.php, remplacez les trois lignes require par :
# require 'vendor/autoload.php';
```

Ou téléchargez manuellement les sources dans un dossier `PHPMailer/src/`.

**3. Importez le schéma SQL** dans votre base de données :

```bash
mysql -u your_user -p your_database < schema.sql
```

**4. Configurez `config.php`** avec vos propres valeurs :

```php
define('DB_HOST',     'localhost');
define('DB_NAME',     'your_database_name');
define('DB_USER',     'your_database_user');
define('DB_PASSWORD', 'your_database_password');

define('APP_NAME',  'Ma Villa');
define('APP_URL',   'https://example.com/villa');  // sans slash final
define('APP_OWNER', 'Votre Nom');
```

**5. Configurez `mailer.php`** avec vos identifiants SMTP :

```php
define('MAIL_USERNAME', 'votre.adresse@gmail.com');
define('MAIL_PASSWORD', 'xxxx xxxx xxxx xxxx'); // mot de passe d'application Gmail
```

> **Gmail** : pour obtenir un mot de passe d'application, activez d'abord la validation en deux étapes sur votre compte Google, puis rendez-vous dans *Sécurité → Mots de passe des applications*.

**6. Créez le premier compte administrateur** en exécutant dans MySQL :

```sql
-- Générez d'abord le hash en PHP :
-- php -r "echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);"

INSERT INTO users (username, email, password, role, is_active)
VALUES ('admin', 'admin@example.com', '$2y$...votre_hash...', 'admin', 1);
```

**7. Mettez à jour `manifest.json`** si vous souhaitez personnaliser le nom de la PWA.

---

## 🔒 Sécurité

- Les mots de passe sont hachés avec `password_hash()` (bcrypt)
- Les requêtes SQL utilisent des **prepared statements** (PDO) — pas d'injection SQL possible
- Les tokens de réinitialisation de mot de passe expirent après **1 heure**
- Les sorties HTML sont systématiquement échappées avec `htmlspecialchars()`
- L'accès aux pages est protégé par vérification de session à chaque requête
- Le panneau admin est réservé au rôle `admin`

> ⚠️ **Ne commitez jamais `config.php`** avec vos vraies credentials dans un dépôt public. Ajoutez-le à votre `.gitignore`.

---

## 🎨 Personnalisation

Le thème graphique (palette cognac / ardoise / or) est entièrement défini dans `header.php` via des variables CSS. Vous pouvez l'adapter librement en modifiant les valeurs dans le bloc `:root { ... }`.

---

## 📄 Licence

MIT License — libre d'utilisation, de modification et de redistribution.
