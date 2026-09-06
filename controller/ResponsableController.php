<?php
/**
 * Contrôleur Responsable - Gestion des fonctionnalités du responsable de pharmacie
 * - Gestion des stocks de médicaments (CRUD)
 * - Validation des ordonnances et demandes de renouvellement
 * - Rapports et statistiques
 * - Recherche multicritères
 */

require_once __DIR__ . '/../model/Medicament.php';
require_once __DIR__ . '/../model/Ordonnance.php';
require_once __DIR__ . '/../model/Utilisateur.php';
require_once __DIR__ . '/../model/DemandeRenouvellement.php';
require_once __DIR__ . '/../model/Transaction.php';
require_once __DIR__ . '/PDFGenerator.php';

class ResponsableController
{
    private Medicament $medicamentModel;
    private Ordonnance $ordonnanceModel;
    private Utilisateur $utilisateurModel;
    private DemandeRenouvellement $demandeModel;
    private Transaction $transactionModel;
    
    public function __construct()
    {
        // Vérifier que l'utilisateur est connecté et est un responsable
        if (!isLoggedIn() || !hasRole('responsable')) {
            setFlashMessage('error', 'Accès non autorisé.');
            redirect('index.php?controller=auth&action=login');
        }
        
        $this->medicamentModel = new Medicament();
        $this->ordonnanceModel = new Ordonnance();
        $this->utilisateurModel = new Utilisateur();
        $this->demandeModel = new DemandeRenouvellement();
        $this->transactionModel = new Transaction();
    }
    
    /**
     * Dashboard du responsable
     */
    public function dashboard(): void
    {
        $data = [
            'stats_medicaments' => $this->medicamentModel->getStatistics(),
            'stats_ordonnances' => $this->ordonnanceModel->getStatistics(),
            'stats_demandes' => $this->demandeModel->getStatistics(),
            'stats_transactions' => $this->transactionModel->getStatistics('mois'),
            'stock_critique' => $this->medicamentModel->getStockCritique(),
            'ordonnances_en_attente' => $this->ordonnanceModel->getEnAttente(),
            'demandes_en_attente' => $this->demandeModel->getAll('en_attente')
        ];
        
        require_once __DIR__ . '/../view/responsable/dashboard.php';
    }
    
    /**
     * Liste des médicaments
     */
    public function listMedicaments(): void
    {
        $sortOrder = $_GET['sort'] ?? null; // 'asc' ou 'desc'
        $currency = $_GET['currency'] ?? 'EUR'; // EUR, USD, TND
        
        $medicaments = $this->medicamentModel->getAll(true);
        
        // Tri par prix
        if ($sortOrder === 'asc' || $sortOrder === 'desc') {
            usort($medicaments, function($a, $b) use ($sortOrder) {
                if ($sortOrder === 'asc') {
                    return $a['prix_unitaire'] <=> $b['prix_unitaire'];
                } else {
                    return $b['prix_unitaire'] <=> $a['prix_unitaire'];
                }
            });
        }
        
        require_once __DIR__ . '/../view/responsable/medicaments/list.php';
    }
    
    /**
     * Afficher le formulaire d'ajout de médicament
     */
    public function addMedicament(): void
    {
        $categories = $this->medicamentModel->getCategories();
        $formes = $this->medicamentModel->getFormes();
        require_once __DIR__ . '/../view/responsable/medicaments/add.php';
    }
    
