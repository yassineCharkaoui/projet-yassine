<?php
/**
 * Modèle Interaction - Gestion des interactions médicamenteuses
 */

class Interaction
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }
    
    /**
     * Créer une nouvelle interaction médicamenteuse
     */
    public function create(array $data): ?int
    {
        try {
            // Assurer que id_medicament_1 < id_medicament_2
            $med1 = min($data['id_medicament_1'], $data['id_medicament_2']);
            $med2 = max($data['id_medicament_1'], $data['id_medicament_2']);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO interaction_medicamenteuse (id_medicament_1, id_medicament_2, 
                    niveau_gravite, description, recommandation)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $med1,
                $med2,
                $data['niveau_gravite'],
                $data['description'],
                $data['recommandation'] ?? null
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur create interaction: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer une interaction par ID
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT i.*, 
                    m1.nom_commercial as med1_nom, m1.forme as med1_forme,
                    m2.nom_commercial as med2_nom, m2.forme as med2_forme
                FROM interaction_medicamenteuse i
                JOIN medicament m1 ON i.id_medicament_1 = m1.id_medicament
                JOIN medicament m2 ON i.id_medicament_2 = m2.id_medicament
                WHERE i.id_interaction = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Erreur getById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer toutes les interactions
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT i.*, 
                    m1.nom_commercial as med1_nom, m1.forme as med1_forme,
                    m2.nom_commercial as med2_nom, m2.forme as med2_forme
                FROM interaction_medicamenteuse i
                JOIN medicament m1 ON i.id_medicament_1 = m1.id_medicament
                JOIN medicament m2 ON i.id_medicament_2 = m2.id_medicament
                ORDER BY i.niveau_gravite DESC, m1.nom_commercial
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Détecter les interactions dans une liste de médicaments
     */
    public function detecterInteractions(array $medicamentIds): array
    {
        try {
            if (count($medicamentIds) < 2) {
                return [];
            }
            
            $placeholders = implode(',', array_fill(0, count($medicamentIds), '?'));
            
            $stmt = $this->pdo->prepare("
                SELECT i.*, 
                    m1.nom_commercial as med1_nom, m1.forme as med1_forme,
                    m2.nom_commercial as med2_nom, m2.forme as med2_forme
                FROM interaction_medicamenteuse i
                JOIN medicament m1 ON i.id_medicament_1 = m1.id_medicament
                JOIN medicament m2 ON i.id_medicament_2 = m2.id_medicament
                WHERE i.id_medicament_1 IN ($placeholders)
                  AND i.id_medicament_2 IN ($placeholders)
                ORDER BY 
                    CASE i.niveau_gravite
                        WHEN 'contre_indique' THEN 1
                        WHEN 'majeur' THEN 2
                        WHEN 'modere' THEN 3
                        WHEN 'mineur' THEN 4
                    END
            ");
            $params = array_merge($medicamentIds, $medicamentIds);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur detecterInteractions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les interactions d'un médicament spécifique
     */
    public function getByMedicament(int $medicamentId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT i.*, 
                    m1.nom_commercial as med1_nom, m1.forme as med1_forme,
                    m2.nom_commercial as med2_nom, m2.forme as med2_forme
                FROM interaction_medicamenteuse i
                JOIN medicament m1 ON i.id_medicament_1 = m1.id_medicament
                JOIN medicament m2 ON i.id_medicament_2 = m2.id_medicament
                WHERE i.id_medicament_1 = ? OR i.id_medicament_2 = ?
                ORDER BY i.niveau_gravite DESC
            ");
            $stmt->execute([$medicamentId, $medicamentId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByMedicament: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Créer une alerte d'interaction pour une ordonnance
     */
    public function creerAlerte(int $ordonnanceId, int $interactionId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO alerte_interaction (id_ordonnance, id_interaction)
                VALUES (?, ?)
            ");
            return $stmt->execute([$ordonnanceId, $interactionId]);
        } catch (PDOException $e) {
            error_log("Erreur creerAlerte: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les alertes d'une ordonnance
     */
    public function getAlertesByOrdonnance(int $ordonnanceId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, i.niveau_gravite, i.description, i.recommandation,
                    m1.nom_commercial as med1_nom, m2.nom_commercial as med2_nom
                FROM alerte_interaction a
                JOIN interaction_medicamenteuse i ON a.id_interaction = i.id_interaction
                JOIN medicament m1 ON i.id_medicament_1 = m1.id_medicament
                JOIN medicament m2 ON i.id_medicament_2 = m2.id_medicament
                WHERE a.id_ordonnance = ?
                ORDER BY 
                    CASE i.niveau_gravite
                        WHEN 'contre_indique' THEN 1
                        WHEN 'majeur' THEN 2
                        WHEN 'modere' THEN 3
                        WHEN 'mineur' THEN 4
                    END
            ");
            $stmt->execute([$ordonnanceId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getAlertesByOrdonnance: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marquer une alerte comme vue par le pharmacien
     */
    public function marquerVuePharmacien(int $alerteId, ?string $actionPrise = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE alerte_interaction 
                SET vue_par_pharmacien = TRUE, action_prise = ?
                WHERE id_alerte = ?
            ");
            return $stmt->execute([$actionPrise, $alerteId]);
        } catch (PDOException $e) {
            error_log("Erreur marquerVuePharmacien: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marquer une alerte comme vue par le client
     */
    public function marquerVueClient(int $alerteId): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE alerte_interaction SET vue_par_client = TRUE WHERE id_alerte = ?");
            return $stmt->execute([$alerteId]);
        } catch (PDOException $e) {
            error_log("Erreur marquerVueClient: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour une interaction
     */
    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE interaction_medicamenteuse 
                SET id_medicament_1 = ?, id_medicament_2 = ?, niveau_gravite = ?, description = ?, recommandation = ?
                WHERE id_interaction = ?
            ");
            return $stmt->execute([
                min($data['id_medicament_1'], $data['id_medicament_2']),
                max($data['id_medicament_1'], $data['id_medicament_2']),
                $data['niveau_gravite'],
                $data['description'],
                $data['recommandation'] ?? null,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Erreur update: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer une interaction
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM interaction_medicamenteuse WHERE id_interaction = ? AND NOT EXISTS (SELECT 1 FROM alerte_interaction WHERE alerte_interaction.id_interaction = interaction_medicamenteuse.id_interaction)");
            $stmt->execute([$id]);
            return $stmt->rowCount() === 1;
        } catch (PDOException $e) {
            error_log("Erreur delete: " . $e->getMessage());
            return false;
        }
    }

    public function pairExists(int $med1, int $med2, int $excludeId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM interaction_medicamenteuse WHERE ((id_medicament_1 = ? AND id_medicament_2 = ?) OR (id_medicament_1 = ? AND id_medicament_2 = ?)) AND id_interaction <> ?');
        $stmt->execute([$med1, $med2, $med2, $med1, $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function hasAlerts(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM alerte_interaction WHERE id_interaction = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
?>
