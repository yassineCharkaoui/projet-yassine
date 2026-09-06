<?php
/**
 * Modèle Ordonnance - Gestion des ordonnances (CRUD)
 */

class Ordonnance
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }
    
    /**
     * Créer une nouvelle ordonnance
     */
    public function create(array $data): ?int
    {
        try {
            $this->pdo->beginTransaction();
            
            // Générer un numéro d'ordonnance unique
            $numeroOrdonnance = $this->generateNumeroOrdonnance();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO ordonnance (numero_ordonnance, id_client, nom_medecin, 
                    date_prescription, fichier_scan, ordonnance_renouvelable, date_renouvellement)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $numeroOrdonnance,
                $data['id_client'],
                $data['nom_medecin'],
                $data['date_prescription'],
                $data['fichier_scan'] ?? null,
                $data['ordonnance_renouvelable'] ?? false,
                $data['date_renouvellement'] ?? null
            ]);
            
            $ordonnanceId = (int) $this->pdo->lastInsertId();
            
            // Ajouter les médicaments si fournis
            if (!empty($data['medicaments'])) {
                foreach ($data['medicaments'] as $med) {
                    $this->addMedicament($ordonnanceId, $med);
                }
            }
            
            $this->pdo->commit();
            return $ordonnanceId;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur create ordonnance: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Générer un numéro d'ordonnance unique
     */
    private function generateNumeroOrdonnance(): string
    {
        return 'ORD-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Ajouter un médicament à une ordonnance
     */
    public function addMedicament(int $ordonnanceId, array $data): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ordonnance_medicament (id_ordonnance, id_medicament, quantite, 
                    posologie_prescrite, duree_traitement, prix_unitaire_vente)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $ordonnanceId,
                $data['id_medicament'],
                $data['quantite'] ?? 1,
                $data['posologie_prescrite'] ?? null,
                $data['duree_traitement'] ?? null,
                $data['prix_unitaire_vente']
            ]);
        } catch (PDOException $e) {
            error_log("Erreur addMedicament: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer une ordonnance par ID avec ses médicaments
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT o.*, 
                    c.nom as client_nom, c.prenom as client_prenom, c.email as client_email,
                    c.telephone as client_telephone, c.adresse as client_adresse,
                    p.nom as pharmacien_nom, p.prenom as pharmacien_prenom
                FROM ordonnance o
                JOIN utilisateur c ON o.id_client = c.id_utilisateur
                LEFT JOIN utilisateur p ON o.id_pharmacien = p.id_utilisateur
                WHERE o.id_ordonnance = ?
            ");
            $stmt->execute([$id]);
            $ordonnance = $stmt->fetch();
            
            if ($ordonnance) {
                $ordonnance['medicaments'] = $this->getMedicamentsByOrdonnance($id);
            }
            
            return $ordonnance ?: null;
        } catch (PDOException $e) {
            error_log("Erreur getById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer les médicaments d'une ordonnance
     */
    public function getMedicamentsByOrdonnance(int $ordonnanceId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT om.*, m.nom_commercial, m.nom_generique, m.forme, m.dosage, 
                    m.stock_disponible, m.prescription_obligatoire
                FROM ordonnance_medicament om
                JOIN medicament m ON om.id_medicament = m.id_medicament
                WHERE om.id_ordonnance = ?
            ");
            $stmt->execute([$ordonnanceId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getMedicamentsByOrdonnance: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer toutes les ordonnances
     */
    public function getAll(?string $statut = null): array
    {
        try {
            if ($statut) {
                $stmt = $this->pdo->prepare("
                    SELECT o.*, 
                        c.nom as client_nom, c.prenom as client_prenom,
                        p.nom as pharmacien_nom, p.prenom as pharmacien_prenom
                    FROM ordonnance o
                    JOIN utilisateur c ON o.id_client = c.id_utilisateur
                    LEFT JOIN utilisateur p ON o.id_pharmacien = p.id_utilisateur
                    WHERE o.statut = ?
                    ORDER BY o.date_soumission DESC
                ");
                $stmt->execute([$statut]);
            } else {
                $stmt = $this->pdo->query("
                    SELECT o.*, 
                        c.nom as client_nom, c.prenom as client_prenom,
                        p.nom as pharmacien_nom, p.prenom as pharmacien_prenom
                    FROM ordonnance o
                    JOIN utilisateur c ON o.id_client = c.id_utilisateur
                    LEFT JOIN utilisateur p ON o.id_pharmacien = p.id_utilisateur
                    ORDER BY o.date_soumission DESC
                ");
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les ordonnances d'un client
     */
    public function getByClient(int $clientId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT o.*, 
                    p.nom as pharmacien_nom, p.prenom as pharmacien_prenom
                FROM ordonnance o
                LEFT JOIN utilisateur p ON o.id_pharmacien = p.id_utilisateur
                WHERE o.id_client = ?
                ORDER BY o.date_soumission DESC
            ");
            $stmt->execute([$clientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByClient: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les ordonnances traitées par un pharmacien
     */
    public function getByPharmacien(int $pharmacienId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT o.*, 
                    c.nom as client_nom, c.prenom as client_prenom
                FROM ordonnance o
                JOIN utilisateur c ON o.id_client = c.id_utilisateur
                WHERE o.id_pharmacien = ?
                ORDER BY o.date_validation DESC
            ");
            $stmt->execute([$pharmacienId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByPharmacien: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mettre à jour le statut d'une ordonnance
     */
    public function updateStatut(int $id, string $statut, int $pharmacienId, ?string $commentaire = null, ?string $motifRejet = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE ordonnance 
                SET statut = ?, id_pharmacien = ?, commentaire_pharmacien = ?, 
                    motif_rejet = ?, date_validation = NOW()
                WHERE id_ordonnance = ?
            ");
            return $stmt->execute([$statut, $pharmacienId, $commentaire, $motifRejet, $id]);
        } catch (PDOException $e) {
            error_log("Erreur updateStatut: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valider une ordonnance
     */
    public function valider(int $id, int $pharmacienId, ?string $commentaire = null): bool
    {
        return $this->updateStatut($id, 'validee', $pharmacienId, $commentaire);
    }
    
    /**
     * Rejeter une ordonnance
     */
    public function rejeter(int $id, int $pharmacienId, string $motifRejet): bool
    {
        return $this->updateStatut($id, 'rejetee', $pharmacienId, null, $motifRejet);
    }
    
    /**
     * Marquer une ordonnance comme traitée
     */
    public function traiter(int $id, int $pharmacienId): bool
    {
        return $this->updateStatut($id, 'traitee', $pharmacienId);
    }
    
    /**
     * Supprimer une ordonnance
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM ordonnance WHERE id_ordonnance = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur delete: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir les ordonnances en attente
     */
    public function getEnAttente(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM v_ordonnances_en_attente");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getEnAttente: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les statistiques des ordonnances
     */
    public function getStatistics(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                    SUM(CASE WHEN statut = 'validee' THEN 1 ELSE 0 END) as validee,
                    SUM(CASE WHEN statut = 'rejetee' THEN 1 ELSE 0 END) as rejetee,
                    SUM(CASE WHEN statut = 'traitee' THEN 1 ELSE 0 END) as traitee,
                    SUM(montant_total) as montant_total
                FROM ordonnance
            ");
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur getStatistics: " . $e->getMessage());
            return [];
        }
    }
}
?>
