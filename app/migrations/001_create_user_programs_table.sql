-- Table pour stocker les programmes personnalisés achetés par les utilisateurs

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
  
  -- Contenu du programme généré
  program_content LONGTEXT,
  
  -- Métadonnées
  purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  price DECIMAL(10, 2),
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id),
  INDEX (purchased_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
