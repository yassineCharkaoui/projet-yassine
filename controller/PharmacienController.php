<?php
/**
 * Contrôleur Pharmacien - Gestion des fonctionnalités du pharmacien
 * - Consultation et validation des ordonnances
 * - Enregistrement des interactions médicamenteuses
 * - Visualisation de l'historique des transactions
 */

require_once __DIR__ . '/../model/Ordonnance.php';
require_once __DIR__ . '/../model/Medicament.php';
require_once __DIR__ . '/../model/Transaction.php';
require_once __DIR__ . '/../model/Interaction.php';
require_once __DIR__ . '/PDFGenerator.php';

class PharmacienController
{
    private Ordonnance $ordonnanceModel;
    private Medicament $medicamentModel;
    private Transaction $transactionModel;
    private Interaction $interactionModel;
    
    public function __construct()
    {
        // Vérifier que l'utilisateur est connecté et est un pharmacien
        if (!isLoggedIn() || !hasRole('pharmacien')) {
            setFlashMessage('error', 'Accès non autorisé.');
            redirect('index.php?controller=auth&action=login');
        }
        
        $this->ordonnanceModel = new Ordonnance();
        $this->medicamentModel = new Medicament();
        $this->transactionModel = new Transaction();
        $this->interactionModel = new Interaction();
    }
    
    /**
     * Dashboard du pharmacien
     */
    public function dashboard(): void
    {
        $data = [
            'ordonnances_en_attente' => $this->ordonnanceModel->getAll('en_attente'),
            'mes_transactions' => $this->transactionModel->getByPharmacien($_SESSION['user_id']),
            'mes_ordonnances' => $this->ordonnanceModel->getByPharmacien($_SESSION['user_id']),
            'stats' => $this->ordonnanceModel->getStatistics()
        ];
        
        require_once __DIR__ . '/../view/pharmacien/dashboard.php';
    }
    
    /**
     * Liste des ordonnances
     */
    public function listOrdonnances(): void
    {
        $statut = $_GET['statut'] ?? null;
        $ordonnances = $statut ? $this->ordonnanceModel->getAll($statut) : $this->ordonnanceModel->getAll();
        require_once __DIR__ . '/../view/pharmacien/ordonnances/list.php';
    }
    
    /**
     * Détails d'une ordonnance
     */
    public function viewOrdonnance(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $ordonnance = $this->ordonnanceModel->getById($id);
        
        if (!$ordonnance) {
            setFlashMessage('error', 'Ordonnance non trouvée.');
            redirect('index.php?controller=pharmacien&action=listOrdonnances');
        }
        
        // Détecter les interactions médicamenteuses
        $medicamentIds = array_column($ordonnance['medicaments'], 'id_medicament');
        $interactions = $this->interactionModel->detecterInteractions($medicamentIds);
        
        // Récupérer les alertes existantes
        $alertes = $this->interactionModel->getAlertesByOrdonnance($id);
        
        require_once __DIR__ . '/../view/pharmacien/ordonnances/view.php';
    }
    
