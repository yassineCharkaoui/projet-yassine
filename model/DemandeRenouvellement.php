<?php
/**
 * Modèle DemandeRenouvellement - Gestion des demandes de renouvellement d'ordonnances
 */

class DemandeRenouvellement
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }
    
    /**
     * Créer une nouvelle demande de renouvellement
     */
    public function create(array $data): ?int
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO demande_renouvellement (id_ordonnance_origine, id_client, commentaire)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $data['id_ordonnance_origine'],
                $data['id_client'],
                $data['commentaire'] ?? null
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur create demande: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer une demande par ID
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.*, 
                    o.numero_ordonnance, o.nom_medecin, o.date_prescription,
                    c.nom as client_nom, c.prenom as client_prenom, c.email as client_email,
                    r.nom as responsable_nom, r.prenom as responsable_prenom
                FROM demande_renouvellement d
                JOIN ordonnance o ON d.id_ordonnance_origine = o.id_ordonnance
                JOIN utilisateur c ON d.id_client = c.id_utilisateur
                LEFT JOIN utilisateur r ON d.id_responsable = r.id_utilisateur
                WHERE d.id_demande = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Erreur getById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer toutes les demandes
     */
    public function getAll(?string $statut = null): array
    {
        try {
            if ($statut) {
                $stmt = $this->pdo->prepare("
                    SELECT d.*, 
                        o.numero_ordonnance,
                        c.nom as client_nom, c.prenom as client_prenom
                    FROM demande_renouvellement d
                    JOIN ordonnance o ON d.id_ordonnance_origine = o.id_ordonnance
                    JOIN utilisateur c ON d.id_client = c.id_utilisateur
                    WHERE d.statut = ?
                    ORDER BY d.date_demande DESC
                ");
                $stmt->execute([$statut]);
            } else {
                $stmt = $this->pdo->query("
                    SELECT d.*, 
                        o.numero_ordonnance,
                        c.nom as client_nom, c.prenom as client_prenom
                    FROM demande_renouvellement d
                    JOIN ordonnance o ON d.id_ordonnance_origine = o.id_ordonnance
                    JOIN utilisateur c ON d.id_client = c.id_utilisateur
                    ORDER BY d.date_demande DESC
                ");
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les demandes d'un client
     */
    public function getByClient(int $clientId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.*, o.numero_ordonnance, o.nom_medecin
                FROM demande_renouvellement d
                JOIN ordonnance o ON d.id_ordonnance_origine = o.id_ordonnance
                WHERE d.id_client = ?
                ORDER BY d.date_demande DESC
            ");
            $stmt->execute([$clientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByClient: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Approuver une demande de renouvellement
     */
    public function approuver(int $id, int $responsableId, ?string $commentaire = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE demande_renouvellement 
                SET statut = 'approuvee', id_responsable = ?, commentaire = ?, date_traitement = NOW()
                WHERE id_demande = ?
            ");
            return $stmt->execute([$responsableId, $commentaire, $id]);
        } catch (PDOException $e) {
            error_log("Erreur approuver: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rejeter une demande de renouvellement
     */
    public function rejeter(int $id, int $responsableId, string $commentaire): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE demande_renouvellement 
                SET statut = 'rejetee', id_responsable = ?, commentaire = ?, date_traitement = NOW()
                WHERE id_demande = ?
            ");
            return $stmt->execute([$responsableId, $commentaire, $id]);
        } catch (PDOException $e) {
            error_log("Erreur rejeter: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier si une ordonnance a déjà une demande en attente
     */
    public function hasDemandeEnAttente(int $ordonnanceId, int $clientId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM demande_renouvellement 
                WHERE id_ordonnance_origine = ? AND id_client = ? AND statut = 'en_attente'
            ");
            $stmt->execute([$ordonnanceId, $clientId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur hasDemandeEnAttente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir les statistiques des demandes
     */
    public function getStatistics(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                    SUM(CASE WHEN statut = 'approuvee' THEN 1 ELSE 0 END) as approuvee,
                    SUM(CASE WHEN statut = 'rejetee' THEN 1 ELSE 0 END) as rejetee
                FROM demande_renouvellement
            ");
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur getStatistics: " . $e->getMessage());
            return [];
        }
    }
}
?>
