<?php
/**
 * Modèle Panier - Gestion du panier d'achat en session
 * Permet l'achat de médicaments sans ordonnance
 */

class Panier
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Config::getConnexion();
        
        // Initialiser le panier en session s'il n'existe pas
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
    }
    
    /**
     * Ajouter un article au panier
     */
    public function ajouterArticle(int $idMedicament, int $quantite = 1): bool
    {
        // Vérifier que le médicament existe et n'exige pas de prescription
        $medicament = $this->getMedicamentInfo($idMedicament);
        
        if (!$medicament) {
            return false;
        }
        
        // Ne pas permettre l'ajout de médicaments avec prescription obligatoire
        if ($medicament['prescription_obligatoire']) {
            return false;
        }
        
        // Vérifier le stock disponible
        if ($medicament['stock_disponible'] < $quantite) {
            return false;
        }
        
        // Si l'article existe déjà, augmenter la quantité
        if (isset($_SESSION['panier'][$idMedicament])) {
            $nouvelleQuantite = $_SESSION['panier'][$idMedicament]['quantite'] + $quantite;
            
            // Vérifier que la nouvelle quantité ne dépasse pas le stock
            if ($nouvelleQuantite > $medicament['stock_disponible']) {
                return false;
            }
            
            $_SESSION['panier'][$idMedicament]['quantite'] = $nouvelleQuantite;
        } else {
            // Ajouter un nouvel article
            $_SESSION['panier'][$idMedicament] = [
                'id_medicament' => $idMedicament,
                'nom_commercial' => $medicament['nom_commercial'],
                'nom_generique' => $medicament['nom_generique'],
                'prix_unitaire' => $medicament['prix_unitaire'],
                'quantite' => $quantite,
                'image' => $medicament['image'],
                'stock_disponible' => $medicament['stock_disponible']
            ];
        }
        
        return true;
    }
    
    /**
     * Retirer un article du panier
     */
    public function retirerArticle(int $idMedicament): bool
    {
        if (isset($_SESSION['panier'][$idMedicament])) {
            unset($_SESSION['panier'][$idMedicament]);
            return true;
        }
        return false;
    }
    
    /**
     * Mettre à jour la quantité d'un article
     */
    public function mettreAJourQuantite(int $idMedicament, int $quantite): bool
    {
        if (!isset($_SESSION['panier'][$idMedicament])) {
            return false;
        }
        
        if ($quantite <= 0) {
            return $this->retirerArticle($idMedicament);
        }
        
        // Vérifier le stock disponible
        $medicament = $this->getMedicamentInfo($idMedicament);
        if (!$medicament || $quantite > $medicament['stock_disponible']) {
            return false;
        }
        
        $_SESSION['panier'][$idMedicament]['quantite'] = $quantite;
        return true;
    }
    
    /**
     * Obtenir le contenu du panier
     */
    public function getContenu(): array
    {
        // Mettre à jour les informations en temps réel (prix, stock)
        foreach ($_SESSION['panier'] as $idMedicament => $article) {
            $medicament = $this->getMedicamentInfo($idMedicament);
            if ($medicament) {
                $_SESSION['panier'][$idMedicament]['prix_unitaire'] = $medicament['prix_unitaire'];
                $_SESSION['panier'][$idMedicament]['stock_disponible'] = $medicament['stock_disponible'];
                
                // Si la quantité dépasse le stock, l'ajuster
                if ($_SESSION['panier'][$idMedicament]['quantite'] > $medicament['stock_disponible']) {
                    $_SESSION['panier'][$idMedicament]['quantite'] = $medicament['stock_disponible'];
                }
            }
        }
        
        return $_SESSION['panier'];
    }
    
    /**
     * Calculer le total du panier
     */
    public function getTotal(): float
    {
        $total = 0;
        foreach ($_SESSION['panier'] as $article) {
            $total += $article['prix_unitaire'] * $article['quantite'];
        }
        return $total;
    }
    
    /**
     * Obtenir le nombre d'articles dans le panier
     */
    public function getNombreArticles(): int
    {
        $total = 0;
        foreach ($_SESSION['panier'] as $article) {
            $total += $article['quantite'];
        }
        return $total;
    }
    
    /**
     * Vider le panier
     */
    public function vider(): void
    {
        $_SESSION['panier'] = [];
    }
    
    /**
     * Vérifier si le panier est vide
     */
    public function estVide(): bool
    {
        return empty($_SESSION['panier']);
    }
    
    /**
     * Obtenir les informations d'un médicament
     */
    private function getMedicamentInfo(int $idMedicament): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id_medicament, nom_commercial, nom_generique, 
                       prix_unitaire, stock_disponible, prescription_obligatoire, image
                FROM medicament 
                WHERE id_medicament = ?
            ");
            $stmt->execute([$idMedicament]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Erreur getMedicamentInfo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Valider le panier avant achat
     */
    public function valider(): array
    {
        $erreurs = [];
        
        if ($this->estVide()) {
            $erreurs[] = "Le panier est vide.";
            return $erreurs;
        }
        
        foreach ($_SESSION['panier'] as $idMedicament => $article) {
            $medicament = $this->getMedicamentInfo($idMedicament);
            
            if (!$medicament) {
                $erreurs[] = "Le médicament '{$article['nom_commercial']}' n'est plus disponible.";
                $this->retirerArticle($idMedicament);
                continue;
            }
            
            if ($medicament['prescription_obligatoire']) {
                $erreurs[] = "Le médicament '{$article['nom_commercial']}' nécessite une prescription.";
                $this->retirerArticle($idMedicament);
                continue;
            }
            
            if ($medicament['stock_disponible'] < $article['quantite']) {
                $erreurs[] = "Stock insuffisant pour '{$article['nom_commercial']}'. Disponible: {$medicament['stock_disponible']}";
                
                if ($medicament['stock_disponible'] > 0) {
                    $this->mettreAJourQuantite($idMedicament, $medicament['stock_disponible']);
                } else {
                    $this->retirerArticle($idMedicament);
                }
            }
        }
        
        return $erreurs;
    }
    
    /**
     * Créer une transaction à partir du panier
     */
    public function creerTransaction(int $idClient, ?int $idPharmacien = null, string $modePaiement = 'especes'): ?int
    {
        try {
            // S'assurer que le schéma de la table transaction permet id_ordonnance = NULL et id_pharmacien = NULL
            try {
                $this->pdo->exec("ALTER TABLE transaction MODIFY id_ordonnance INT NULL");
                $this->pdo->exec("ALTER TABLE transaction MODIFY id_pharmacien INT NULL");
            } catch (PDOException $e) {
                // Modifié précédemment ou pas de permission ALTER TABLE
            }

            $this->pdo->beginTransaction();
            
            // Valider le panier
            $erreurs = $this->valider();
            if (!empty($erreurs)) {
                $this->pdo->rollBack();
                return null;
            }
            
            $total = $this->getTotal();
            
            // Créer la transaction (sans ordonnance pour vente libre)
            $stmt = $this->pdo->prepare("
                INSERT INTO transaction (id_ordonnance, id_client, id_pharmacien, 
                    montant_total, mode_paiement, statut)
                VALUES (NULL, ?, ?, ?, ?, 'completee')
            ");
            $stmt->execute([$idClient, $idPharmacien, $total, $modePaiement]);
            $transactionId = (int) $this->pdo->lastInsertId();
            
            // Créer les détails de transaction pour chaque article
            $stmtDetail = $this->pdo->prepare("
                INSERT INTO transaction_detail (id_transaction, id_medicament, quantite, prix_unitaire)
                VALUES (?, ?, ?, ?)
            ");
            
            // Mettre à jour le stock pour chaque article
            $stmtStock = $this->pdo->prepare("
                UPDATE medicament 
                SET stock_disponible = stock_disponible - ? 
                WHERE id_medicament = ?
            ");
            
            foreach ($_SESSION['panier'] as $article) {
                // Ajouter le détail
                $stmtDetail->execute([
                    $transactionId,
                    $article['id_medicament'],
                    $article['quantite'],
                    $article['prix_unitaire']
                ]);
                
                // Mettre à jour le stock
                $stmtStock->execute([
                    $article['quantite'],
                    $article['id_medicament']
                ]);
            }
            
            $this->pdo->commit();
            
            // Vider le panier après achat réussi
            $this->vider();
            
            return $transactionId;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Erreur creerTransaction: " . $e->getMessage());
            return null;
        }
    }
}
?>
