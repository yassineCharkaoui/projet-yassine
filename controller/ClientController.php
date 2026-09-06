<?php
/**
 * Contrôleur Client - Gestion des fonctionnalités du client
 * - Consultation des médicaments disponibles
 * - Soumission d'ordonnances
 * - Visualisation de l'historique d'achats
 * - Demandes de renouvellement
 * - Consultation des alertes d'interactions
 */

require_once __DIR__ . '/../model/Medicament.php';
require_once __DIR__ . '/../model/Ordonnance.php';
require_once __DIR__ . '/../model/Transaction.php';
require_once __DIR__ . '/../model/DemandeRenouvellement.php';
require_once __DIR__ . '/../model/Interaction.php';
require_once __DIR__ . '/../model/Panier.php';
require_once __DIR__ . '/PDFGenerator.php';

class ClientController
{
    private Medicament $medicamentModel;
    private Ordonnance $ordonnanceModel;
    private Transaction $transactionModel;
    private DemandeRenouvellement $demandeModel;
    private Interaction $interactionModel;
    private Panier $panierModel;
    
    public function __construct()
    {
        // Vérifier que l'utilisateur est connecté et est un client
        if (!isLoggedIn() || !hasRole('client')) {
            setFlashMessage('error', 'Accès non autorisé.');
            redirect('index.php?controller=auth&action=login');
        }
        
        $this->medicamentModel = new Medicament();
        $this->ordonnanceModel = new Ordonnance();
        $this->transactionModel = new Transaction();
        $this->demandeModel = new DemandeRenouvellement();
        $this->interactionModel = new Interaction();
        $this->panierModel = new Panier();
    }
    
    /**
     * Dashboard du client
     */
    public function dashboard(): void
    {
        $data = [
            'mes_ordonnances' => $this->ordonnanceModel->getByClient($_SESSION['user_id']),
            'mes_transactions' => $this->transactionModel->getByClient($_SESSION['user_id']),
            'mes_demandes' => $this->demandeModel->getByClient($_SESSION['user_id'])
        ];
        
        require_once __DIR__ . '/../view/client/dashboard.php';
    }
    
    /**
     * Catalogue des médicaments
     */
    public function catalogue(): void
    {
        $categorie = $_GET['categorie'] ?? null;
        $sortOrder = $_GET['sort'] ?? null; // 'asc' ou 'desc'
        $currency = $_GET['currency'] ?? 'EUR'; // EUR, USD, TND
        
        if ($categorie) {
            $medicaments = $this->medicamentModel->getByCategorie($categorie);
        } else {
            $medicaments = $this->medicamentModel->getAll();
        }
        
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
        
        $categories = $this->medicamentModel->getCategories();
        require_once __DIR__ . '/../view/client/medicaments/catalogue.php';
    }
    
    /**
     * Détails d'un médicament
     */
    public function viewMedicament(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $medicament = $this->medicamentModel->getById($id);
        
        if (!$medicament) {
            setFlashMessage('error', 'Médicament non trouvé.');
            redirect('index.php?controller=client&action=catalogue');
        }
        
        // Obtenir les interactions de ce médicament
        $interactions = $this->interactionModel->getByMedicament($id);
        
        require_once __DIR__ . '/../view/client/medicaments/view.php';
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
        
        $categories = $this->medicamentModel->getCategories();
        require_once __DIR__ . '/../view/client/medicaments/search.php';
    }
    
    /**
     * Afficher le formulaire de soumission d'ordonnance
     */
    public function soumettre(): void
    {
        $medicaments = $this->medicamentModel->getAll();
        require_once __DIR__ . '/../view/client/ordonnances/soumettre.php';
    }
    
