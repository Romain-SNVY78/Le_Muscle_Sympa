# Le Muscle Sympa 💪

Site web complet pour une salle de sport : gestion des membres, abonnements et programmes sportifs personnalisés.

---

## Prérequis

- [XAMPP](https://www.apachefriends.org/fr/index.html) (Apache + MySQL)
- Un navigateur web

---

## Installation

### 1. Télécharger le projet

**Via GitHub (ZIP) :**
1. Aller sur https://github.com/Romain-SNVY78/Le_Muscle_Sympa
2. Cliquer sur le bouton vert **"Code"**
3. Cliquer sur **"Download ZIP"**
4. Extraire le contenu dans `C:\xampp\htdocs\`
5. S'assurer que le dossier s'appelle bien **`Le_Muscle_Sympa`**

---

### 2. Démarrer XAMPP

1. Ouvrir **XAMPP Control Panel**
2. Cliquer sur **Start** pour **Apache**
3. Cliquer sur **Start** pour **MySQL**

---

### 3. Mettre en place la base de données

1. Ouvrir [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Dans le panneau gauche, cliquer sur **"Nouvelle base de données"**
3. Saisir le nom : `lms`
4. Cliquer sur **"Créer"**
5. Cliquer sur la base **`lms`** dans le panneau gauche
6. Cliquer sur l'onglet **"Importer"**
7. Cliquer sur **"Choisir un fichier"** et sélectionner :
   ```
   C:\xampp\htdocs\Le_Muscle_Sympa\app\migrations\database.sql
   ```
8. Cliquer sur **"Importer"**

> Cela crée automatiquement les 3 tables (`users`, `password_resets`, `user_programs`) et charge les données de test.

---

### 4. Accéder au site

Ouvrir dans le navigateur :

```
http://localhost/Le_Muscle_Sympa/public/
```

---

## Compte de test (Jurys)

| Champ | Valeur |
|-------|--------|
| **Email** | jurys.test@gmail.com |
| **Mot de passe** | Azerty123 |

> Vous pouvez également créer votre propre compte via le formulaire d'inscription.

---

## Fonctionnalités

| Fonctionnalité | Description |
|---|---|
| Inscription / Connexion | Création de compte et authentification sécurisée |
| Abonnements | 4 formules : SOLO (20€), SOLO+ (50€), DUO (30€), DUO+ (80€) |
| Programmes sportifs | Questionnaire + génération automatique d'un programme (10€) |
| Espace client | Consultation et téléchargement des programmes achetés |
| Mot de passe oublié | Réinitialisation par email (nécessite config SMTP) |

---

## Structure du projet

```
Le_Muscle_Sympa/
├── app/
│   ├── config.php               → Configuration BDD et URLs
│   ├── db.php                   → Connexion PDO
│   ├── helpers.php              → Fonctions utilitaires (sécurité)
│   └── migrations/
│       ├── database.sql         → Script SQL complet (tables + données)
│       └── 001_create_user_programs_table.sql
├── public/
│   ├── index.php                → Page d'accueil
│   ├── auth.php                 → Inscription / Connexion
│   ├── abonnements.php          → Présentation des formules
│   ├── confirm_plan.php         → Confirmation du choix d'abonnement
│   ├── questionnaire.php        → Formulaire programme sportif
│   ├── pay.php                  → Paiement simulé
│   ├── pay_result.php           → Confirmation + génération programme
│   ├── profile.php              → Espace client
│   ├── forgot.php               → Mot de passe oublié
│   ├── reset.php                → Réinitialisation mot de passe
│   ├── logout.php               → Déconnexion
│   └── api/
│       ├── get-program.php      → API consultation programme (AJAX)
│       └── download-program.php → API téléchargement programme (HTML)
├── vendor/phpmailer/            → Bibliothèque PHPMailer (emails)
└── views/partials/
    ├── header.php               → En-tête commun
    └── footer.php               → Pied de page commun
```

---

## Technologies utilisées

| Catégorie | Technologies |
|---|---|
| Back-end | PHP 8 |
| Base de données | MySQL / MariaDB |
| Front-end | HTML5, CSS3, JavaScript Vanilla |
| Serveur | Apache (XAMPP) |
| Email | PHPMailer + SendGrid SMTP |
| Versioning | Git, GitHub |

---

## Auteur

**SANJIVY Romain** — BTS SIO option SLAM — Session 2026
