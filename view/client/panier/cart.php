<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Mon Panier';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../../controller/CurrencyHelper.php';

$currentCurrency = $_GET['currency'] ?? 'EUR';
?>

<div class="page-header">
    <div>
        <h2>🛒 Mon Panier</h2>
        <p>Gérez les articles sélectionnés avant de passer au règlement</p>
    </div>
    <div>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue" class="btn btn-secondary">
            ← Continuer mes achats
        </a>
    </div>
</div>

<?php if (empty($panier)): ?>
<!-- Panier vide -->
<div class="card">
    <div style="text-align: center; padding: 70px 20px;">
        <div style="font-size: 80px; margin-bottom: 16px;">🛒</div>
        <h3 style="color: var(--text-dark); margin-bottom: 8px; font-weight: 800;">Votre panier est actuellement vide</h3>
        <p style="color: var(--text-muted); margin-bottom: 24px; max-width: 480px; margin-left: auto; margin-right: auto;">
            Explorez notre catalogue de médicaments sans prescription et ajoutez des produits en quelques clics.
        </p>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue" class="btn btn-primary" style="padding: 14px 32px; font-size: 15px;">
            📖 Explorer le Catalogue
        </a>
    </div>
</div>

<?php else: ?>
<!-- Sélecteur de devise -->
<div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: white;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <span style="font-weight: 700; font-size: 14px;">💱 Devise d'affichage :</span>
        <div style="display: flex; gap: 8px;">
            <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewCart&currency=EUR" 
               class="btn <?= $currentCurrency === 'EUR' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                € EUR
            </a>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewCart&currency=USD" 
               class="btn <?= $currentCurrency === 'USD' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                $ USD
            </a>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewCart&currency=TND" 
               class="btn <?= $currentCurrency === 'TND' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                د.ت TND
            </a>
        </div>
    </div>
</div>

