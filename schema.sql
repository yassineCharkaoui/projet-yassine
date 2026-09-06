-- Base de données pour le système de gestion de pharmacie
-- PHP 8 - Architecture MVC - PDO

DROP DATABASE IF EXISTS projetyassine;
CREATE DATABASE projetyassine CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projetyassine;

-- Table Utilisateur
CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    role ENUM('responsable', 'pharmacien', 'client') NOT NULL DEFAULT 'client',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_derniere_connexion DATETIME NULL,
    actif BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- Table Médicament
CREATE TABLE medicament (
    id_medicament INT AUTO_INCREMENT PRIMARY KEY,
    nom_commercial VARCHAR(200) NOT NULL,
    nom_generique VARCHAR(200),
    dci VARCHAR(200) COMMENT 'Dénomination Commune Internationale',
    forme VARCHAR(100) COMMENT 'Comprimé, Sirop, Gélule, Injectable, etc.',
    dosage VARCHAR(100),
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    stock_disponible INT NOT NULL DEFAULT 0,
    stock_minimum INT NOT NULL DEFAULT 10,
    date_peremption DATE,
    laboratoire VARCHAR(200),
    categorie VARCHAR(100) COMMENT 'Antibiotique, Antalgique, Anti-inflammatoire, etc.',
    prescription_obligatoire BOOLEAN DEFAULT FALSE,
    description TEXT,
    contre_indications TEXT,
    effets_secondaires TEXT,
    posologie TEXT,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE,
    INDEX idx_nom_commercial (nom_commercial),
    INDEX idx_stock (stock_disponible),
    INDEX idx_categorie (categorie),
    INDEX idx_peremption (date_peremption)
) ENGINE=InnoDB;

-- Table Ordonnance
CREATE TABLE ordonnance (
    id_ordonnance INT AUTO_INCREMENT PRIMARY KEY,
    numero_ordonnance VARCHAR(50) UNIQUE NOT NULL,
    id_client INT NOT NULL,
    id_pharmacien INT NULL COMMENT 'Pharmacien qui traite l''ordonnance',
    nom_medecin VARCHAR(200) NOT NULL,
    date_prescription DATE NOT NULL,
    date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_validation DATETIME NULL,
    statut ENUM('en_attente', 'validee', 'rejetee', 'traitee', 'renouvelee') NOT NULL DEFAULT 'en_attente',
    fichier_scan VARCHAR(255) COMMENT 'Chemin du fichier scanné',
    commentaire_pharmacien TEXT,
    motif_rejet TEXT,
    montant_total DECIMAL(10, 2) DEFAULT 0,
    date_renouvellement DATE NULL COMMENT 'Date de renouvellement possible',
    ordonnance_renouvelable BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_client) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_pharmacien) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL,
    INDEX idx_client (id_client),
    INDEX idx_pharmacien (id_pharmacien),
    INDEX idx_statut (statut),
    INDEX idx_date_prescription (date_prescription),
    INDEX idx_numero (numero_ordonnance)
) ENGINE=InnoDB;

-- Table de liaison : Ordonnance - Médicament
CREATE TABLE ordonnance_medicament (
    id_ligne INT AUTO_INCREMENT PRIMARY KEY,
    id_ordonnance INT NOT NULL,
    id_medicament INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    posologie_prescrite VARCHAR(255),
    duree_traitement VARCHAR(100),
    prix_unitaire_vente DECIMAL(10, 2) NOT NULL,
    sous_total DECIMAL(10, 2) GENERATED ALWAYS AS (quantite * prix_unitaire_vente) STORED,
    FOREIGN KEY (id_ordonnance) REFERENCES ordonnance(id_ordonnance) ON DELETE CASCADE,
    FOREIGN KEY (id_medicament) REFERENCES medicament(id_medicament) ON DELETE RESTRICT,
    INDEX idx_ordonnance (id_ordonnance),
    INDEX idx_medicament (id_medicament)
) ENGINE=InnoDB;

