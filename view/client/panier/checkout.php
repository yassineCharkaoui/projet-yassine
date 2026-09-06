<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Finaliser mon achat';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../../controller/CurrencyHelper.php';

$currentCurrency = $_GET['currency'] ?? 'EUR';
?>

<div class="page-header">
    <h2>💳 Finaliser mon achat</h2>
    <p>Dernière étape avant de valider votre commande</p>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Formulaire de paiement -->
    <div>
        <!-- Étapes -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
            <div style="flex: 1; text-align: center;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #4caf50; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: bold;">✓</div>
                <p style="font-size: 12px; color: #4caf50;">Panier</p>
            </div>
            <div style="flex: 1; border-top: 2px solid #667eea; margin-top: 20px;"></div>
            <div style="flex: 1; text-align: center;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #667eea; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: bold;">2</div>
                <p style="font-size: 12px; color: #667eea; font-weight: bold;">Paiement</p>
            </div>
            <div style="flex: 1; border-top: 2px solid #e0e0e0; margin-top: 20px;"></div>
            <div style="flex: 1; text-align: center;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0e0e0; color: #7f8c8d; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: bold;">3</div>
                <p style="font-size: 12px; color: #7f8c8d;">Confirmation</p>
            </div>
        </div>
        
        <form method="POST" action="<?= escape(appUrl('index.php')) ?>?controller=client&action=processCheckout">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <!-- Mode de paiement -->
            <div class="card" style="margin-bottom: 20px;">
                <h3 style="margin-bottom: 20px;">💳 Mode de paiement</h3>
                
                <div style="display: grid; gap: 15px;">
                    <!-- Carte bancaire -->
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; cursor: pointer; transition: all 0.3s;" 
                           onmouseover="this.style.borderColor='#667eea'; this.style.background='#f8f9fa'"
                           onmouseout="this.style.borderColor='#e0e0e0'; this.style.background='white'">
                        <input type="radio" name="mode_paiement" value="carte" checked style="margin-right: 15px; width: 20px; height: 20px;">
                        <div style="flex-grow: 1;">
                            <div style="font-weight: bold; font-size: 16px; margin-bottom: 5px;">💳 Carte bancaire</div>
                            <div style="font-size: 13px; color: #7f8c8d;">Visa, Mastercard, American Express</div>
                        </div>
                        <div style="font-size: 30px;">💳</div>
                    </label>
                    
                    <!-- Espèces -->
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; cursor: pointer; transition: all 0.3s;"
                           onmouseover="this.style.borderColor='#667eea'; this.style.background='#f8f9fa'"
                           onmouseout="this.style.borderColor='#e0e0e0'; this.style.background='white'">
                        <input type="radio" name="mode_paiement" value="especes" style="margin-right: 15px; width: 20px; height: 20px;">
                        <div style="flex-grow: 1;">
                            <div style="font-weight: bold; font-size: 16px; margin-bottom: 5px;">💵 Espèces</div>
                            <div style="font-size: 13px; color: #7f8c8d;">Paiement à la livraison</div>
                        </div>
                        <div style="font-size: 30px;">💵</div>
                    </label>
                    
                    <!-- Chèque -->
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; cursor: pointer; transition: all 0.3s;"
                           onmouseover="this.style.borderColor='#667eea'; this.style.background='#f8f9fa'"
                           onmouseout="this.style.borderColor='#e0e0e0'; this.style.background='white'">
                        <input type="radio" name="mode_paiement" value="cheque" style="margin-right: 15px; width: 20px; height: 20px;">
                        <div style="flex-grow: 1;">
                            <div style="font-weight: bold; font-size: 16px; margin-bottom: 5px;">📝 Chèque</div>
                            <div style="font-size: 13px; color: #7f8c8d;">Chèque bancaire</div>
                        </div>
                        <div style="font-size: 30px;">📝</div>
                    </label>
                </div>
            </div>
            
            <!-- Informations importantes -->
            <div class="card" style="margin-bottom: 20px; background: #fff3e0; border-left: 4px solid #ff9800;">
                <h4 style="color: #e65100; margin-bottom: 10px;">⚠️ Informations importantes</h4>
                <ul style="margin-left: 20px; line-height: 1.8; color: #5d4037;">
                    <li>Tous les médicaments de votre panier sont disponibles <strong>sans ordonnance</strong></li>
                    <li>Votre commande sera préparée immédiatement après validation</li>
                    <li>Le stock sera réservé dès la confirmation de paiement</li>
                    <li>Vous recevrez un email de confirmation</li>
                </ul>
            </div>
            
            <!-- Conditions générales -->
            <div class="card" style="margin-bottom: 20px;">
                <label style="display: flex; align-items: start; cursor: pointer;">
                    <input type="checkbox" required style="margin-right: 10px; margin-top: 5px; width: 18px; height: 18px;">
                    <span style="font-size: 14px; line-height: 1.6;">
                        J'accepte les <a href="#" style="color: #667eea;">conditions générales de vente</a> et 
                        la <a href="#" style="color: #667eea;">politique de confidentialité</a>
                    </span>
                </label>
            </div>
            
            <!-- Boutons -->
            <div style="display: flex; gap: 15px;">
                <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewCart" class="btn btn-secondary" style="flex: 1;">
                    ← Retour au panier
                </a>
                <button type="button" onclick="openPaymentModal()" class="btn btn-primary" style="flex: 2; padding: 15px; font-size: 18px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                    ✓ Passer au Paiement (Popup) →
                </button>
            </div>
        </form>
    </div>
    
    <!-- Résumé de la commande -->
    <div>
        <div class="card" style="position: sticky; top: 20px;">
            <h3 style="margin-bottom: 20px;">📦 Résumé de la commande</h3>
            
            <!-- Articles -->
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; padding-right: 10px;">
                <?php foreach ($panier as $article): ?>
                <div style="display: flex; gap: 10px; padding: 10px; border-bottom: 1px solid #f0f0f0;">
                    <div style="flex-shrink: 0;">
                        <?php if (!empty($article['image'])): ?>
                            <img src="<?= escape(appUrl('view/uploads/')) ?>medicaments/<?= escape($article['image']) ?>" 
                                 alt="<?= escape($article['nom_commercial']) ?>"
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        <?php else: ?>
                            <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 20px;">📦</div>
                        <?php endif; ?>
                    </div>
                    <div style="flex-grow: 1;">
                        <div style="font-size: 14px; font-weight: bold; margin-bottom: 3px;">
                            <?= escape($article['nom_commercial']) ?>
                        </div>
                        <div style="font-size: 12px; color: #7f8c8d;">
                            Qté: <?= $article['quantite'] ?> × <?= CurrencyHelper::convertAndFormat($article['prix_unitaire'], $currentCurrency) ?>
                        </div>
                    </div>
                    <div style="text-align: right; font-weight: bold; color: #667eea;">
                        <?= CurrencyHelper::convertAndFormat($article['prix_unitaire'] * $article['quantite'], $currentCurrency) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Total -->
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 20px; border-radius: 10px; color: white;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Total à payer</p>
                        <p style="font-size: 32px; font-weight: bold; margin: 0;">
                            <?= CurrencyHelper::convertAndFormat($total, $currentCurrency) ?>
                        </p>
                        <?php if ($currentCurrency !== 'EUR'): ?>
                            <p style="font-size: 12px; opacity: 0.8; margin-top: 5px;">
                                (<?= number_format($total, 2) ?> €)
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sécurité -->
            <div style="margin-top: 20px; padding: 15px; background: #e8f5e9; border-radius: 8px; text-align: center;">
                <p style="font-size: 12px; color: #2e7d32; margin: 0;">
                    🔒 Paiement 100% sécurisé<br>
                    🚚 Livraison rapide<br>
                    ✓ Satisfaction garantie
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Popup de Paiement -->
<div id="paymentModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: var(--card-bg, #ffffff); border-radius: 20px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid var(--card-border); overflow: hidden; animation: modalPop 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <!-- En-tête du Modal -->
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); padding: 24px; color: white; position: relative;">
            <button type="button" onclick="closePaymentModal()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.15); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center;">✕</button>
            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #818cf8; margin-bottom: 4px;">Confirmation de commande</div>
            <h3 style="margin: 0; font-size: 22px; font-weight: 800; color: white; display: flex; align-items: center; gap: 8px;">
                💳 Choix du Paiement
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

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-dark); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Sélectionnez votre moyen de paiement :
                </label>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <!-- Option Espèces -->
                    <label id="labelEspecesModal" style="display: flex; flex-direction: column; align-items: center; text-align: center; padding: 18px 12px; border: 2px solid var(--primary); border-radius: 14px; cursor: pointer; background: rgba(79, 70, 229, 0.06); transition: all 0.2s ease;">
                        <input type="radio" name="mode_paiement" value="especes" checked onchange="togglePaymentFieldsModal('especes')" style="display: none;">
                        <div style="font-size: 36px; margin-bottom: 6px;">💵</div>
                        <div style="font-weight: 800; font-size: 15px; color: var(--text-dark);">Espèces</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px; line-height: 1.3;">À la livraison / au retrait</div>
                    </label>

                    <!-- Option Carte Bancaire -->
                    <label id="labelCarteModal" style="display: flex; flex-direction: column; align-items: center; text-align: center; padding: 18px 12px; border: 2px solid var(--card-border); border-radius: 14px; cursor: pointer; background: var(--card-bg); transition: all 0.2s ease;">
                        <input type="radio" name="mode_paiement" value="carte" onchange="togglePaymentFieldsModal('carte')" style="display: none;">
                        <div style="font-size: 36px; margin-bottom: 6px;">💳</div>
                        <div style="font-weight: 800; font-size: 15px; color: var(--text-dark);">Carte Bancaire</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px; line-height: 1.3;">Visa, MasterCard, CB</div>
                    </label>
                </div>
            </div>

            <!-- Panneau Informations Espèces -->
            <div id="infoEspecesModal" style="display: block; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 14px 16px; border-radius: 12px; color: #065f46; font-size: 13px; margin-bottom: 20px; line-height: 1.5;">
                <div style="font-weight: 700; display: flex; align-items: center; gap: 6px; margin-bottom: 4px; font-size: 14px;">
                    💵 Paiement en espèces
                </div>
                Vous réglerez la somme exacte de <strong><?= CurrencyHelper::convertAndFormat($total, $currentCurrency) ?></strong> directement lors de la livraison ou du retrait au comptoir de la pharmacie.
            </div>

            <!-- Panneau Champs Carte Bancaire -->
            <div id="infoCarteModal" style="display: none; margin-bottom: 20px;">
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
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 14px; font-weight: 800; font-size: 16px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; border-radius: 12px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                    ✓ Confirmer le Paiement
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
    document.getElementById('paymentModal').style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function togglePaymentFieldsModal(type) {
    const labelEspeces = document.getElementById('labelEspecesModal');
    const labelCarte = document.getElementById('labelCarteModal');
    const infoEspeces = document.getElementById('infoEspecesModal');
    const infoCarte = document.getElementById('infoCarteModal');

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

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
