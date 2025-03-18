-- Création de la table des utilisateurs
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajout d'un utilisateur administrateur par défaut (mot de passe: admin123)
-- Le mot de passe est haché avec password_hash() en PHP, mais pour l'initialisation,
-- nous utilisons un hachage généré à l'avance
INSERT INTO `users` (`username`, `email`, `password`, `name`, `created_at`)
VALUES
('admin', 'admin@example.com', '$2y$10$3F2lGdRnCLD61JhVR3X1R.hGSA1KpkF/gggJMIjOEZSv5ydIBb4fG', 'Administrateur', NOW());