-- Table Historique des transactions
CREATE TABLE transaction (
    id_transaction INT AUTO_INCREMENT PRIMARY KEY,
    id_ordonnance INT NULL,
    id_client INT NOT NULL,
    id_pharmacien INT NULL,
    montant_total DECIMAL(10, 2) NOT NULL,
    mode_paiement ENUM('especes', 'carte', 'cheque', 'mutuelle') DEFAULT 'especes',
    date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_cours', 'completee', 'annulee') DEFAULT 'completee',
    FOREIGN KEY (id_ordonnance) REFERENCES ordonnance(id_ordonnance) ON DELETE SET NULL,
    FOREIGN KEY (id_client) REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
    FOREIGN KEY (id_pharmacien) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL,
    INDEX idx_client (id_client),
    INDEX idx_pharmacien (id_pharmacien),
    INDEX idx_date (date_transaction)
) ENGINE=InnoDB;

-- Table Interactions médicamenteuses
CREATE TABLE interaction_medicamenteuse (
    id_interaction INT AUTO_INCREMENT PRIMARY KEY,
    id_medicament_1 INT NOT NULL,
    id_medicament_2 INT NOT NULL,
    niveau_gravite ENUM('mineur', 'modere', 'majeur', 'contre_indique') NOT NULL,
    description TEXT NOT NULL,
    recommandation TEXT,
    FOREIGN KEY (id_medicament_1) REFERENCES medicament(id_medicament) ON DELETE CASCADE,
    FOREIGN KEY (id_medicament_2) REFERENCES medicament(id_medicament) ON DELETE CASCADE,
    UNIQUE KEY unique_interaction (id_medicament_1, id_medicament_2),
    INDEX idx_med1 (id_medicament_1),
    INDEX idx_med2 (id_medicament_2),
    CHECK (id_medicament_1 < id_medicament_2)
) ENGINE=InnoDB;

-- Table Alertes interactions détectées
CREATE TABLE alerte_interaction (
    id_alerte INT AUTO_INCREMENT PRIMARY KEY,
    id_ordonnance INT NOT NULL,
    id_interaction INT NOT NULL,
    niveau_alerte ENUM('mineur', 'modere', 'majeur', 'contre_indique') NOT NULL,
    message TEXT NOT NULL,
    date_detection DATETIME DEFAULT CURRENT_TIMESTAMP,
    vue_par_pharmacien BOOLEAN DEFAULT FALSE,
    vue_par_client BOOLEAN DEFAULT FALSE,
    action_prise TEXT,
    FOREIGN KEY (id_ordonnance) REFERENCES ordonnance(id_ordonnance) ON DELETE CASCADE,
    FOREIGN KEY (id_interaction) REFERENCES interaction_medicamenteuse(id_interaction) ON DELETE CASCADE,
    INDEX idx_ordonnance (id_ordonnance),
    INDEX idx_non_vue (vue_par_pharmacien, vue_par_client)
) ENGINE=InnoDB;

-- Table Log des actions
CREATE TABLE log_action (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_concernee VARCHAR(50),
    id_enregistrement INT,
    details TEXT,
    adresse_ip VARCHAR(45),
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    INDEX idx_utilisateur (id_utilisateur),
    INDEX idx_date (date_action),
    INDEX idx_action (action)
) ENGINE=InnoDB;

-- Table Demande de renouvellement
CREATE TABLE demande_renouvellement (
    id_demande INT AUTO_INCREMENT PRIMARY KEY,
    id_ordonnance_origine INT NOT NULL,
    id_client INT NOT NULL,
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'approuvee', 'rejetee') DEFAULT 'en_attente',
    date_traitement DATETIME NULL,
    id_responsable INT NULL,
    commentaire TEXT,
    FOREIGN KEY (id_ordonnance_origine) REFERENCES ordonnance(id_ordonnance) ON DELETE CASCADE,
    FOREIGN KEY (id_client) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_responsable) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL,
    INDEX idx_statut (statut),
    INDEX idx_client (id_client)
) ENGINE=InnoDB;

