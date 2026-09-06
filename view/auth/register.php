<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView('register');
?>
<?php
if (!defined('DB_NAME')) {
    require_once __DIR__ . '/../../controller/config.php';
}
$pageTitle = 'Inscription';
$errors = $_SESSION['register_errors'] ?? [];
$oldData = $_SESSION['register_data'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['register_data']);
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
        
        .register-container {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 640px;
            padding: 44px 36px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 16px auto;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5);
        }
        
        .logo h1 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        
        .logo p {
            color: #94a3b8;
            font-size: 14px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #cbd5e1;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group label .required {
            color: #f87171;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 14px;
            color: white;
            transition: all 0.3s ease;
            outline: none;
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #64748b;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #818cf8;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.15);
        }
        
        .form-group input.error,
        .form-group textarea.error {
            border-color: #f87171;
        }
        
        .error-message {
            color: #f87171;
            font-size: 13px;
            margin-top: 5px;
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
        
        .alert-error ul {
            margin-left: 20px;
            margin-top: 6px;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.6);
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
        
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }
        
        .strength-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 4px;
            overflow: hidden;
        }
        
        .strength-bar-fill {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }
        
        @media (max-width: 640px) {
            .register-container {
                padding: 32px 20px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <div class="logo-icon">✨</div>
            <h1>Créer un Compte Patient</h1>
            <p>Accédez au catalogue, panier et suivi d'ordonnances</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Erreurs de validation :</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= escape($flash['message']) ?>
            </div>
        <?php endif; ?>
        
        <form action="<?= escape(appUrl('index.php')) ?>?controller=auth&action=processRegister" method="POST" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" required 
                           value="<?= escape($oldData['nom'] ?? '') ?>"
                           placeholder="Dupont">
                    <div class="error-message" id="nomError"></div>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom <span class="required">*</span></label>
                    <input type="text" id="prenom" name="prenom" required 
                           value="<?= escape($oldData['prenom'] ?? '') ?>"
                           placeholder="Marie">
                    <div class="error-message" id="prenomError"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Adresse Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" required 
                       value="<?= escape($oldData['email'] ?? '') ?>"
                       placeholder="marie.dupont@exemple.com">
                <div class="error-message" id="emailError"></div>
            </div>
            
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" 
                       value="<?= escape($oldData['telephone'] ?? '') ?>"
                       placeholder="0612345678">
                <div class="error-message" id="telephoneError"></div>
            </div>
            
            <div class="form-group">
                <label for="adresse">Adresse complète</label>
                <textarea id="adresse" name="adresse" 
                          placeholder="123 Rue Exemple, 75001 Paris"><?= escape($oldData['adresse'] ?? '') ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de passe <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required 
                           placeholder="••••••••">
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthBar"></div>
                        </div>
                        <span id="strengthText" style="margin-top: 4px; display: inline-block;"></span>
                    </div>
                    <div class="error-message" id="passwordError"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le Mot de passe <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="••••••••">
                    <div class="error-message" id="confirmPasswordError"></div>
                </div>
            </div>
            
            <button type="submit" class="btn">Créer mon Compte Client →</button>
        </form>
        
        <div class="links">
            <p>Vous possédez déjà un compte ?</p>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=auth&action=login" style="display: inline-block; margin-top: 6px;">Se connecter à votre espace</a>
        </div>
    </div>
    
    <script>
        const password = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        password.addEventListener('input', function() {
            const val = this.value;
            let strength = 0;
            
            if (val.length >= 6) strength++;
            if (val.length >= 10) strength++;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) strength++;
            if (/\d/.test(val)) strength++;
            if (/[^a-zA-Z0-9]/.test(val)) strength++;
            
            const percent = (strength / 5) * 100;
            strengthBar.style.width = percent + '%';
            
            if (strength <= 1) {
                strengthBar.style.backgroundColor = '#f87171';
                strengthText.textContent = 'Faible';
                strengthText.style.color = '#f87171';
            } else if (strength <= 3) {
                strengthBar.style.backgroundColor = '#fbbf24';
                strengthText.textContent = 'Moyen';
                strengthText.style.color = '#fbbf24';
            } else {
                strengthBar.style.backgroundColor = '#34d399';
                strengthText.textContent = 'Fort';
                strengthText.style.color = '#34d399';
            }
        });
        
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            let isValid = true;
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.querySelectorAll('input, textarea').forEach(el => el.classList.remove('error'));
            
            const nom = document.getElementById('nom');
            if (!nom.value.trim()) {
                document.getElementById('nomError').textContent = 'Le nom est requis.';
                nom.classList.add('error');
                isValid = false;
            }
            
            const prenom = document.getElementById('prenom');
            if (!prenom.value.trim()) {
                document.getElementById('prenomError').textContent = 'Le prénom est requis.';
                prenom.classList.add('error');
                isValid = false;
            }
            
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
            
            const pwd = document.getElementById('password');
            if (!pwd.value) {
                document.getElementById('passwordError').textContent = 'Le mot de passe est requis.';
                pwd.classList.add('error');
                isValid = false;
            } else if (pwd.value.length < 6) {
                document.getElementById('passwordError').textContent = 'Au moins 6 caractères requis.';
                pwd.classList.add('error');
                isValid = false;
            }
            
            const confirmPwd = document.getElementById('confirm_password');
            if (!confirmPwd.value) {
                document.getElementById('confirmPasswordError').textContent = 'Veuillez confirmer le mot de passe.';
                confirmPwd.classList.add('error');
                isValid = false;
            } else if (pwd.value !== confirmPwd.value) {
                document.getElementById('confirmPasswordError').textContent = 'Les mots de passe ne correspondent pas.';
                confirmPwd.classList.add('error');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
