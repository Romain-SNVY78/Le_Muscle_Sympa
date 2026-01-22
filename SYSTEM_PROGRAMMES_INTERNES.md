# 📊 Système de Programmes Personnalisés - Le Muscle Sympa

## 🎯 Vue d'ensemble

Les utilisateurs remplissent un questionnaire, paient 10€, et trouvent leur programme dans leur compte.

**Aucune dépendance externe** - tout est stocké en interne à la base de données.

---

## 🔄 Flux utilisateur

```
1. Utilisateur remplit questionnaire.php
        ↓
2. Clique sur "Recevoir mon programme"
        ↓
3. Données du questionnaire → Session PHP
        ↓
4. Redirection vers pay.php (paiement simulé)
        ↓
5. Confirmation pay_result.php
        ↓
6. Enregistrement du programme en BDD
        ↓
7. Visible dans profile.php → onglet "Mes programmes"
```

---

## 📁 Fichiers modifiés/créés

### 1. **Database**
- **[app/migrations/001_create_user_programs_table.sql](app/migrations/001_create_user_programs_table.sql)** (nouvelle table)
  - Table `user_programs` pour stocker les programmes achetés

### 2. **Frontend**

- **[public/questionnaire.php](public/questionnaire.php)** (modifié)
  - Collecte les données du formulaire
  - Les passe à `pay.php` via input hidden

- **[public/pay.php](public/pay.php)** (modifié)
  - Récupère `program_data` depuis POST
  - Stocke en `$_SESSION['program_data']`

- **[public/pay_result.php](public/pay_result.php)** (recréé)
  - Génère le contenu du programme
  - Enregistre en base de données
  - Affiche confirmation

- **[public/profile.php](public/profile.php)** (recréé)
  - 2 onglets: "Vue d'ensemble" + "Mes programmes"
  - Liste les programmes achetés
  - Boutons "Consulter" et "Télécharger"

### 3. **API (backend)**

- **[public/api/get-program.php](public/api/get-program.php)** (nouveau)
  - Récupère le contenu du programme en AJAX
  - Affiche dans une modal

- **[public/api/download-program.php](public/api/download-program.php)** (nouveau)
  - Génère un fichier HTML
  - Envoie en téléchargement

### 4. **JavaScript**

- **[public/assets/js/questionnaire-updated.js](public/assets/js/questionnaire-updated.js)** (nouveau)
  - Version améliorée du questionnaire
  - Validation + stockage en session

---

## 📋 Configuration requise

### 1. Créer la table en BDD

Exécutez le SQL depuis `app/migrations/001_create_user_programs_table.sql`:

```sql
CREATE TABLE IF NOT EXISTS user_programs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  program_type VARCHAR(50) NOT NULL,
  program_name VARCHAR(255) NOT NULL,
  
  -- Données du questionnaire
  first_name VARCHAR(100),
  email VARCHAR(100),
  age INT,
  poids DECIMAL(5, 1),
  taille INT,
  objectif VARCHAR(50),
  experience VARCHAR(50),
  frequence INT,
  jours VARCHAR(255),
  equip VARCHAR(100),
  contraintes TEXT,
  duree INT,
  preferences TEXT,
  
  -- Contenu généré
  program_content LONGTEXT,
  
  -- Métadonnées
  purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  price DECIMAL(10, 2),
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id),
  INDEX (purchased_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Vérifier les permissions dossier

Le dossier `public/api/` doit exister et être accessible. Sinon, créez-le:

```bash
mkdir -p c:\xampp\htdocs\Le_Muscle_Sympa\public\api
```

---

## 🧪 Test du flux

1. **Créez un compte** sur votre site
2. **Allez à** `questionnaire.php?type=renforcement`
3. **Remplissez le formulaire** avec des données valides
4. **Cliquez** "Recevoir mon programme"
5. **Complétez le paiement** (simulation)
6. **Vous voyez** "Paiement validé ✔"
7. **Allez à** `profile.php?tab=programs`
8. **Vous voyez votre programme!** ✅

---

## 📦 Structure du programme généré

Le programme est stocké en HTML dans la colonne `program_content`:

```html
<h2>Programme personnalisé - Renforcement musculaire</h2>
<p><strong>Client:</strong> Alex</p>
<p><strong>Objectif:</strong> Prise de masse</p>
<p><strong>Niveau:</strong> Intermédiaire</p>
<p><strong>Fréquence:</strong> 4 séances/semaine</p>
<p><strong>Équipements:</strong> Salle complète</p>
... contenu détaillé ...
```

### Amélioration future:
- Générer via **OpenAI API** pour des programmes vraiment personnalisés
- Ajouter **PDF** au lieu de HTML
- Intégrer **images d'exercices**
- Ajouter **suivi de progression** (notes, poids levé, etc)

---

## 🔧 Modification du contenu du programme

Pour personnaliser le contenu généré, modifiez la fonction `generateProgram()` dans [public/pay_result.php](public/pay_result.php#L76):

```php
function generateProgram($type, $data) {
  // $type: 'renforcement', 'endurance', etc
  // $data: données du questionnaire (prenom, age, poids, etc)
  
  $html = "<h2>Mon programme personnalisé</h2>";
  // Générer le contenu...
  return $html;
}
```

---

## ✅ Checklist

- [ ] Table `user_programs` créée en BDD
- [ ] Dossier `public/api/` existe
- [ ] Fichiers questionnaire.php, pay.php, pay_result.php, profile.php modifiés
- [ ] Files API `get-program.php`, `download-program.php` créés
- [ ] Site fonctionne sans erreurs
- [ ] Flux testé de bout en bout

---

## 🐛 Dépannage

### "user_programs table doesn't exist"
→ Exécutez le SQL de migration

### "Mes programmes" affiche 0
→ Vérifiez que le paiement a été complété
→ Vérifiez `user_id` en session
→ Regardez les logs PHP pour erreurs

### Programme n'apparaît pas après paiement
→ Vérifiez que les `$_SESSION['program_data']` arrivent à pay_result.php
→ Ajoutez `error_log()` pour debug

### Téléchargement ne marche pas
→ Vérifiez que `download-program.php` est accessible
→ Vérifiez les permissions fichiers

---

## 📊 Données stockées par programme

Pour chaque programme acheté, on stocke:

| Champ | Type | Exemple |
|-------|------|---------|
| `user_id` | INT | 42 |
| `program_type` | VARCHAR | 'renforcement' |
| `program_name` | VARCHAR | 'Renforcement musculaire' |
| `first_name` | VARCHAR | 'Alex' |
| `email` | VARCHAR | 'alex@example.com' |
| `age` | INT | 28 |
| `poids` | DECIMAL | 72.5 |
| `taille` | INT | 178 |
| `objectif` | VARCHAR | 'prise_masse' |
| `experience` | VARCHAR | 'intermediaire' |
| `frequence` | INT | 4 |
| `jours` | VARCHAR | 'Lun, Mar, Jeu, Ven' |
| `equip` | VARCHAR | 'salle_complete' |
| `contraintes` | TEXT | 'Épaule fragile' |
| `duree` | INT | 60 |
| `preferences` | TEXT | 'Full-body, léger HIIT' |
| `program_content` | LONGTEXT | `<h2>Program...</h2>` |
| `purchased_at` | TIMESTAMP | 2026-01-20 14:30:00 |
| `price` | DECIMAL | 10.00 |

---

## 🚀 Améliorations futures

1. **Génération IA**: Appeler OpenAI pour générer les exercices
2. **PDF**: Utiliser mPDF pour télécharger en PDF
3. **Email**: Envoyer par email si souhaité
4. **Historique**: Voir les anciens paiements
5. **Modifications**: Permettre de modifier le programme
6. **Partage**: Partager son programme avec un coach
7. **Progression**: Tracker la progression (poids, reps, dates)
8. **Notes**: Ajouter des notes personnelles

Tout est **100% interne** - pas de dépendances externes! 🎉