-- DONNÉES DE TEST (mot de passe: "password123")
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, adresse, role) VALUES
('Admin', 'Responsable', 'responsable@pharmacie.com', '$2y$10$fJiVmxadM4H.DFWnyvE.8OrcMjyA7N3soknLY.owfzBihWwQauu9K', '0612345678', '123 Rue de la Pharmacie, Paris', 'responsable'),
('Dupont', 'Marie', 'marie.dupont@pharmacie.com', '$2y$10$fJiVmxadM4H.DFWnyvE.8OrcMjyA7N3soknLY.owfzBihWwQauu9K', '0623456789', '456 Avenue des Soins, Lyon', 'pharmacien'),
('Martin', 'Jean', 'jean.martin@pharmacie.com', '$2y$10$fJiVmxadM4H.DFWnyvE.8OrcMjyA7N3soknLY.owfzBihWwQauu9K', '0634567890', '789 Boulevard Santé, Marseille', 'pharmacien'),
('Dubois', 'Sophie', 'sophie.dubois@email.com', '$2y$10$fJiVmxadM4H.DFWnyvE.8OrcMjyA7N3soknLY.owfzBihWwQauu9K', '0645678901', '12 Rue des Clients, Nice', 'client'),
('Bernard', 'Pierre', 'pierre.bernard@email.com', '$2y$10$fJiVmxadM4H.DFWnyvE.8OrcMjyA7N3soknLY.owfzBihWwQauu9K', '0656789012', '34 Avenue Principale, Toulouse', 'client');

-- Insertion médicaments
INSERT INTO medicament (nom_commercial, nom_generique, dci, forme, dosage, prix_unitaire, stock_disponible, stock_minimum, date_peremption, laboratoire, categorie, prescription_obligatoire, description, contre_indications, effets_secondaires, posologie) VALUES
('Doliprane', 'Paracétamol', 'Paracétamol', 'Comprimé', '1000mg', 3.50, 250, 50, '2026-12-31', 'Sanofi', 'Antalgique', FALSE, 'Traitement symptomatique de la douleur et de la fièvre', 'Allergie au paracétamol, insuffisance hépatique sévère', 'Rares: réactions allergiques cutanées', '1 comprimé 3 fois par jour, maximum 3g/jour'),
('Amoxicilline', 'Amoxicilline', 'Amoxicilline', 'Gélule', '500mg', 6.80, 180, 40, '2026-08-30', 'Arrow', 'Antibiotique', TRUE, 'Traitement des infections bactériennes', 'Allergie aux pénicillines, mononucléose', 'Diarrhée, nausées, éruptions cutanées', '1 gélule 3 fois par jour pendant 7 jours'),
('Ventoline', 'Salbutamol', 'Salbutamol', 'Inhalateur', '100µg/dose', 5.20, 95, 20, '2027-03-15', 'GSK', 'Bronchodilatateur', TRUE, 'Traitement de l''asthme et bronchospasme', 'Hypersensibilité au salbutamol', 'Tremblements, palpitations, céphalées', '1 à 2 bouffées si besoin, max 8/jour'),
('Ibuprofène', 'Ibuprofène', 'Ibuprofène', 'Comprimé', '400mg', 3.20, 5, 50, '2026-10-31', 'Mylan', 'Anti-inflammatoire', FALSE, 'Douleur et inflammation', 'Ulcère gastrique actif, grossesse 3e trimestre', 'Troubles digestifs, céphalées', '1 comprimé 3 fois/jour max pendant les repas'),
('Xanax', 'Alprazolam', 'Alprazolam', 'Comprimé', '0.25mg', 8.90, 80, 15, '2026-12-15', 'Pfizer', 'Anxiolytique', TRUE, 'Traitement de l''anxiété', 'Insuffisance respiratoire sévère', 'Somnolence, vertiges, dépendance', '0.25 à 0.5mg 3 fois/jour');

-- Interactions médicamenteuses
INSERT INTO interaction_medicamenteuse (id_medicament_1, id_medicament_2, niveau_gravite, description, recommandation) VALUES
(1, 4, 'mineur', 'Toxicité hépatique potentiellement augmentée', 'Surveillance de la fonction hépatique si utilisation prolongée');

