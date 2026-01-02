-- Création de la base de données
CREATE DATABASE IF NOT EXISTS examen_2016;
USE examen_2016;

-- Table Article
CREATE TABLE Article (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    qteStock INT NOT NULL DEFAULT 0
);

-- Table Vente
CREATE TABLE Vente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) UNIQUE NOT NULL,
    date DATE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    adresse TEXT NOT NULL
);

-- Table VenteArticle
CREATE TABLE VenteArticle (
    idArt INT,
    idVen INT,
    qteVendue INT NOT NULL,
    PRIMARY KEY (idArt, idVen),
    FOREIGN KEY (idArt) REFERENCES Article(id) ON DELETE CASCADE,
    FOREIGN KEY (idVen) REFERENCES Vente(id) ON DELETE CASCADE
);

-- Insertion de 5 articles (à exécuter via PhpMyAdmin)
INSERT INTO Article (code, nom, description, qteStock) VALUES
('ART001', 'Ordinateur Portable', 'PC Portable 15 pouces, 8GB RAM, 512GB SSD', 10),
('ART002', 'Souris Sans Fil', 'Souris ergonomique sans fil, 1600 DPI', 25),
('ART003', 'Clavier Mécanique', 'Clavier gaming RGB switches bleus', 8),
('ART004', 'Écran 24 pouces', 'Écran Full HD 24 pouces, 75Hz', 3),
('ART005', 'Casque Audio', 'Casque gaming avec micro détachable', 0);