<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView('login');
?>
<?php
if (!defined('DB_NAME')) {
    require_once __DIR__ . '/../../controller/config.php';
}
$pageTitle = 'Connexion';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($pageTitle) ?> - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311b92 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #fff;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 460px;
            padding: 44px 36px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 16px auto;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.5);
        }
        
        .logo h1 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }
        
        .logo p {
            color: #94a3b8;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #cbd5e1;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 13px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 15px;
            color: white;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .form-group input::placeholder {
            color: #64748b;
        }
        
        .form-group input:focus {
            border-color: #818cf8;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.15);
        }
        
        .form-group input.error {
            border-color: #f87171;
        }
        
        .error-message {
            color: #f87171;
            font-size: 13px;
            margin-top: 6px;
            font-weight: 500;
        }
        
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .alert-error {
            background-color: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.6);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .links {
            text-align: center;
            margin-top: 24px;
            color: #94a3b8;
            font-size: 14px;
        }
        
        .links a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 700;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .divider {
            text-align: center;
            margin: 24px 0;
            color: #64748b;
            position: relative;
            font-size: 13px;
            font-weight: 600;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .divider::before { left: 0; }
        .divider::after { right: 0; }

        .demo-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }

        .demo-box p {
            margin-bottom: 4px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 32px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">💊</div>
            <h1>Espace Connexion</h1>
            <p><?= APP_NAME ?></p>
        </div>
        
        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= escape($flash['message']) ?>
            </div>
        <?php endif; ?>
        
        <form action="<?= escape(appUrl('index.php')) ?>?controller=auth&action=processLogin" method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="form-group">
                <label for="email">Adresse Email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="votre.email@exemple.com">
                <div class="error-message" id="emailError"></div>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required 
                       placeholder="••••••••">
                <div class="error-message" id="passwordError"></div>
            </div>
            
            <button type="submit" class="btn">Se connecter →</button>
        </form>
        
        <div class="divider">OU</div>
        
        <div class="links">
            <p>Vous n'avez pas encore de compte ?</p>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=auth&action=register" style="display: inline-block; margin-top: 6px;">Créer un compte client patient</a>
        </div>
        
        <div class="demo-box">
            <p style="font-weight: 700; color: #818cf8; margin-bottom: 6px;">💡 Comptes de Démo Rapide :</p>
            <p><strong>Responsable:</strong> responsable@pharmacie.com</p>
            <p><strong>Pharmacien:</strong> marie.dupont@pharmacie.com</p>
            <p><strong>Client:</strong> sophie.dubois@email.com</p>
            <p style="margin-top: 4px;">Mot de passe unique: <strong style="color: white;">password123</strong></p>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let isValid = true;
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.querySelectorAll('input').forEach(el => el.classList.remove('error'));
            
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim()) {
                document.getElementById('emailError').textContent = 'L\'email est requis.';
                email.classList.add('error');
                isValid = false;
            } else if (!emailRegex.test(email.value)) {
                document.getElementById('emailError').textContent = 'Format d\'email invalide.';
                email.classList.add('error');
                isValid = false;
            }
            
            const password = document.getElementById('password');
            if (!password.value) {
                document.getElementById('passwordError').textContent = 'Le mot de passe est requis.';
                password.classList.add('error');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