    /**
     * Traiter la soumission d'ordonnance
     */
    public function processSoumission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=client&action=soumettre');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Erreur de sécurité.');
            redirect('index.php?controller=client&action=soumettre');
        }
        
        // Validation
        $nomMedecin = trim($_POST['nom_medecin'] ?? '');
        $datePrescription = $_POST['date_prescription'] ?? '';
        $ordonnanceRenouvelable = isset($_POST['ordonnance_renouvelable']);
        
        if (empty($nomMedecin) || empty($datePrescription)) {
            setFlashMessage('error', 'Veuillez remplir tous les champs obligatoires.');
            redirect('index.php?controller=client&action=soumettre');
        }
        
        // Gérer l'upload du fichier
        $fichierScan = null;
        if (isset($_FILES['fichier_scan']) && $_FILES['fichier_scan']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../view/uploads/ordonnances/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['fichier_scan']['name'], PATHINFO_EXTENSION);
            $fileName = 'ORD_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['fichier_scan']['tmp_name'], $filePath)) {
                $fichierScan = 'view/uploads/ordonnances/' . $fileName;
            }
        }
        
        // Créer l'ordonnance
        $ordonnanceData = [
            'id_client' => $_SESSION['user_id'],
            'nom_medecin' => $nomMedecin,
            'date_prescription' => $datePrescription,
            'fichier_scan' => $fichierScan,
            'ordonnance_renouvelable' => $ordonnanceRenouvelable,
            'date_renouvellement' => $ordonnanceRenouvelable ? $_POST['date_renouvellement'] : null,
            'medicaments' => []
        ];
        
        // Ajouter les médicaments
        if (isset($_POST['medicaments']) && is_array($_POST['medicaments'])) {
            foreach ($_POST['medicaments'] as $index => $medId) {
                $medicament = $this->medicamentModel->getById($medId);
                if ($medicament) {
                    $ordonnanceData['medicaments'][] = [
                        'id_medicament' => $medId,
                        'quantite' => intval($_POST['quantites'][$index] ?? 1),
                        'posologie_prescrite' => trim($_POST['posologies'][$index] ?? ''),
                        'duree_traitement' => trim($_POST['durees'][$index] ?? ''),
                        'prix_unitaire_vente' => $medicament['prix_unitaire']
                    ];
                }
            }
        }
        
        $ordonnanceId = $this->ordonnanceModel->create($ordonnanceData);
        
        if ($ordonnanceId) {
            logAction($_SESSION['user_id'], 'CREATE', 'ordonnance', $ordonnanceId);
            setFlashMessage('success', 'Ordonnance soumise avec succès. Elle sera traitée par un pharmacien.');
            redirect('index.php?controller=client&action=mesOrdonnances');
        } else {
            setFlashMessage('error', 'Erreur lors de la soumission.');
            redirect('index.php?controller=client&action=soumettre');
        }
    }
    
    /**
     * Mes ordonnances
     */
    public function mesOrdonnances(): void
    {
        $ordonnances = $this->ordonnanceModel->getByClient($_SESSION['user_id']);
        require_once __DIR__ . '/../view/client/ordonnances/list.php';
    }
    
    /**
     * Détails d'une ordonnance
     */
    public function viewOrdonnance(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $ordonnance = $this->ordonnanceModel->getById($id);
        
        if (!$ordonnance || $ordonnance['id_client'] != $_SESSION['user_id']) {
            setFlashMessage('error', 'Ordonnance non trouvée.');
            redirect('index.php?controller=client&action=mesOrdonnances');
        }
        
        // Récupérer les alertes d'interactions
        $alertes = $this->interactionModel->getAlertesByOrdonnance($id);
        
        require_once __DIR__ . '/../view/client/ordonnances/view.php';
    }
    
    /**
     * Historique des achats
     */
    public function historique(): void
    {
        $transactions = $this->transactionModel->getByClient($_SESSION['user_id']);
        
        // Enrichir chaque transaction avec les détails des médicaments depuis transaction_detail
        foreach ($transactions as &$trans) {
            $trans['details'] = $this->transactionModel->getTransactionDetails($trans['id_transaction']);
        }
        
        require_once __DIR__ . '/../view/client/historique.php';
    }
    
    /**
     * Demander un renouvellement d'ordonnance
     */
    public function demanderRenouvellement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=client&action=mesOrdonnances');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Erreur de sécurité.');
            redirect('index.php?controller=client&action=mesOrdonnances');
        }
        
        $ordonnanceId = intval($_POST['id_ordonnance']);
        $commentaire = trim($_POST['commentaire'] ?? '');
        
        // Vérifier que l'ordonnance appartient au client
        $ordonnance = $this->ordonnanceModel->getById($ordonnanceId);
        if (!$ordonnance || $ordonnance['id_client'] != $_SESSION['user_id']) {
            setFlashMessage('error', 'Ordonnance non trouvée.');
            redirect('index.php?controller=client&action=mesOrdonnances');
        }
        
        // Vérifier si l'ordonnance est renouvelable
        if (!$ordonnance['ordonnance_renouvelable']) {
            setFlashMessage('error', 'Cette ordonnance n\'est pas renouvelable.');
            redirect('index.php?controller=client&action=viewOrdonnance&id=' . $ordonnanceId);
        }
        
        // Vérifier s'il y a déjà une demande en attente
        if ($this->demandeModel->hasDemandeEnAttente($ordonnanceId, $_SESSION['user_id'])) {
            setFlashMessage('error', 'Vous avez déjà une demande de renouvellement en attente pour cette ordonnance.');
            redirect('index.php?controller=client&action=viewOrdonnance&id=' . $ordonnanceId);
        }
        
        $demandeData = [
            'id_ordonnance_origine' => $ordonnanceId,
            'id_client' => $_SESSION['user_id'],
            'commentaire' => $commentaire
        ];
        
        $demandeId = $this->demandeModel->create($demandeData);
        
        if ($demandeId) {
            logAction($_SESSION['user_id'], 'REQUEST', 'demande_renouvellement', $demandeId);
            setFlashMessage('success', 'Demande de renouvellement soumise avec succès.');
        } else {
            setFlashMessage('error', 'Erreur lors de la soumission.');
        }
        
        redirect('index.php?controller=client&action=mesDemandes');
    }
    
    /**
     * Mes demandes de renouvellement
     */
    public function mesDemandes(): void
    {
        $demandes = $this->demandeModel->getByClient($_SESSION['user_id']);
        require_once __DIR__ . '/../view/client/demandes/list.php';
    }
    
    /**
     * Voir les alertes d'interactions
     */
    public function alertes(): void
    {
        // Récupérer toutes les ordonnances du client avec des alertes
        $ordonnances = $this->ordonnanceModel->getByClient($_SESSION['user_id']);
        $alertesParOrdonnance = [];
        
        foreach ($ordonnances as $ordonnance) {
            $alertes = $this->interactionModel->getAlertesByOrdonnance($ordonnance['id_ordonnance']);
            if (!empty($alertes)) {
                $alertesParOrdonnance[$ordonnance['id_ordonnance']] = [
                    'ordonnance' => $ordonnance,
                    'alertes' => $alertes
                ];
            }
        }
        
        require_once __DIR__ . '/../view/client/alertes.php';
    }
    
    /**
     * Ajouter un article au panier
     */
    public function addToCart(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=client&action=catalogue');
        }
        
        $idMedicament = intval($_POST['id_medicament'] ?? 0);
        $quantite = intval($_POST['quantite'] ?? 1);
        
        if ($quantite < 1) {
            $quantite = 1;
        }
        
        if ($this->panierModel->ajouterArticle($idMedicament, $quantite)) {
            setFlashMessage('success', 'Article ajouté au panier avec succès.');
        } else {
            setFlashMessage('error', 'Impossible d\'ajouter cet article au panier.');
        }
        
        // Rediriger vers la page précédente ou le panier
        $referer = $_POST['referer'] ?? 'catalogue';
        if ($referer === 'view') {
            redirect('index.php?controller=client&action=viewMedicament&id=' . $idMedicament);
        } else {
            redirect('index.php?controller=client&action=catalogue');
        }
    }
    
    /**
     * Voir le panier
     */
    public function viewCart(): void
    {
        $panier = $this->panierModel->getContenu();
        $total = $this->panierModel->getTotal();
        $nombreArticles = $this->panierModel->getNombreArticles();
        
        require_once __DIR__ . '/../view/client/panier/cart.php';
    }
    
    /**
     * Mettre à jour le panier
     */
    public function updateCart(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=client&action=viewCart');
        }
        
        if (isset($_POST['quantites']) && is_array($_POST['quantites'])) {
            foreach ($_POST['quantites'] as $idMedicament => $quantite) {
                $this->panierModel->mettreAJourQuantite((int)$idMedicament, (int)$quantite);
            }
            setFlashMessage('success', 'Panier mis à jour.');
        }
        
        redirect('index.php?controller=client&action=viewCart');
    }
    
    /**
     * Retirer un article du panier
     */
    public function removeFromCart(): void
    {
        $idMedicament = intval($_GET['id'] ?? 0);
        
        if ($this->panierModel->retirerArticle($idMedicament)) {
            setFlashMessage('success', 'Article retiré du panier.');
        } else {
            setFlashMessage('error', 'Erreur lors de la suppression.');
        }
        
        redirect('index.php?controller=client&action=viewCart');
    }
    
    /**
     * Vider le panier
     */
    public function clearCart(): void
    {
        $this->panierModel->vider();
        setFlashMessage('success', 'Panier vidé.');
        redirect('index.php?controller=client&action=viewCart');
    }
    
    /**
     * Page de paiement/checkout
     */
    public function checkout(): void
    {
        if ($this->panierModel->estVide()) {
            setFlashMessage('error', 'Votre panier est vide.');
            redirect('index.php?controller=client&action=catalogue');
        }
        
        // Valider le panier
        $erreurs = $this->panierModel->valider();
        
        if (!empty($erreurs)) {
            foreach ($erreurs as $erreur) {
                setFlashMessage('warning', $erreur);
            }
        }
        
        $panier = $this->panierModel->getContenu();
        $total = $this->panierModel->getTotal();
        
        require_once __DIR__ . '/../view/client/panier/checkout.php';
    }
    
    /**
     * Traiter le paiement
     */
    public function processCheckout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=client&action=checkout');
        }
        
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Erreur de sécurité.');
            redirect('index.php?controller=client&action=checkout');
        }
        
        if ($this->panierModel->estVide()) {
            setFlashMessage('error', 'Votre panier est vide.');
            redirect('index.php?controller=client&action=catalogue');
        }
        
        // Valider le panier
        $erreurs = $this->panierModel->valider();
        $redirectTarget = isset($_POST['from_cart']) ? 'index.php?controller=client&action=viewCart' : 'index.php?controller=client&action=checkout';
        
        if (!empty($erreurs)) {
            foreach ($erreurs as $erreur) {
                setFlashMessage('error', $erreur);
            }
            redirect($redirectTarget);
        }
        
        $modePaiement = $_POST['mode_paiement'] ?? 'especes';
        if ($modePaiement === 'espece') {
            $modePaiement = 'especes';
        }
        
        // Pour les achats sans ordonnance, on utilise un pharmacien par défaut (le premier disponible)
        // ou on peut créer un utilisateur système "Vente en ligne"
        $stmt = $this->panierModel->pdo ?? Config::getConnexion();
        $pharmacien = $stmt->query("SELECT id_utilisateur FROM utilisateur WHERE role = 'pharmacien' LIMIT 1")->fetch();
        $idPharmacien = $pharmacien ? $pharmacien['id_utilisateur'] : 1;
        
        // Créer la transaction
        $transactionId = $this->panierModel->creerTransaction(
            $_SESSION['user_id'],
            $idPharmacien,
            $modePaiement
        );
        
        if ($transactionId) {
            logAction($_SESSION['user_id'], 'PURCHASE', 'transaction', $transactionId, "Achat sans ordonnance");
            setFlashMessage('success', 'Achat effectué avec succès ! Numéro de transaction : #' . $transactionId);
            redirect('index.php?controller=client&action=historique');
        } else {
            setFlashMessage('error', 'Erreur lors du traitement du paiement.');
            redirect($redirectTarget);
        }
    }
    
    /**
     * Générer le PDF d'une ordonnance
     */
    public function generatePDF(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $ordonnance = $this->ordonnanceModel->getById($id);
        
        if (!$ordonnance || $ordonnance['id_client'] != $_SESSION['user_id']) {
            setFlashMessage('error', 'Ordonnance non trouvée.');
            redirect('index.php?controller=client&action=mesOrdonnances');
        }
        
        try {
            // Télécharger directement le PDF
            PDFGenerator::downloadOrdonnancePDF($ordonnance);
            exit;
        } catch (Exception $e) {
            setFlashMessage('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
            redirect('index.php?controller=client&action=viewOrdonnance&id=' . $id);
        }
    }
}
?>