    /**
     * Traiter l'ajout de médicament
     */
    public function processAddMedicament(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=responsable&action=addMedicament');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Erreur de sécurité.');
            redirect('index.php?controller=responsable&action=addMedicament');
        }
        
        // Gestion de l'upload d'image
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../view/uploads/medicaments/';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            $fileType = $_FILES['image']['type'];
            $fileSize = $_FILES['image']['size'];
            $fileTmpName = $_FILES['image']['tmp_name'];
            
            // Vérifier le type et la taille
            if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                // Vérifier le type MIME réel du fichier
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $realMimeType = finfo_file($finfo, $fileTmpName);
                finfo_close($finfo);
                
                if (in_array($realMimeType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $imageName = 'med_' . time() . '_' . uniqid() . '.' . strtolower($extension);
                    $uploadPath = $uploadDir . $imageName;
                    
                    if (!move_uploaded_file($fileTmpName, $uploadPath)) {
                        setFlashMessage('warning', 'Erreur lors de l\'upload de l\'image. Le médicament sera ajouté sans image.');
                        $imageName = null;
                    }
                } else {
                    setFlashMessage('warning', 'Type de fichier non valide. Le médicament sera ajouté sans image.');
                }
            } else {
                setFlashMessage('warning', 'Image trop volumineuse ou type non autorisé. Le médicament sera ajouté sans image.');
            }
        }
        
        $data = [
            'nom_commercial' => trim($_POST['nom_commercial']),
            'nom_generique' => trim($_POST['nom_generique']),
            'dci' => trim($_POST['dci']),
            'forme' => $_POST['forme'],
            'dosage' => trim($_POST['dosage']),
            'prix_unitaire' => floatval($_POST['prix_unitaire']),
            'stock_disponible' => intval($_POST['stock_disponible']),
            'stock_minimum' => intval($_POST['stock_minimum']),
            'date_peremption' => $_POST['date_peremption'] ?: null,
            'laboratoire' => trim($_POST['laboratoire']),
            'categorie' => $_POST['categorie'],
            'prescription_obligatoire' => ($_POST['prescription_obligatoire'] ?? '0') === '1' ? 1 : 0,
            'description' => trim($_POST['description']),
            'contre_indications' => trim($_POST['contre_indications']),
            'effets_secondaires' => trim($_POST['effets_secondaires']),
            'posologie' => trim($_POST['posologie']),
            'image' => $imageName
        ];
        
        $id = $this->medicamentModel->create($data);
        
        if ($id) {
            logAction($_SESSION['user_id'], 'CREATE', 'medicament', $id, "Ajout médicament: {$data['nom_commercial']}");
            setFlashMessage('success', 'Médicament ajouté avec succès' . ($imageName ? ' avec image.' : '.'));
            redirect('index.php?controller=responsable&action=listMedicaments');
        } else {
            // Supprimer l'image si l'ajout a échoué
            if ($imageName && file_exists($uploadDir . $imageName)) {
                unlink($uploadDir . $imageName);
            }
            setFlashMessage('error', 'Erreur lors de l\'ajout du médicament.');
            redirect('index.php?controller=responsable&action=addMedicament');
        }
    }
    
    /**
     * Afficher le formulaire de modification de médicament
     */
    public function editMedicament(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $medicament = $this->medicamentModel->getById($id);
        
        if (!$medicament) {
            setFlashMessage('error', 'Médicament non trouvé.');
            redirect('index.php?controller=responsable&action=listMedicaments');
        }
        
        $categories = $this->medicamentModel->getCategories();
        $formes = $this->medicamentModel->getFormes();
        require_once __DIR__ . '/../view/responsable/medicaments/edit.php';
    }
    
    /**
     * Traiter la modification de médicament
     */
    public function processEditMedicament(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=responsable&action=listMedicaments');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Erreur de sécurité.');
            redirect('index.php?controller=responsable&action=listMedicaments');
        }
        
        $id = intval($_POST['id_medicament']);
        
        // Récupérer les données actuelles pour garder l'ancienne image si nécessaire
        $currentData = $this->medicamentModel->getById($id);
        $imageName = $currentData['image'] ?? null;
        
        // Gestion de l'upload d'une nouvelle image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../view/uploads/medicaments/';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            $fileType = $_FILES['image']['type'];
            $fileSize = $_FILES['image']['size'];
            $fileTmpName = $_FILES['image']['tmp_name'];
            
            if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                // Vérifier le type MIME réel
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $realMimeType = finfo_file($finfo, $fileTmpName);
                finfo_close($finfo);
                
                if (in_array($realMimeType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $newImageName = 'med_' . time() . '_' . uniqid() . '.' . strtolower($extension);
                    $uploadPath = $uploadDir . $newImageName;
                    
                    if (move_uploaded_file($fileTmpName, $uploadPath)) {
                        // Supprimer l'ancienne image si elle existe
                        if ($imageName && file_exists($uploadDir . $imageName)) {
                            unlink($uploadDir . $imageName);
                        }
                        $imageName = $newImageName;
                    } else {
                        setFlashMessage('warning', 'Erreur lors de l\'upload de la nouvelle image. L\'ancienne image est conservée.');
                    }
                } else {
                    setFlashMessage('warning', 'Type de fichier non valide. L\'ancienne image est conservée.');
                }
            } else {
                setFlashMessage('warning', 'Image trop volumineuse ou type non autorisé. L\'ancienne image est conservée.');
            }
        }
        
        $data = [
            'nom_commercial' => trim($_POST['nom_commercial']),
            'nom_generique' => trim($_POST['nom_generique']),
            'dci' => trim($_POST['dci']),
            'forme' => $_POST['forme'],
            'dosage' => trim($_POST['dosage']),
            'prix_unitaire' => floatval($_POST['prix_unitaire']),
            'stock_disponible' => intval($_POST['stock_disponible']),
            'stock_minimum' => intval($_POST['stock_minimum']),
            'date_peremption' => $_POST['date_peremption'] ?: null,
            'laboratoire' => trim($_POST['laboratoire']),
            'categorie' => $_POST['categorie'],
            'prescription_obligatoire' => ($_POST['prescription_obligatoire'] ?? '0') === '1' ? 1 : 0,
            'description' => trim($_POST['description']),
            'contre_indications' => trim($_POST['contre_indications']),
            'effets_secondaires' => trim($_POST['effets_secondaires']),
            'posologie' => trim($_POST['posologie']),
            'image' => $imageName
        ];
        
        if ($this->medicamentModel->update($id, $data)) {
            logAction($_SESSION['user_id'], 'UPDATE', 'medicament', $id, "Modification médicament: {$data['nom_commercial']}");
            setFlashMessage('success', 'Médicament modifié avec succès.');
        } else {
            setFlashMessage('error', 'Erreur lors de la modification.');
        }
        
        redirect('index.php?controller=responsable&action=listMedicaments');
    }
    
    /**
     * Supprimer un médicament
     */
    public function deleteMedicament(): void
    {
        $id = intval($_GET['id'] ?? 0);
        
        if ($this->medicamentModel->delete($id)) {
            logAction($_SESSION['user_id'], 'DELETE', 'medicament', $id);
            setFlashMessage('success', 'Médicament supprimé avec succès.');
        } else {
            setFlashMessage('error', 'Erreur lors de la suppression.');
        }
        
        redirect('index.php?controller=responsable&action=listMedicaments');
    }
    
    /**
     * Recherche multicritères de médicaments
     */
    public function searchMedicaments(): void
    {
        $criteria = [
            'nom' => $_GET['nom'] ?? '',
            'categorie' => $_GET['categorie'] ?? '',
            'forme' => $_GET['forme'] ?? '',
            'laboratoire' => $_GET['laboratoire'] ?? ''
        ];
        
        if (isset($_GET['prescription_obligatoire'])) {
            $criteria['prescription_obligatoire'] = $_GET['prescription_obligatoire'] === '1';
        }
        
        $medicaments = $this->medicamentModel->search($criteria);
        $categories = $this->medicamentModel->getCategories();
        $formes = $this->medicamentModel->getFormes();
        
        require_once __DIR__ . '/../view/responsable/medicaments/search.php';
    }
    
    /**
     * Rapport des stocks critiques
     */
    public function rapportStockCritique(): void
    {
        $stocks = $this->medicamentModel->getStockCritique();
        require_once __DIR__ . '/../view/responsable/rapports/stock_critique.php';
    }
    
    /**
     * Liste des demandes de renouvellement
     */
    public function listDemandes(): void
    {
        $demandes = $this->demandeModel->getAll();
        require_once __DIR__ . '/../view/responsable/demandes/list.php';
    }
    
    /**
     * Approuver une demande de renouvellement
     */
    public function approuverDemande(): void
    {
        $id = intval($_POST['id_demande'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        
        if ($this->demandeModel->approuver($id, $_SESSION['user_id'], $commentaire)) {
            logAction($_SESSION['user_id'], 'APPROVE', 'demande_renouvellement', $id);
            setFlashMessage('success', 'Demande approuvée avec succès.');
        } else {
            setFlashMessage('error', 'Erreur lors de l\'approbation.');
        }
        
        redirect('index.php?controller=responsable&action=listDemandes');
    }
    
    /**
     * Rejeter une demande de renouvellement
     */
    public function rejeterDemande(): void
    {
        $id = intval($_POST['id_demande'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        
        if (empty($commentaire)) {
            setFlashMessage('error', 'Le motif de rejet est obligatoire.');
            redirect('index.php?controller=responsable&action=listDemandes');
        }
        
        if ($this->demandeModel->rejeter($id, $_SESSION['user_id'], $commentaire)) {
            logAction($_SESSION['user_id'], 'REJECT', 'demande_renouvellement', $id);
            setFlashMessage('success', 'Demande rejetée.');
        } else {
            setFlashMessage('error', 'Erreur lors du rejet.');
        }
        
        redirect('index.php?controller=responsable&action=listDemandes');
    }
    
    /**
     * Gestion des utilisateurs
     */
    public function listUtilisateurs(): void
    {
        $utilisateurs = $this->utilisateurModel->getAll();
        require_once __DIR__ . '/../view/responsable/utilisateurs/list.php';
    }
    
    /**
     * Rapports et statistiques
     */
    public function rapports(): void
    {
        $data = [
            'stats_medicaments' => $this->medicamentModel->getStatistics(),
            'stats_ordonnances' => $this->ordonnanceModel->getStatistics(),
            'stats_transactions' => $this->transactionModel->getStatistics(),
            'stats_utilisateurs' => $this->utilisateurModel->getStatsByRole(),
            'transactions_par_mode' => $this->transactionModel->getByModePaiement()
        ];
        
        require_once __DIR__ . '/../view/responsable/rapports/index.php';
    }

    /**
     * Générer le PDF d'une ordonnance
     */
    public function generatePDF(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $ordonnance = $this->ordonnanceModel->getById($id);
        
        if (!$ordonnance) {
            setFlashMessage('error', 'Ordonnance non trouvée.');
            redirect('index.php?controller=responsable&action=dashboard');
        }
        
        try {
            // Télécharger directement le PDF
            PDFGenerator::downloadOrdonnancePDF($ordonnance);
            exit;
        } catch (Exception $e) {
            setFlashMessage('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
            redirect('index.php?controller=responsable&action=dashboard');
        }
    }
}
