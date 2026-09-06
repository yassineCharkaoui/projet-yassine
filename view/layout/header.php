<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView();
?>
<?php
if (!defined('DB_NAME')) {
    require_once __DIR__ . '/../../controller/config.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' - ' : '' ?><?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #e0e7ff;
            --secondary: #06b6d4;
            --accent: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --bg-main: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --card-border: #e2e8f0;
            --radius-lg: 16px;
            --radius-md: 10px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.06);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
            line-height: 1.5;
            overflow-x: hidden;
        }

        /* Responsive Navigation Bar */
        .navbar {
            background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            gap: 10px;
        }

        .navbar-brand h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-brand h1 span {
            color: #818cf8;
        }

        .navbar-menu {
            display: flex;
            list-style: none;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .navbar-menu li a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 10px 16px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            border-radius: var(--radius-md);
            transition: var(--transition);
        }

        .navbar-menu li a:hover,
        .navbar-menu li a.active {
            color: white;
            background: rgba(255, 255, 255, 0.12);
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 700;
            font-size: 14px;
            color: white;
        }

        .user-role {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Buttons Design System */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(79, 70, 229, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(239, 68, 68, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(16, 185, 129, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 13px;
            border-radius: 8px;
        }

        /* Container Layout */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        .page-header {
            margin-bottom: 28px;
            background: white;
            padding: 24px 28px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--card-border);
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header h2 {
            color: var(--text-dark);
            font-size: 24px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 4px;
        }

        /* Flash Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }

        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .alert-info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }

        /* Cards & Containers */
        .card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--card-border);
            box-shadow: var(--shadow-sm);
            padding: 24px;
            margin-bottom: 24px;
            transition: var(--transition);
        }

        .card-header {
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 16px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .card-header h3 {
            color: var(--text-dark);
            font-size: 18px;
            font-weight: 700;
        }

        /* Responsive Table Wrapper */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: var(--radius-md);
            border: 1px solid var(--card-border);
            margin-top: 16px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: white;
        }

        .table thead {
            background: #f8fafc;
        }

        .table th,
        .table td {
            padding: 14px 18px;
            text-align: left;
            border-bottom: 1px solid var(--card-border);
            white-space: nowrap;
        }

        .table th {
            font-weight: 700;
            color: var(--text-muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-secondary { background: #f1f5f9; color: #475569; }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: var(--radius-md);
            font-size: 14px;
            transition: var(--transition);
            background: white;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Grid Utilities */
        .grid-responsive-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .grid-responsive-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .grid-responsive-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .container {
                padding: 20px 16px;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 768px) {
            .navbar-container {
                flex-direction: column;
                align-items: flex-start;
                padding: 16px;
            }
            
            .navbar-menu {
                width: 100%;
                justify-content: flex-start;
            }
            
            .navbar-menu li a {
                padding: 8px 12px;
                font-size: 13px;
            }

            .navbar-user {
                width: 100%;
                justify-content: space-between;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                padding-top: 12px;
                margin-top: 6px;
            }

            .user-info {
                text-align: left;
            }

            .card {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?= escape(appUrl('index.php')) ?>" class="navbar-brand">
                <h1>🏥 <span><?= APP_NAME ?></span></h1>
            </a>
            
            <ul class="navbar-menu">
                <?php if (hasRole('responsable')): ?>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=dashboard">📊 Dashboard</a></li>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listMedicaments">💊 Médicaments</a></li>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listDemandes">📋 Demandes</a></li>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=rapports">📈 Rapports</a></li>
                    <li><a href="<?= escape(buildUrl('responsable', 'listUtilisateurs')) ?>">Utilisateurs</a></li>
                <?php elseif (hasRole('pharmacien')): ?>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=dashboard">📊 Dashboard</a></li>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances">📝 Ordonnances</a></li>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=consultMedicaments">💊 Médicaments</a></li>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=historique">📜 Historique</a></li>
                    <li><a href="<?= escape(buildUrl('pharmacien', 'interactions')) ?>">Interactions</a></li>
                <?php elseif (hasRole('client')): ?>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=dashboard">📊 Dashboard</a></li>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue">📦 Catalogue</a></li>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=mesOrdonnances">📝 Ordonnances</a></li>
                    <li><a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=historique">📜 Historique</a></li>
                    <li><a href="<?= escape(buildUrl('client', 'mesDemandes')) ?>">Mes demandes</a></li>
                    <li><a href="<?= escape(buildUrl('client', 'alertes')) ?>">Mes alertes</a></li>
                    <li>
                        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewCart" style="position: relative;">
                            🛒 Panier
                            <?php
                            if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])):
                                $nbArticles = array_sum(array_column($_SESSION['panier'], 'quantite'));
                                if ($nbArticles > 0):
                            ?>
                                <span style="position: absolute; top: -4px; right: -6px; background: var(--danger); color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800;">
                                    <?= $nbArticles > 9 ? '9+' : $nbArticles ?>
                                </span>
                            <?php 
                                endif;
                            endif;
                            ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <div class="navbar-user">
                <div class="user-info">
                    <div class="user-name"><?= escape($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></div>
                    <div class="user-role"><?= escape(ucfirst($_SESSION['user_role'])) ?></div>
                </div>
                <a href="<?= escape(appUrl('index.php')) ?>?controller=auth&action=logout" class="btn btn-danger btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= escape($flash['message']) ?>
            </div>
        <?php endif; ?>
