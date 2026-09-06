<?php
/**
 * Contrôleur d'authentification
 * Gestion de la connexion, déconnexion et inscription
 */

require_once __DIR__ . '/../model/Utilisateur.php';

class AuthController
{
    private Utilisateur $utilisateurModel;
    
    public function __construct()
    {
        $this->utilisateurModel = new Utilisateur();
    }
    
    /**
     * Afficher la page de connexion
     */
    public function login(): void
    {
        if (isLoggedIn()) {
            $this->redirectToDashboard();
        }
        require_once __DIR__ . '/../view/auth/login.php';
    }
    
    /**
     * Traiter la connexion
     */
    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=auth&action=login');
        }
        
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('error', 'Erreur de sécurité. Veuillez réessayer.');
            redirect('index.php?controller=auth&action=login');
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($email) || empty($password)) {
            setFlashMessage('error', 'Veuillez remplir tous les champs.');
            redirect('index.php?controller=auth&action=login');
        }
        
        // Vérifier les identifiants
        $user = $this->utilisateurModel->authenticate($email, $password);
        
        if ($user) {
            // Créer la session
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            
            // Mettre à jour la dernière connexion
            $this->utilisateurModel->updateLastLogin($user['id_utilisateur']);
            
            // Logger l'action
            logAction($user['id_utilisateur'], 'LOGIN', 'utilisateur', $user['id_utilisateur']);
            
            setFlashMessage('success', 'Bienvenue ' . $user['prenom'] . ' ' . $user['nom'] . ' !');
            
            // Rediriger selon le rôle
            $this->redirectToDashboard();
        } else {
            setFlashMessage('error', 'Email ou mot de passe incorrect.');
            redirect('index.php?controller=auth&action=login');
        }
    }
    
    /**
     * Afficher la page d'inscription (clients uniquement)
     */
    public function register(): void
    {
        if (isLoggedIn()) {
            $this->redirectToDashboard();
        }
        require_once __DIR__ . '/../view/auth/register.php';
    }
    
    /**
     * Traiter l'inscription (clients uniquement)
     */
    public function processRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=auth&action=register');
        }
        
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('error', 'Erreur de sécurité. Veuillez réessayer.');
            redirect('index.php?controller=auth&action=register');
        }
        
        // Récupérer les données
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (empty($nom)) $errors[] = 'Le nom est requis.';
        if (empty($prenom)) $errors[] = 'Le prénom est requis.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide.';
        }
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }
        
        if (!empty($errors)) {
            $_SESSION['register_errors'] = $errors;
            $_SESSION['register_data'] = $_POST;
            redirect('index.php?controller=auth&action=register');
        }
        
        // Vérifier si l'email existe déjà
        if ($this->utilisateurModel->emailExists($email)) {
            setFlashMessage('error', 'Cet email est déjà utilisé.');
            $_SESSION['register_data'] = $_POST;
            redirect('index.php?controller=auth&action=register');
        }
        
        // Créer le compte
        $userId = $this->utilisateurModel->create([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'adresse' => $adresse,
            'mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'client'
        ]);
        
        if ($userId) {
            setFlashMessage('success', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');
            redirect('index.php?controller=auth&action=login');
        } else {
            setFlashMessage('error', 'Une erreur est survenue lors de la création du compte.');
            redirect('index.php?controller=auth&action=register');
        }
    }
    
    /**
     * Déconnexion
     */
    public function logout(): void
    {
        if (isLoggedIn()) {
            logAction($_SESSION['user_id'], 'LOGOUT', 'utilisateur', $_SESSION['user_id']);
        }
        
        session_unset();
        session_destroy();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        setFlashMessage('success', 'Vous avez été déconnecté avec succès.');
        redirect('index.php?controller=auth&action=login');
    }
    
    /**
     * Rediriger vers le dashboard selon le rôle
     */
    private function redirectToDashboard(): void
    {
        $role = $_SESSION['user_role'] ?? 'client';
        switch ($role) {
            case 'responsable':
                redirect('index.php?controller=responsable&action=dashboard');
                break;
            case 'pharmacien':
                redirect('index.php?controller=pharmacien&action=dashboard');
                break;
            case 'client':
                redirect('index.php?controller=client&action=dashboard');
                break;
            default:
                redirect('index.php');
        }
    }
}
?>