<!-- Contenu du panier -->
<div class="form-row" style="grid-template-columns: 2fr 1fr; align-items: flex-start;">
    <!-- Articles -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3>📦 Articles dans le panier (<?= $nombreArticles ?>)</h3>
            </div>
            
            <form method="POST" action="<?= escape(appUrl('index.php')) ?>?controller=client&action=updateCart">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <?php foreach ($panier as $idMedicament => $article): ?>
                <div style="display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--card-border); align-items: center; flex-wrap: wrap;">
                    <!-- Image -->
                    <div style="flex-shrink: 0;">
                        <?php if (!empty($article['image'])): ?>
                            <img src="<?= escape(appUrl('view/uploads/')) ?>medicaments/<?= escape($article['image']) ?>" 
                                 alt="<?= escape($article['nom_commercial']) ?>"
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 12px; border: 1px solid var(--card-border);"
                                 onerror="this.parentElement.innerHTML='<div style=\'width:80px;height:80px;background:#f8fafc;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:36px;\'>📦</div>'">
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 36px; border: 1px solid var(--card-border);">📦</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Informations -->
                    <div style="flex: 2; min-width: 180px;">
                        <h4 style="color: var(--primary); margin-bottom: 4px; font-weight: 800; font-size: 16px;">
                            <?= escape($article['nom_commercial']) ?>
                        </h4>
                        <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 6px;">
                            <?= escape($article['nom_generique'] ?? '') ?>
                        </p>
                        <div style="font-size: 16px; font-weight: 800; color: var(--text-dark);">
                            <?= CurrencyHelper::convertAndFormat($article['prix_unitaire'], $currentCurrency) ?>
                            <?php if ($currentCurrency !== 'EUR'): ?>
                                <span style="font-size: 12px; color: var(--text-muted); font-weight: normal;">
                                    (<?= number_format($article['prix_unitaire'], 2) ?> €)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quantité -->
                    <div style="text-align: center;">
                        <label style="display: block; margin-bottom: 4px; font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Qté</label>
                        <input type="number" 
                               name="quantites[<?= $idMedicament ?>]" 
                               value="<?= $article['quantite'] ?>" 
                               min="1" 
                               max="<?= $article['stock_disponible'] ?>"
                               class="form-control"
                               style="width: 75px; text-align: center; padding: 6px; font-weight: 700;">
                    </div>
                    
                    <!-- Sous-total -->
                    <div style="text-align: right; min-width: 100px;">
                        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Sous-total</div>
                        <div style="font-size: 18px; font-weight: 800; color: var(--primary);">
                            <?= CurrencyHelper::convertAndFormat($article['prix_unitaire'] * $article['quantite'], $currentCurrency) ?>
                        </div>
                    </div>
                    
                    <!-- Supprimer -->
                    <div>
                        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=removeFromCart&id=<?= $idMedicament ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Retirer cet article du panier ?')"
                           title="Supprimer">
                            🗑️
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Boutons d'action -->
                <div style="display: flex; justify-content: space-between; gap: 12px; margin-top: 20px; flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary">
                        🔄 Mettre à jour le panier
                    </button>
                    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=clearCart" 
                       class="btn btn-secondary"
                       onclick="return confirm('Vider complètement le panier ?')">
                        🗑️ Vider le panier
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Résumé -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3>💰 Résumé de la commande</h3>
            </div>
            
            <div style="border-bottom: 1px solid var(--card-border); padding-bottom: 15px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: var(--text-muted);">Nombre d'articles :</span>
                    <span style="font-weight: 700;"><?= $nombreArticles ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Sous-total :</span>
                    <span style="font-weight: 700; font-size: 16px; color: var(--text-dark);">
                        <?= CurrencyHelper::convertAndFormat($total, $currentCurrency) ?>
                    </span>
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 20px; border-radius: var(--radius-md); color: white; margin-bottom: 20px; text-align: center;">
                <div style="font-size: 13px; opacity: 0.9; margin-bottom: 4px; font-weight: 700; text-transform: uppercase;">Total Général</div>
                <div style="font-size: 32px; font-weight: 800;">
                    <?= CurrencyHelper::convertAndFormat($total, $currentCurrency) ?>
                </div>
                <?php if ($currentCurrency !== 'EUR'): ?>
                    <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">
                        (<?= number_format($total, 2) ?> €)
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="button" 
                    onclick="openPaymentModal()" 
                    class="btn btn-primary" 
                    style="width: 100%; padding: 16px; font-size: 17px; font-weight: 800; justify-content: center; border: none; background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%); box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4); cursor: pointer;">
                ⚡ Passer au Paiement →
            </button>
            
            <div style="margin-top: 20px; padding: 16px; background: #dcfce7; border-radius: var(--radius-md); border: 1px solid #bbf7d0; color: #15803d; font-size: 13px;">
                <strong>🛡️ Garanties Pharmacie :</strong>
                <ul style="margin-left: 18px; margin-top: 6px;">
                    <li>Paiement 100% sécurisé (Espèces ou Carte)</li>
                    <li>Retrait en pharmacie / Livraison express</li>
                    <li>Conformité réglementaire Santé</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal Popup de Choix du Mode de Paiement -->