-- Vues
CREATE VIEW v_stock_critique AS
SELECT m.id_medicament, m.nom_commercial, m.forme, m.dosage, m.stock_disponible, m.stock_minimum,
    CASE WHEN m.stock_disponible = 0 THEN 'RUPTURE'
         WHEN m.stock_disponible <= m.stock_minimum THEN 'CRITIQUE'
         ELSE 'NORMAL' END AS etat_stock
FROM medicament m WHERE m.actif = TRUE AND m.stock_disponible <= m.stock_minimum;

-- Ajout de plus de médicaments
INSERT INTO medicament (nom_commercial, nom_generique, dci, forme, dosage, prix_unitaire, stock_disponible, stock_minimum, date_peremption, laboratoire, categorie, prescription_obligatoire, description) VALUES
('Aspirine', 'Acide Acétylsalicylique', 'Acide Acétylsalicylique', 'Comprimé', '500mg', 2.90, 200, 50, '2026-11-30', 'Bayer', 'Antalgique', FALSE, 'Douleur et fièvre'),
('Nexium', 'Ésoméprazole', 'Ésoméprazole', 'Gélule', '20mg', 12.50, 120, 30, '2027-01-15', 'AstraZeneca', 'Anti-acide', FALSE, 'Reflux gastro-oesophagien'),
('Levothyrox', 'Lévothyroxine', 'Lévothyroxine', 'Comprimé', '75µg', 7.80, 150, 40, '2027-06-30', 'Merck', 'Hormone thyroïdienne', TRUE, 'Hypothyroïdie'),
('Efferalgan', 'Paracétamol', 'Paracétamol', 'Comprimé effervescent', '1000mg', 4.20, 180, 50, '2026-09-30', 'BMS', 'Antalgique', FALSE, 'Douleur et fièvre'),
('Spasfon', 'Phloroglucinol', 'Phloroglucinol', 'Comprimé', '80mg', 5.60, 90, 30, '2026-12-31', 'Teva', 'Antispasmodique', FALSE, 'Douleurs abdominales'),
('Mopral', 'Oméprazole', 'Oméprazole', 'Gélule', '20mg', 9.30, 110, 25, '2027-04-30', 'AstraZeneca', 'Anti-acide', FALSE, 'Ulcère gastrique'),
('Zithromax', 'Azithromycine', 'Azithromycine', 'Comprimé', '250mg', 15.40, 70, 20, '2026-11-15', 'Pfizer', 'Antibiotique', TRUE, 'Infections respiratoires'),
('Lexomil', 'Bromazépam', 'Bromazépam', 'Comprimé', '6mg', 7.90, 60, 15, '2027-02-28', 'Roche', 'Anxiolytique', TRUE, 'Anxiété'),
('Prednisolone', 'Prednisolone', 'Prednisolone', 'Comprimé', '20mg', 8.50, 85, 20, '2027-03-31', 'Mylan', 'Corticoïde', TRUE, 'Inflammation'),
('Seretide', 'Fluticasone/Salmétérol', 'Fluticasone/Salmétérol', 'Inhalateur', '250µg/25µg', 35.80, 45, 15, '2027-05-31', 'GSK', 'Antiasthmatique', TRUE, 'Asthme persistant');

-- Ajout d'interactions médicamenteuses
INSERT INTO interaction_medicamenteuse (id_medicament_1, id_medicament_2, niveau_gravite, description, recommandation) VALUES
(2, 12, 'majeur', 'Augmentation du risque d''infection par diminution de l''efficacité antibiotique', 'Éviter l''association, espacer les prises de 2h minimum'),
(5, 13, 'majeur', 'Risque de dépression respiratoire et de surdosage', 'Association déconseillée, surveillance étroite si nécessaire'),
(6, 8, 'mineur', 'Diminution possible de l''absorption de l''ésoméprazole', 'Prendre les médicaments à des moments différents'),
(4, 6, 'mineur', 'Effet anti-inflammatoire potentialisé, risque gastrique augmenté', 'Surveillance des effets indésirables gastro-intestinaux'),
(2, 7, 'modere', 'Modification de l''absorption de la lévothyroxine', 'Espacer les prises d''au moins 2 heures'),
(11, 14, 'majeur', 'Effet sédatif majoré, risque de somnolence excessive', 'Prudence lors de la conduite, éviter alcool'),
(1, 6, 'mineur', 'Toxicité hépatique si doses élevées prolongées', 'Ne pas dépasser 3g de paracétamol/jour'),
(9, 14, 'modere', 'Augmentation du risque d''effets indésirables du bromazépam', 'Surveillance clinique, adaptation posologique si nécessaire');

