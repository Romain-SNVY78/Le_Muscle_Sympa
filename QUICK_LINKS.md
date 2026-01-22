# 📋 Liens de test rapides

## 🧪 Tester le flux complet

### 1. Remplir le questionnaire
- **Renforcement**: http://localhost/Le_Muscle_Sympa/public/questionnaire.php?type=renforcement
- **Endurance**: http://localhost/Le_Muscle_Sympa/public/questionnaire.php?type=endurance
- **Esthétique**: http://localhost/Le_Muscle_Sympa/public/questionnaire.php?type=esthetique
- **Entretien**: http://localhost/Le_Muscle_Sympa/public/questionnaire.php?type=entretien

### 2. Voir ses programmes
- **Profil utilisateur**: http://localhost/Le_Muscle_Sympa/public/profile.php
- **Onglet programmes**: http://localhost/Le_Muscle_Sympa/public/profile.php?tab=programs

### 3. Accueil
- **Page d'accueil**: http://localhost/Le_Muscle_Sympa/public/index.php

---

## 🔍 Vérifier la BDD

Utilisez phpMyAdmin:
- **Table**: `user_programs`
- **Colonnes**: 22 colonnes (user_id, program_type, program_name, données du questionnaire, program_content, etc)

---

## 📊 Base de données

Vous pouvez vérifier les enregistrements avec:

```sql
-- Voir tous les programmes achetés
SELECT * FROM user_programs ORDER BY purchased_at DESC;

-- Voir les programmes d'un utilisateur
SELECT * FROM user_programs WHERE user_id = 1;

-- Compter les achats par type
SELECT program_type, COUNT(*) as total 
FROM user_programs 
GROUP BY program_type;
```

---

## 🐛 Troubleshooting

### Erreur: "Table user_programs doesn't exist"
→ Exécutez la migration SQL depuis `INSTALLATION.md`

### Le programme n'apparaît pas après achat
→ Vérifiez les logs PHP
→ Vérifiez que `user_id` n'est pas NULL

### API n'ouvre pas le programme
→ Vérifiez que le navigateur n'a pas bloqué les popups
→ Ouvrez la console (F12) pour voir les erreurs

### Download ne marche pas
→ Assurez-vous que `/public/api/download-program.php` est accessible

---

## 📌 Note importante

**Tout fonctionne sans EmailJS!** ✅

Les programmes sont maintenant:
1. Stockés dans la BDD
2. Accessibles dans le compte utilisateur
3. Consultables directement
4. Téléchargeables

C'est beaucoup mieux que d'envoyer par email! 📧❌ → 💾✅
