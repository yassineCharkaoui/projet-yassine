<?php
/**
 * Modèle Medicament - Gestion des médicaments (CRUD)
 */

class Medicament
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }
    
    /**
     * Créer un nouveau médicament
     */
    public function create(array $data): ?int
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO medicament (nom_commercial, nom_generique, dci, forme, dosage, 
                    prix_unitaire, stock_disponible, stock_minimum, date_peremption, 
                    laboratoire, categorie, prescription_obligatoire, description, 
                    contre_indications, effets_secondaires, posologie, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['nom_commercial'],
                $data['nom_generique'] ?? null,
                $data['dci'] ?? null,
                $data['forme'] ?? null,
                $data['dosage'] ?? null,
                $data['prix_unitaire'],
                $data['stock_disponible'] ?? 0,
                $data['stock_minimum'] ?? 10,
                $data['date_peremption'] ?? null,
                $data['laboratoire'] ?? null,
                $data['categorie'] ?? null,
                $data['prescription_obligatoire'] ?? false,
                $data['description'] ?? null,
                $data['contre_indications'] ?? null,
                $data['effets_secondaires'] ?? null,
                $data['posologie'] ?? null,
                $data['image'] ?? null
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur create medicament: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer un médicament par ID
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM medicament WHERE id_medicament = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Erreur getById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer tous les médicaments
     */
    public function getAll(bool $activeOnly = true): array
    {
        try {
            if ($activeOnly) {
                $stmt = $this->pdo->query("SELECT * FROM medicament WHERE actif = TRUE ORDER BY nom_commercial");
            } else {
                $stmt = $this->pdo->query("SELECT * FROM medicament ORDER BY nom_commercial");
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mettre à jour un médicament
     */
    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $values = [];
            
            $allowedFields = ['nom_commercial', 'nom_generique', 'dci', 'forme', 'dosage', 
                'prix_unitaire', 'stock_disponible', 'stock_minimum', 'date_peremption', 
                'laboratoire', 'categorie', 'prescription_obligatoire', 'description', 
                'contre_indications', 'effets_secondaires', 'posologie', 'actif', 'image'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            
            if (empty($fields)) return false;
            
            $values[] = $id;
            $sql = "UPDATE medicament SET " . implode(', ', $fields) . " WHERE id_medicament = ?";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erreur update: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un médicament
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM medicament WHERE id_medicament = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur delete: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Recherche multicritères
     */
    public function search(array $criteria): array
    {
        try {
            $where = ["actif = TRUE"];
            $params = [];
            
            if (!empty($criteria['nom'])) {
                $where[] = "(nom_commercial LIKE ? OR nom_generique LIKE ?)";
                $params[] = "%{$criteria['nom']}%";
                $params[] = "%{$criteria['nom']}%";
            }
            
            if (!empty($criteria['categorie'])) {
                $where[] = "categorie = ?";
                $params[] = $criteria['categorie'];
            }
            
            if (!empty($criteria['forme'])) {
                $where[] = "forme = ?";
                $params[] = $criteria['forme'];
            }
            
            if (isset($criteria['prescription_obligatoire'])) {
                $where[] = "prescription_obligatoire = ?";
                $params[] = $criteria['prescription_obligatoire'];
            }
            
            if (!empty($criteria['laboratoire'])) {
                $where[] = "laboratoire LIKE ?";
                $params[] = "%{$criteria['laboratoire']}%";
            }
            
            $sql = "SELECT * FROM medicament WHERE " . implode(' AND ', $where) . " ORDER BY nom_commercial";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur search: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les médicaments en stock critique
     */
    public function getStockCritique(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM v_stock_critique ORDER BY stock_disponible ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getStockCritique: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les médicaments par catégorie
     */
    public function getByCategorie(string $categorie): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM medicament 
                WHERE categorie = ? AND actif = TRUE 
                ORDER BY nom_commercial
            ");
            $stmt->execute([$categorie]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getByCategorie: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir toutes les catégories
     */
    public function getCategories(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT DISTINCT categorie 
                FROM medicament 
                WHERE categorie IS NOT NULL AND actif = TRUE 
                ORDER BY categorie
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erreur getCategories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir toutes les formes
     */
    public function getFormes(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT DISTINCT forme 
                FROM medicament 
                WHERE forme IS NOT NULL AND actif = TRUE 
                ORDER BY forme
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erreur getFormes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mettre à jour le stock
     */
    public function updateStock(int $id, int $quantity, string $operation = 'add'): bool
    {
        try {
            if ($operation === 'add') {
                $sql = "UPDATE medicament SET stock_disponible = stock_disponible + ? WHERE id_medicament = ?";
            } else {
                $sql = "UPDATE medicament SET stock_disponible = stock_disponible - ? WHERE id_medicament = ?";
            }
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$quantity, $id]);
        } catch (PDOException $e) {
            error_log("Erreur updateStock: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir les statistiques des médicaments
     */
    public function getStatistics(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN stock_disponible <= stock_minimum THEN 1 ELSE 0 END) as stock_critique,
                    SUM(CASE WHEN stock_disponible = 0 THEN 1 ELSE 0 END) as rupture_stock,
                    SUM(CASE WHEN prescription_obligatoire = TRUE THEN 1 ELSE 0 END) as avec_prescription
                FROM medicament 
                WHERE actif = TRUE
            ");
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur getStatistics: " . $e->getMessage());
            return [];
        }
    }
}
?>
