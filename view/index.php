<?php
/**
 * Point d'entrée MVC - Routeur sous view/
 * Système de Gestion de Pharmacie - PHP 8
 */

// Charger la configuration globale et la session
require_once __DIR__ . '/../controller/config.php';

// Entêtes de sécurité HTTP
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Explicit allowlist excludes constructors and internal methods.
$routes = [
    'auth' => ['login', 'processLogin', 'register', 'processRegister', 'logout'],
    'client' => ['dashboard', 'catalogue', 'viewMedicament', 'searchMedicaments', 'soumettre', 'processSoumission', 'mesOrdonnances', 'viewOrdonnance', 'historique', 'demanderRenouvellement', 'mesDemandes', 'alertes', 'addToCart', 'viewCart', 'updateCart', 'removeFromCart', 'clearCart', 'checkout', 'processCheckout', 'generatePDF'],
    'pharmacien' => ['dashboard', 'listOrdonnances', 'viewOrdonnance', 'validerOrdonnance', 'rejeterOrdonnance', 'traiterOrdonnance', 'historique', 'consultMedicaments', 'searchMedicaments', 'interactions', 'viewInteraction', 'addInteraction', 'editInteraction', 'saveInteraction', 'deleteInteraction', 'generatePDF'],
    'responsable' => ['dashboard', 'listMedicaments', 'addMedicament', 'processAddMedicament', 'editMedicament', 'processEditMedicament', 'deleteMedicament', 'searchMedicaments', 'rapportStockCritique', 'listDemandes', 'approuverDemande', 'rejeterDemande', 'listUtilisateurs', 'rapports', 'generatePDF'],
];
$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';
try {
    if (!is_string($controller) || !is_string($action) || !isset($routes[$controller]) || !in_array($action, $routes[$controller], true)) {
        throw new RuntimeException('PAGE_NOT_FOUND', 404);
    }
    if (!isLoggedIn() && ($controller !== 'auth' || !in_array($action, ['login', 'register', 'processLogin', 'processRegister'], true))) {
        redirect(buildUrl('auth', 'login'));
    }
    if (isLoggedIn() && $controller !== 'auth' && !hasRole($controller)) throw new RuntimeException('ACCESS_DENIED', 403);
    if (isLoggedIn() && $controller === 'auth' && in_array($action, ['login', 'register'], true)) {
        $role = $_SESSION['user_role'];
        if (!in_array($role, ['client', 'pharmacien', 'responsable'], true)) throw new RuntimeException('ACCESS_DENIED', 403);
        redirect(buildUrl($role, 'dashboard'));
    }
    define('APP_ROUTED', true);
    $controllerName = ucfirst($controller) . 'Controller';
    require_once __DIR__ . '/../controller/' . $controllerName . '.php';
    $controllerInstance = new $controllerName();
    $controllerInstance->$action();

} catch (PDOException $e) {
    // Catch spécifique pour les erreurs de base de données (MySQL non démarré ou DB inexistante)
    error_log("[DB Error] " . $e->getMessage());
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur Base de Données - <?= escape(APP_NAME) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
            body { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311b92 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; color: #fff; }
            .error-card { background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.18); padding: 50px 40px; border-radius: 24px; text-align: center; max-width: 580px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
            .error-icon { font-size: 64px; margin-bottom: 15px; }
            .error-title { font-size: 24px; font-weight: 700; margin-bottom: 12px; color: #f8fafc; }
            .error-desc { color: #94a3b8; font-size: 15px; margin-bottom: 25px; line-height: 1.6; }
            .solution-box { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 16px; text-align: left; margin-bottom: 25px; font-size: 14px; color: #fca5a5; }
            .solution-box ul { margin-left: 20px; margin-top: 8px; }
            .btn-action { display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; text-decoration: none; font-weight: 600; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4); transition: all 0.3s ease; margin: 5px; }
            .btn-action:hover { transform: translateY(-2px); }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">⚠️</div>
            <h2 class="error-title">Erreur de Connexion MySQL</h2>
            <p class="error-desc">Impossible d'établir une connexion avec le serveur de base de données MySQL.</p>
            <div class="solution-box">
                <strong>🛠️ Solutions rapides :</strong>
                <ul>
                    <li>Ouvrez <strong>XAMPP Control Panel</strong> et cliquez sur <strong>Start</strong> à côté de <strong>MySQL</strong>.</li>
                    <li>Vérifiez les paramètres de connexion dans la configuration.</li>
                </ul>
            </div>
            <div>
                <a href="<?= escape(buildUrl('auth', 'login')) ?>" class="btn-action">Réessayer</a>
                <a href="<?= escape(buildUrl('auth', 'login')) ?>" class="btn-action">Réessayer</a>
            </div>
        </div>
    </body>
    </html>
    <?php
} catch (Throwable $e) {
    // Erreurs 404 ou autres exceptions système
    error_log("[Router Error] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    $status = in_array($e->getCode(), [403, 404], true) ? $e->getCode() : 500;
    http_response_code($status);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur <?= $status ?> - <?= escape(APP_NAME) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
            body { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311b92 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; color: #fff; }
            .error-card { background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.18); padding: 50px 40px; border-radius: 24px; text-align: center; max-width: 520px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
            .error-code { font-size: 84px; font-weight: 800; background: linear-gradient(135deg, #818cf8 0%, #38bdf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; margin-bottom: 10px; }
            .error-title { font-size: 22px; font-weight: 700; margin-bottom: 12px; color: #f8fafc; }
            .error-desc { color: #94a3b8; font-size: 15px; margin-bottom: 30px; line-height: 1.6; }
            .btn-home { display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; text-decoration: none; font-weight: 600; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4); transition: all 0.3s ease; }
            .btn-home:hover { transform: translateY(-2px); box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.6); }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-code"><?= $status ?></div>
            <h2 class="error-title"><?= $status === 404 ? 'Page introuvable' : ($status === 403 ? 'Accès refusé' : 'Erreur interne') ?></h2>
            <p class="error-desc"><?= $status === 500 ? 'Une erreur interne est survenue. Consultez les journaux PHP.' : 'La ressource demandée est indisponible ou inaccessible.' ?></p>
            <?php if (isLoggedIn()): ?>
                <a href="<?= escape(appUrl('index.php')) ?>?controller=<?= $_SESSION['user_role'] ?>&action=dashboard" class="btn-home">
                    📊 Retour à mon Tableau de bord
                </a>
            <?php else: ?>
                <a href="<?= escape(appUrl('index.php')) ?>?controller=auth&action=login" class="btn-home">
                    🔑 Retour à la Page de Connexion
                </a>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}
