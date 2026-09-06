<?php
/**
 * Modèle Transaction - Gestion des transactions (historique des ventes)
 */

class Transaction
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }
    
    /**
     * Créer une nouvelle transaction
     */
    public function create(array $data): ?int
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO transaction (id_ordonnance, id_client, id_pharmacien, 
                    montant_total, mode_paiement, statut)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['id_ordonnance'],
                $data['id_client'],
                $data['id_pharmacien'],
                $data['montant_total'],
                $data['mode_paiement'] ?? 'especes',
                $data['statut'] ?? 'completee'
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur create transaction: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer une transaction par ID
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, o.numero_ordonnance,
                    c.nom as client_nom, c.prenom as client_prenom,
                    COALESCE(p.nom, 'Officine') as pharmacien_nom, 
                    COALESCE(p.prenom, 'Pharmacie') as pharmacien_prenom
                FROM transaction t
                LEFT JOIN ordonnance o ON t.id_ordonnance = o.id_ordonnance
                JOIN utilisateur c ON t.id_client = c.id_utilisateur
                LEFT JOIN utilisateur p ON t.id_pharmacien = p.id_utilisateur
                WHERE t.id_transaction = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Erreur getById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer toutes les transactions
     */
    public function getAll(?string $statut = null): array
    {
        try {
            if ($statut) {
                $stmt = $this->pdo->prepare("
                    SELECT t.*, o.numero_ordonnance,
                        c.nom as client_nom, c.prenom as client_prenom,
                        COALESCE(p.nom, 'Officine') as pharmacien_nom, 
                        COALESCE(p.prenom, 'Pharmacie') as pharmacien_prenom
                    FROM transaction t
                    LEFT JOIN ordonnance o ON t.id_ordonnance = o.id_ordonnance
                    JOIN utilisateur c ON t.id_client = c.id_utilisateur
                    LEFT JOIN utilisateur p ON t.id_pharmacien = p.id_utilisateur
                    WHERE t.statut = ?
                    ORDER BY t.date_transaction DESC
                ");
                $stmt->execute([$statut]);
            } else {
                $stmt = $this->pdo->query("
                    SELECT t.*, o.numero_ordonnance,
                        c.nom as client_nom, c.prenom as client_prenom,
                        COALESCE(p.nom, 'Officine') as pharmacien_nom, 
                        COALESCE(p.prenom, 'Pharmacie') as pharmacien_prenom
                    FROM transaction t
                    LEFT JOIN ordonnance o ON t.id_ordonnance = o.id_ordonnance
                    JOIN utilisateur c ON t.id_client = c.id_utilisateur
                    LEFT JOIN utilisateur p ON t.id_pharmacien = p.id_utilisateur
                    ORDER BY t.date_transaction DESC
                ");
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer l'historique d'un client
     */
    public function getByClient(int $clientId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, 
                    o.numero_ordonnance,
                    COALESCE(p.nom, 'Officine') as nom, 
                    COALESCE(p.prenom, 'Pharmacie') as prenom
                FROM transaction t
                LEFT JOIN ordonnance o ON t.id_ordonnance = o.id_ordonnance
                LEFT JOIN utilisateur p ON t.id_pharmacien = p.id_utilisateur
                WHERE t.id_client = ?
                ORDER BY t.date_transaction DESC
            ");
            $stmt->execute([$clientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByClient: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer l'historique d'un pharmacien
     */
    public function getByPharmacien(int $pharmacienId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, 
                    o.numero_ordonnance,
                    o.statut as statut_ordonnance,
                    c.nom as client_nom, 
                    c.prenom as client_prenom,
                    c.email as client_email,
                    COUNT(td.id_medicament) as nb_medicaments
                FROM transaction t
                LEFT JOIN ordonnance o ON t.id_ordonnance = o.id_ordonnance
                LEFT JOIN transaction_detail td ON t.id_transaction = td.id_transaction
                JOIN utilisateur c ON t.id_client = c.id_utilisateur
                WHERE t.id_pharmacien = ?
                GROUP BY t.id_transaction
                ORDER BY t.date_transaction DESC
            ");
            $stmt->execute([$pharmacienId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByPharmacien: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les statistiques des transactions
     */
    public function getStatistics(?string $periode = null): array
    {
        try {
            $where = "WHERE statut = 'completee'";
            
            if ($periode === 'jour') {
                $where .= " AND DATE(date_transaction) = CURDATE()";
            } elseif ($periode === 'semaine') {
                $where .= " AND YEARWEEK(date_transaction) = YEARWEEK(NOW())";
            } elseif ($periode === 'mois') {
                $where .= " AND YEAR(date_transaction) = YEAR(NOW()) AND MONTH(date_transaction) = MONTH(NOW())";
            } elseif ($periode === 'annee') {
                $where .= " AND YEAR(date_transaction) = YEAR(NOW())";
            }
            
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_transactions,
                    SUM(montant_total) as montant_total,
                    AVG(montant_total) as montant_moyen,
                    COUNT(DISTINCT id_client) as nb_clients,
                    COUNT(DISTINCT id_pharmacien) as nb_pharmaciens
                FROM transaction $where
            ");
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur getStatistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les transactions par mode de paiement
     */
    public function getByModePaiement(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT mode_paiement, 
                    COUNT(*) as nombre,
                    SUM(montant_total) as total
                FROM transaction 
                WHERE statut = 'completee'
                GROUP BY mode_paiement
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByModePaiement: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les détails d'une transaction (médicaments achetés)
     */
    public function getTransactionDetails(int $transactionId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    td.*,
                    m.nom_commercial,
                    m.nom_generique,
                    m.forme,
                    m.dosage,
                    m.image
                FROM transaction_detail td
                JOIN medicament m ON td.id_medicament = m.id_medicament
                WHERE td.id_transaction = ?
                ORDER BY td.date_ajout
            ");
            $stmt->execute([$transactionId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getTransactionDetails: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Annuler une transaction
     */
    public function annuler(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE transaction SET statut = 'annulee' WHERE id_transaction = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur annuler: " . $e->getMessage());
            return false;
        }
    }
}
?>