    /**
     * Valider une ordonnance
     */
    public function validerOrdonnance(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=pharmacien&action=listOrdonnances');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Erreur de sécurité.');
            redirect('index.php?controller=pharmacien&action=listOrdonnances');
        }
        
        $id = intval($_POST['id_ordonnance']);
        $commentaire = trim($_POST['commentaire'] ?? '');
        
        // Vérifier les interactions
        $ordonnance = $this->ordonnanceModel->getById($id);
        $medicamentIds = array_column($ordonnance['medicaments'], 'id_medicament');
        $interactions = $this->interactionModel->detecterInteractions($medicamentIds);
        
        // Créer des alertes pour les interactions détectées
        foreach ($interactions as $interaction) {
            $this->interactionModel->creerAlerte($id, $interaction['id_interaction']);
        }
        
        if ($this->ordonnanceModel->valider($id, $_SESSION['user_id'], $commentaire)) {
            logAction($_SESSION['user_id'], 'VALIDATE', 'ordonnance', $id);
            setFlashMessage('success', 'Ordonnance validée avec succès.');
            
            if (!empty($interactions)) {
                setFlashMessage('warning', count($interactions) . ' interaction(s) médicamenteuse(s) détectée(s).');
            }
        } else {
            setFlashMessage('error', 'Erreur lors de la validation.');
        }
        
        redirect('index.php?controller=pharmacien&action=viewOrdonnance&id=' . $id);
    }
    
    /**
     * Rejeter une ordonnance
     */
    public function rejeterOrdonnance(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=pharmacien&action=listOrdonnances');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Erreur de sécurité.');
            redirect('index.php?controller=pharmacien&action=listOrdonnances');
        }
        
        $id = intval($_POST['id_ordonnance']);
        $motifRejet = trim($_POST['motif_rejet'] ?? '');
        
        if (empty($motifRejet)) {
            setFlashMessage('error', 'Le motif de rejet est obligatoire.');
            redirect('index.php?controller=pharmacien&action=viewOrdonnance&id=' . $id);
        }
        
        if ($this->ordonnanceModel->rejeter($id, $_SESSION['user_id'], $motifRejet)) {
            logAction($_SESSION['user_id'], 'REJECT', 'ordonnance', $id, "Motif: $motifRejet");
            setFlashMessage('success', 'Ordonnance rejetée.');
        } else {
            setFlashMessage('error', 'Erreur lors du rejet.');
        }
        
        redirect('index.php?controller=pharmacien&action=listOrdonnances');
    }
    
    /**
     * Traiter une ordonnance (créer une transaction)
     */
    public function traiterOrdonnance(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=pharmacien&action=listOrdonnances');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Erreur de sécurité.');
            redirect('index.php?controller=pharmacien&action=listOrdonnances');
        }
        
        $id = intval($_POST['id_ordonnance']);
        $modePaiement = $_POST['mode_paiement'] ?? 'especes';
        
        $ordonnance = $this->ordonnanceModel->getById($id);
        
        if (!$ordonnance || $ordonnance['statut'] !== 'validee') {
            setFlashMessage('error', 'Ordonnance non valide pour traitement.');
            redirect('index.php?controller=pharmacien&action=listOrdonnances');
        }
        
        // Vérifier le stock pour tous les médicaments
        $stockInsuffisant = false;
        foreach ($ordonnance['medicaments'] as $med) {
            if ($med['stock_disponible'] < $med['quantite']) {
                $stockInsuffisant = true;
                break;
            }
        }
        
        if ($stockInsuffisant) {
            setFlashMessage('error', 'Stock insuffisant pour certains médicaments.');
            redirect('index.php?controller=pharmacien&action=viewOrdonnance&id=' . $id);
        }
        
        // Créer la transaction
        $transactionData = [
            'id_ordonnance' => $id,
            'id_client' => $ordonnance['id_client'],
            'id_pharmacien' => $_SESSION['user_id'],
            'montant_total' => $ordonnance['montant_total'],
            'mode_paiement' => $modePaiement,
            'statut' => 'completee'
        ];
        
        $transactionId = $this->transactionModel->create($transactionData);
        
        if ($transactionId) {
            // Mettre à jour le stock
            foreach ($ordonnance['medicaments'] as $med) {
                $this->medicamentModel->updateStock($med['id_medicament'], $med['quantite'], 'remove');
            }
            
            // Marquer l'ordonnance comme traitée
            $this->ordonnanceModel->traiter($id, $_SESSION['user_id']);
            
            logAction($_SESSION['user_id'], 'PROCESS', 'ordonnance', $id, "Transaction ID: $transactionId");
            setFlashMessage('success', 'Ordonnance traitée avec succès.');
        } else {
            setFlashMessage('error', 'Erreur lors du traitement.');
        }
        
        redirect('index.php?controller=pharmacien&action=listOrdonnances');
    }
    
    /**
     * Historique des transactions
     */
    public function historique(): void
    {
        $transactions = $this->transactionModel->getByPharmacien($_SESSION['user_id']);
        require_once __DIR__ . '/../view/pharmacien/historique.php';
    }
    
    /**
     * Consultation des médicaments
     */
    public function consultMedicaments(): void
    {
        $sortOrder = $_GET['sort'] ?? null; // 'asc' ou 'desc'
        $currency = $_GET['currency'] ?? 'EUR'; // EUR, USD, TND
        
        $medicaments = $this->medicamentModel->getAll();
        
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
        
        require_once __DIR__ . '/../view/pharmacien/medicaments/list.php';
    }
    
    /**
     * Recherche de médicaments
     */
    public function searchMedicaments(): void
    {
        $query = $_GET['q'] ?? '';
        
        if (!empty($query)) {
            $criteria = ['nom' => $query];
            $medicaments = $this->medicamentModel->search($criteria);
        } else {
            $medicaments = [];
        }
        
        require_once __DIR__ . '/../view/pharmacien/medicaments/search.php';
    }
    
    /**
     * Gestion des interactions médicamenteuses
     */
    public function interactions(): void
    {
        $interactions = $this->interactionModel->getAll();
        require_once __DIR__ . '/../view/pharmacien/interactions/list.php';
    }
    
    /**
     * Voir les détails d'une interaction
     */
    public function viewInteraction(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $interaction = $this->interactionModel->getById($id);
        
        if (!$interaction) {
            setFlashMessage('error', 'Interaction non trouvée.');
            redirect('index.php?controller=pharmacien&action=interactions');
        }
        
        require_once __DIR__ . '/../view/pharmacien/interactions/view.php';
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
            redirect('index.php?controller=pharmacien&action=listOrdonnances');
        }
        
        try {
            // Télécharger directement le PDF
            PDFGenerator::downloadOrdonnancePDF($ordonnance);
            exit;
        } catch (Exception $e) {
            setFlashMessage('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
            redirect('index.php?controller=pharmacien&action=viewOrdonnance&id=' . $id);
        }
    }
}
?>