<div id="paymentModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: var(--card-bg, #ffffff); border-radius: 20px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid var(--card-border); overflow: hidden; animation: modalPop 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <!-- En-tête du Modal -->
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); padding: 24px; color: white; position: relative;">
            <button type="button" onclick="closePaymentModal()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.15); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">✕</button>
            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #818cf8; margin-bottom: 4px;">Confirmation de commande</div>
            <h3 style="margin: 0; font-size: 22px; font-weight: 800; color: white; display: flex; align-items: center; gap: 8px;">
                💳 Mode de Paiement
            </h3>
            <div style="margin-top: 14px; display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.1); padding: 12px 18px; border-radius: 12px; backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.15);">
                <span style="font-size: 13px; color: #cbd5e1; font-weight: 600;">Montant total à régler :</span>
                <span style="font-size: 22px; font-weight: 800; color: #34d399;">
                    <?= CurrencyHelper::convertAndFormat($total, $currentCurrency) ?>
                </span>
            </div>
        </div>

        <!-- Formulaire de Paiement -->
        <form method="POST" action="<?= escape(appUrl('index.php')) ?>?controller=client&action=processCheckout" id="paymentModalForm" style="padding: 24px;">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="from_cart" value="1">

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-dark); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Sélectionnez votre moyen de paiement :
                </label>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <!-- Option Espèces -->
                    <label id="labelEspeces" style="display: flex; flex-direction: column; align-items: center; text-align: center; padding: 18px 12px; border: 2px solid var(--primary); border-radius: 14px; cursor: pointer; background: rgba(79, 70, 229, 0.06); transition: all 0.2s ease;">
                        <input type="radio" name="mode_paiement" value="especes" checked onchange="togglePaymentFields('especes')" style="display: none;">
                        <div style="font-size: 36px; margin-bottom: 6px;">💵</div>
                        <div style="font-weight: 800; font-size: 15px; color: var(--text-dark);">Espèces</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px; line-height: 1.3;">À la livraison / au retrait</div>
                    </label>

                    <!-- Option Carte Bancaire -->
                    <label id="labelCarte" style="display: flex; flex-direction: column; align-items: center; text-align: center; padding: 18px 12px; border: 2px solid var(--card-border); border-radius: 14px; cursor: pointer; background: var(--card-bg); transition: all 0.2s ease;">
                        <input type="radio" name="mode_paiement" value="carte" onchange="togglePaymentFields('carte')" style="display: none;">
                        <div style="font-size: 36px; margin-bottom: 6px;">💳</div>
                        <div style="font-weight: 800; font-size: 15px; color: var(--text-dark);">Carte Bancaire</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px; line-height: 1.3;">Visa, MasterCard, CB</div>
                    </label>
                </div>
            </div>

            <!-- Panneau Informations Espèces -->
            <div id="infoEspeces" style="display: block; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 14px 16px; border-radius: 12px; color: #065f46; font-size: 13px; margin-bottom: 20px; line-height: 1.5;">
                <div style="font-weight: 700; display: flex; align-items: center; gap: 6px; margin-bottom: 4px; font-size: 14px;">
                    💵 Paiement en espèces
                </div>
                Vous réglerez la somme exacte de <strong><?= CurrencyHelper::convertAndFormat($total, $currentCurrency) ?></strong> directement lors de la livraison ou du retrait au comptoir de la pharmacie.
            </div>

            <!-- Panneau Champs Carte Bancaire -->
            <div id="infoCarte" style="display: none; margin-bottom: 20px;">
                <div style="background: var(--bg-body, #f8fafc); border: 1px solid var(--card-border); padding: 16px; border-radius: 14px; margin-bottom: 10px;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Titulaire de la carte</label>
                        <input type="text" class="form-control" placeholder="M. / Mme. Nom Prénom" style="width: 100%; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Numéro de carte</label>
                        <input type="text" class="form-control" placeholder="4532 •••• •••• 8892" maxlength="19" style="width: 100%; letter-spacing: 2px; font-family: monospace; border-radius: 8px;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Expiration</label>
                            <input type="text" class="form-control" placeholder="MM/AA" maxlength="5" style="width: 100%; text-align: center; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">CVC / CVV</label>
                            <input type="password" class="form-control" placeholder="123" maxlength="4" style="width: 100%; text-align: center; border-radius: 8px;">
                        </div>
                    </div>
                </div>
                <div style="font-size: 12px; color: #166534; background: #f0fdf4; padding: 10px; border-radius: 8px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 6px;">
                    🔒 Paiement SSL sécurisé 256-bit crypté.
                </div>
            </div>

            <!-- Boutons du Modal -->
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" onclick="closePaymentModal()" class="btn btn-secondary" style="flex: 1; padding: 14px; font-weight: 700; border-radius: 12px;">
                    Annuler
                </button>
                <button type="submit" id="btnConfirmPayment" class="btn btn-primary" style="flex: 2; padding: 14px; font-weight: 800; font-size: 16px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; border-radius: 12px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                    ✓ Confirmer et Payer
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.92) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<script>
function openPaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function togglePaymentFields(type) {
    const labelEspeces = document.getElementById('labelEspeces');
    const labelCarte = document.getElementById('labelCarte');
    const infoEspeces = document.getElementById('infoEspeces');
    const infoCarte = document.getElementById('infoCarte');

    if (type === 'especes') {
        labelEspeces.style.borderColor = 'var(--primary)';
        labelEspeces.style.background = 'rgba(79, 70, 229, 0.08)';
        
        labelCarte.style.borderColor = 'var(--card-border)';
        labelCarte.style.background = 'var(--card-bg)';
        
        infoEspeces.style.display = 'block';
        infoCarte.style.display = 'none';
    } else {
        labelCarte.style.borderColor = 'var(--primary)';
        labelCarte.style.background = 'rgba(79, 70, 229, 0.08)';
        
        labelEspeces.style.borderColor = 'var(--card-border)';
        labelEspeces.style.background = 'var(--card-bg)';
        
        infoCarte.style.display = 'block';
        infoEspeces.style.display = 'none';
    }
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target === modal) {
        closePaymentModal();
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
