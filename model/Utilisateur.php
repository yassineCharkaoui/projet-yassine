<?php
/**
 * Modèle Utilisateur - Gestion des utilisateurs (CRUD)
 * Responsable, Pharmacien, Client
 */

class Utilisateur
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }
    
    /**
     * Authentification d'un utilisateur
     */
    public function authenticate(string $email, string $password): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM utilisateur 
                WHERE email = ? AND actif = TRUE
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['mot_de_passe'])) {
                return $user;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erreur authenticate: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function create(array $data): ?int
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, adresse, role)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $data['mot_de_passe'],
                $data['telephone'] ?? null,
                $data['adresse'] ?? null,
                $data['role'] ?? 'client'
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur create utilisateur: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer un utilisateur par ID
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Erreur getById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer tous les utilisateurs
     */
    public function getAll(?string $role = null): array
    {
        try {
            if ($role) {
                $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE role = ? ORDER BY nom, prenom");
                $stmt->execute([$role]);
            } else {
                $stmt = $this->pdo->query("SELECT * FROM utilisateur ORDER BY role, nom, prenom");
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $values = [];
            
            foreach (['nom', 'prenom', 'email', 'telephone', 'adresse', 'role', 'actif'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            
            if (isset($data['mot_de_passe'])) {
                $fields[] = "mot_de_passe = ?";
                $values[] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
            }
            
            if (empty($fields)) return false;
            
            $values[] = $id;
            $sql = "UPDATE utilisateur SET " . implode(', ', $fields) . " WHERE id_utilisateur = ?";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erreur update: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur delete: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier si un email existe déjà
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        try {
            if ($excludeId) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ? AND id_utilisateur != ?");
                $stmt->execute([$email, $excludeId]);
            } else {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
                $stmt->execute([$email]);
            }
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur emailExists: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour la dernière connexion
     */
    public function updateLastLogin(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE utilisateur SET date_derniere_connexion = NOW() WHERE id_utilisateur = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur updateLastLogin: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rechercher des utilisateurs
     */
    public function search(string $query, ?string $role = null): array
    {
        try {
            $searchTerm = "%$query%";
            if ($role) {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM utilisateur 
                    WHERE (nom LIKE ? OR prenom LIKE ? OR email LIKE ?) AND role = ?
                    ORDER BY nom, prenom
                ");
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $role]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM utilisateur 
                    WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ?
                    ORDER BY nom, prenom
                ");
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur search: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les statistiques des utilisateurs par rôle
     */
    public function getStatsByRole(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT role, COUNT(*) as total, 
                       SUM(CASE WHEN actif = TRUE THEN 1 ELSE 0 END) as actifs
                FROM utilisateur 
                GROUP BY role
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getStatsByRole: " . $e->getMessage());
            return [];
        }
    }
}
?>