-- Création d'ordonnances de test
INSERT INTO ordonnance (numero_ordonnance, id_client, id_pharmacien, nom_medecin, date_prescription, statut, montant_total, ordonnance_renouvelable) VALUES
('ORD-2026-001', 4, NULL, 'Dr. Lefevre', '2026-08-28', 'en_attente', 13.30, FALSE),
('ORD-2026-002', 5, 2, 'Dr. Rousseau', '2026-08-25', 'validee', 21.60, TRUE),
('ORD-2026-003', 4, 2, 'Dr. Moreau', '2026-08-20', 'traitee', 18.70, FALSE),
('ORD-2026-004', 5, NULL, 'Dr. Simon', '2026-08-30', 'en_attente', 27.50, TRUE),
('ORD-2026-005', 4, 3, 'Dr. Laurent', '2026-08-15', 'traitee', 35.40, FALSE);

-- Détails des ordonnances
INSERT INTO ordonnance_medicament (id_ordonnance, id_medicament, quantite, posologie_prescrite, duree_traitement, prix_unitaire_vente) VALUES
(1, 1, 2, '1 comprimé 3 fois par jour', '7 jours', 3.50),
(1, 4, 2, '1 comprimé si douleur', '10 jours', 3.20),
(2, 2, 1, '1 gélule 3 fois par jour', '7 jours', 6.80),
(2, 3, 1, '2 bouffées matin et soir', '30 jours', 5.20),
(3, 7, 2, '1 comprimé effervescent matin et soir', '5 jours', 4.20),
(3, 8, 1, '1 comprimé si besoin', '30 jours', 5.60),
(4, 9, 1, '1 gélule le matin', '30 jours', 9.30),
(4, 10, 2, '1 gélule le soir', '5 jours', 7.80),
(5, 12, 1, '1 comprimé par jour', '5 jours', 15.40),
(5, 15, 1, '2 bouffées matin et soir', '30 jours', 35.80);

-- Transactions (pour les ordonnances traitées)
INSERT INTO transaction (id_ordonnance, id_client, id_pharmacien, montant_total, mode_paiement, date_transaction) VALUES
(3, 4, 2, 18.70, 'carte_bancaire', '2026-08-21 10:30:00'),
(5, 4, 3, 35.40, 'especes', '2026-08-16 14:15:00');

-- Demandes de renouvellement
INSERT INTO demande_renouvellement (id_ordonnance_origine, id_client, statut, date_demande) VALUES
(2, 5, 'en_attente', '2026-09-01 09:00:00'),
(3, 4, 'approuvee', '2026-08-29 11:30:00');

-- Alertes d'interactions
INSERT INTO alerte_interaction (id_ordonnance, id_interaction, niveau_alerte, message) VALUES
(1, 1, 'mineur', 'Attention: association paracétamol + ibuprofène - surveillance hépatique recommandée');

-- Vues supplémentaires
CREATE VIEW vue_ordonnances_completes AS
SELECT 
    o.id_ordonnance,
    o.numero_ordonnance,
    o.statut,
    o.date_prescription,
    o.montant_total,
    CONCAT(u_client.prenom, ' ', u_client.nom) AS nom_client,
    u_client.email AS email_client,
    CONCAT(u_pharm.prenom, ' ', u_pharm.nom) AS nom_pharmacien,
    o.nom_medecin,
    COUNT(om.id_medicament) AS nombre_medicaments
