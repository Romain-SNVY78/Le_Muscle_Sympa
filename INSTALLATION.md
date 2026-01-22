# 🚀 Installation - Système de Programmes Personnalisés

## Étape 1: Exécuter la migration SQL

Connectez-vous à votre base de données MySQL et exécutez:

```sql
CREATE TABLE IF NOT EXISTS user_programs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  program_type VARCHAR(50) NOT NULL,
  program_name VARCHAR(255) NOT NULL,
  
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
  
  program_content LONGTEXT,
  
  purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  price DECIMAL(10, 2),
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id),
  INDEX (purchased_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Étape 2: Vérifier les fichiers

Tous les fichiers nécessaires sont déjà créés:

✅ `public/profile.php` - Compte utilisateur avec programmes
✅ `public/questionnaire.php` - Formulaire modifié
✅ `public/pay.php` - Paiement (modifié)
✅ `public/pay_result.php` - Confirmation (recréé)
✅ `public/api/get-program.php` - API consultation
✅ `public/api/download-program.php` - API téléchargement

## Étape 3: Tester le flux

1. Accédez à: `http://localhost/Le_Muscle_Sympa/public/questionnaire.php?type=renforcement`
2. Remplissez le formulaire
3. Cliquez "Recevoir mon programme"
4. Complétez le paiement (simulation)
5. Vous serez redirigé vers la confirmation
6. Allez dans votre compte `profile.php?tab=programs`
7. Vous voyez votre programme! ✅

## Étape 4: Personnaliser

Modifiez la fonction `generateProgram()` dans `public/pay_result.php` pour générer vos propres programmes (ou appelez une API IA).

---

## ✅ Tout fonctionne sans dépendances externes!

- ✅ Aucune API EmailJS
- ✅ Stockage 100% interne
- ✅ Aucun crédit nécessaire
- ✅ Contrôle complet
- ✅ Scalable et personnalisable

Amusez-vous! 🎉