FROM ordonnance o
LEFT JOIN utilisateur u_client ON o.id_client = u_client.id_utilisateur
LEFT JOIN utilisateur u_pharm ON o.id_pharmacien = u_pharm.id_utilisateur
LEFT JOIN ordonnance_medicament om ON o.id_ordonnance = om.id_ordonnance
GROUP BY o.id_ordonnance;

-- Trigger pour détecter les interactions
DELIMITER //
CREATE TRIGGER after_insert_ordonnance_medicament
AFTER INSERT ON ordonnance_medicament
FOR EACH ROW
BEGIN
    -- Vérifier les interactions avec les autres médicaments de la même ordonnance
    INSERT INTO alerte_interaction (id_ordonnance, id_interaction, niveau_alerte, message)
    SELECT 
        NEW.id_ordonnance,
        i.id_interaction,
        i.niveau_gravite,
        CONCAT('Interaction détectée: ', m1.nom_commercial, ' + ', m2.nom_commercial, ' - ', i.description)
    FROM ordonnance_medicament om
    JOIN interaction_medicamenteuse i ON 
        (i.id_medicament_1 = NEW.id_medicament AND i.id_medicament_2 = om.id_medicament)
        OR (i.id_medicament_2 = NEW.id_medicament AND i.id_medicament_1 = om.id_medicament)
    JOIN medicament m1 ON i.id_medicament_1 = m1.id_medicament
    JOIN medicament m2 ON i.id_medicament_2 = m2.id_medicament
    WHERE om.id_ordonnance = NEW.id_ordonnance
    AND om.id_medicament != NEW.id_medicament;
END//
DELIMITER ;

-- Trigger pour mettre à jour le stock après traitement
DELIMITER //
CREATE TRIGGER after_insert_transaction
AFTER INSERT ON transaction
FOR EACH ROW
BEGIN
    -- Débiter le stock des médicaments de l'ordonnance
    UPDATE medicament m
    JOIN ordonnance_medicament om ON m.id_medicament = om.id_medicament
    SET m.stock_disponible = m.stock_disponible - om.quantite
    WHERE om.id_ordonnance = NEW.id_ordonnance;
    
    -- Mettre à jour le statut de l'ordonnance
    UPDATE ordonnance
    SET statut = 'traitee'
    WHERE id_ordonnance = NEW.id_ordonnance;
END//
DELIMITER ;

-- Procédure stockée pour recherche avancée de médicaments
DELIMITER //
CREATE PROCEDURE recherche_medicaments(
    IN p_nom VARCHAR(200),
    IN p_categorie VARCHAR(100),
    IN p_prescription BOOLEAN,
    IN p_stock_min INT
)
BEGIN
    SELECT * FROM medicament
    WHERE actif = TRUE
    AND (p_nom IS NULL OR nom_commercial LIKE CONCAT('%', p_nom, '%') OR nom_generique LIKE CONCAT('%', p_nom, '%'))
    AND (p_categorie IS NULL OR categorie = p_categorie)
    AND (p_prescription IS NULL OR prescription_obligatoire = p_prescription)
    AND (p_stock_min IS NULL OR stock_disponible >= p_stock_min)
    ORDER BY nom_commercial;
END//
DELIMITER ;

-- Procédure stockée pour obtenir les statistiques
DELIMITER //
CREATE PROCEDURE get_statistiques_periode(
    IN p_date_debut DATE,
    IN p_date_fin DATE
)
BEGIN
    SELECT 
        COUNT(DISTINCT t.id_transaction) AS nombre_transactions,
        SUM(t.montant_total) AS chiffre_affaires,
        AVG(t.montant_total) AS montant_moyen,
        COUNT(DISTINCT o.id_client) AS nombre_clients,
        COUNT(DISTINCT om.id_medicament) AS nombre_medicaments_vendus,
        SUM(om.quantite) AS quantite_totale
    FROM transaction t
    JOIN ordonnance o ON t.id_ordonnance = o.id_ordonnance
    JOIN ordonnance_medicament om ON o.id_ordonnance = om.id_ordonnance
    WHERE DATE(t.date_transaction) BETWEEN p_date_debut AND p_date_fin;
END//
DELIMITER ;
