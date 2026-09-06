<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Historique de mes Achats';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../../controller/CurrencyHelper.php';

$currentCurrency = $_GET['currency'] ?? 'EUR';
$totalDepense = !empty($transactions) ? array_sum(array_column($transactions, 'montant_total')) : 0;
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <div>
        <h2 style="font-size: 26px; font-weight: 800; color: var(--text-dark); margin-bottom: 4px;">
            📜 Historique de mes Achats
        </h2>
        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
            Consultez le détail et le suivi de toutes vos transactions en officine
        </p>
    </div>
    
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <!-- Bouton Imprimer -->
        <button onclick="window.print()" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 6px;">
            🖨️ Imprimer
        </button>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue" class="btn btn-primary btn-sm">
            📖 NOUVEL ACHAT
        </a>
    </div>
</div>

<?php if (empty($transactions)): ?>
<div class="card" style="text-align: center; padding: 70px 20px; border-radius: 20px;">
    <div style="font-size: 70px; margin-bottom: 16px; opacity: 0.8;">🛒</div>
    <h3 style="color: var(--text-dark); margin-bottom: 8px; font-weight: 800;">Aucun achat enregistré pour le moment</h3>
    <p style="color: var(--text-muted); margin-bottom: 24px; max-width: 460px; margin-left: auto; margin-right: auto; font-size: 14px;">
        Retrouvez ici l'ensemble de vos règlements en ligne et ordonnances délivrées une fois votre première commande effectuée.
    </p>
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px; font-weight: 700;">
        🛍️ Découvrir le Catalogue
    </a>
</div>
<?php else: ?>

<!-- Sélecteur de Devise & Filtres Rapides -->
<div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: white; border: none; border-radius: 16px; padding: 18px 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="font-weight: 700; font-size: 14px; color: #cbd5e1;">💱 Devise d'affichage :</span>
            <div style="display: flex; gap: 8px;">
                <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=historique&currency=EUR" 
                   class="btn <?= $currentCurrency === 'EUR' ? 'btn-primary' : 'btn-secondary' ?> btn-sm" style="border-radius: 8px;">
                    € EUR
                </a>
                <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=historique&currency=USD" 
                   class="btn <?= $currentCurrency === 'USD' ? 'btn-primary' : 'btn-secondary' ?> btn-sm" style="border-radius: 8px;">
                    $ USD
                </a>
                <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=historique&currency=TND" 
                   class="btn <?= $currentCurrency === 'TND' ? 'btn-primary' : 'btn-secondary' ?> btn-sm" style="border-radius: 8px;">
                    د.ت TND
                </a>
            </div>
        </div>

        <div style="flex-grow: 1; max-width: 340px; min-width: 200px;">
            <input type="text" 
                   id="historySearch" 
                   onkeyup="filterHistory()" 
                   placeholder="🔍 Rechercher N°, médicament, date..." 
                   class="form-control" 
                   style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: 10px; padding: 8px 14px; font-size: 13px;">
        </div>
    </div>
</div>

<!-- Cartes KPI Statistiques -->
<div class="grid-responsive-3" style="margin-bottom: 30px;">
    <!-- Stat 1: Nb transactions -->
    <div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%); color: white; border: none; border-radius: 18px; position: relative; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.35);">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-bottom: 6px;">Total Achats Effectués</div>
        <div style="font-size: 38px; font-weight: 800; line-height: 1.1; margin-bottom: 4px;"><?= count($transactions) ?></div>
        <div style="font-size: 12px; opacity: 0.8;">Commandes finalisées avec succès</div>
        <div style="position: absolute; right: 20px; bottom: 15px; font-size: 55px; opacity: 0.15;">🛍️</div>
    </div>

    <!-- Stat 2: Total dépense -->
    <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); color: white; border: none; border-radius: 18px; position: relative; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.35);">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-bottom: 6px;">Montant Total Cumulé</div>
        <div style="font-size: 32px; font-weight: 800; line-height: 1.1; margin-bottom: 4px;">
            <?= CurrencyHelper::convertAndFormat($totalDepense, $currentCurrency) ?>
        </div>
        <?php if ($currentCurrency !== 'EUR'): ?>
            <div style="font-size: 12px; opacity: 0.85;">(soit <?= number_format($totalDepense, 2) ?> €)</div>
        <?php else: ?>
            <div style="font-size: 12px; opacity: 0.85;">Somme globale investie dans vos traitements</div>
        <?php endif; ?>
        <div style="position: absolute; right: 20px; bottom: 15px; font-size: 55px; opacity: 0.15;">💰</div>
    </div>

    <!-- Stat 3: Dernier achat -->
    <div class="card" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white; border: none; border-radius: 18px; position: relative; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(6, 182, 212, 0.35);">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-bottom: 6px;">Dernier Achat Effectué</div>
        <div style="font-size: 26px; font-weight: 800; line-height: 1.2; margin-bottom: 4px;">
            <?= date('d/m/Y', strtotime($transactions[0]['date_transaction'])) ?>
        </div>
        <div style="font-size: 12px; opacity: 0.85;">
            À <?= date('H:i', strtotime($transactions[0]['date_transaction'])) ?>
        </div>
        <div style="position: absolute; right: 20px; bottom: 15px; font-size: 55px; opacity: 0.15;">📅</div>
    </div>
</div>

<!-- Liste des transactions -->
<div style="display: flex; flex-direction: column; gap: 20px;" id="historyListContainer">
    <?php foreach ($transactions as $trans): 
        $modes = [
            'especes' => ['icon' => '💵', 'label' => 'Espèces', 'bg' => '#ecfdf5', 'color' => '#065f46'],
            'carte' => ['icon' => '💳', 'label' => 'Carte Bancaire', 'bg' => '#eff6ff', 'color' => '#1e40af'],
            'cheque' => ['icon' => '📝', 'label' => 'Chèque', 'bg' => '#fff7ed', 'color' => '#9a3412'],
            'mutuelle' => ['icon' => '🏥', 'label' => 'Mutuelle', 'bg' => '#f5f3ff', 'color' => '#5b21b6']
        ];
        $mode = $modes[$trans['mode_paiement']] ?? ['icon' => '💰', 'label' => $trans['mode_paiement'] ?? 'Paiement', 'bg' => '#f8fafc', 'color' => '#334155'];
    ?>
    <div class="card history-card-item" style="border-radius: 18px; padding: 24px; transition: transform 0.2s ease, box-shadow 0.2s ease; border: 1px solid var(--card-border);">
        
        <!-- Top bar transaction -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; border-bottom: 1px solid var(--card-border); padding-bottom: 18px; margin-bottom: 18px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                    <span style="font-size: 18px; font-weight: 800; color: var(--primary);">
                        Transaction #<?= $trans['id_transaction'] ?>
                    </span>
                    
                    <span style="background: <?= $mode['bg'] ?>; color: <?= $mode['color'] ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                        <?= $mode['icon'] ?> <?= $mode['label'] ?>
                    </span>

                    <?php if (!empty($trans['numero_ordonnance'])): ?>
                        <span class="badge badge-info" style="font-size: 12px; font-weight: 700;">
                            📜 Ordonnance N° <?= escape($trans['numero_ordonnance']) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-success" style="font-size: 12px; font-weight: 700;">
                            ✓ Vente en libre accès
                        </span>
                    <?php endif; ?>
                </div>

                <div style="color: var(--text-muted); font-size: 13px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <span>📅 <strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($trans['date_transaction'])) ?></span>
                    <span>👩‍⚕️ <strong>Pharmacien :</strong> <?= escape(($trans['nom'] ?? 'Officine') . ' ' . ($trans['prenom'] ?? 'Pharmacie')) ?></span>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 26px; font-weight: 800; color: #10b981; line-height: 1.1;">
                    <?= CurrencyHelper::convertAndFormat($trans['montant_total'], $currentCurrency) ?>
                </div>
                <?php if ($currentCurrency !== 'EUR'): ?>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                        (<?= number_format($trans['montant_total'], 2) ?> €)
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Médicaments inclus -->
        <?php if (!empty($trans['details'])): ?>
            <div>
                <h4 style="font-size: 14px; font-weight: 800; color: var(--text-dark); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                    <span>💊</span> Articles commandés (<?= count($trans['details']) ?>)
                </h4>
                
                <div style="display: grid; gap: 10px;">
                    <?php foreach ($trans['details'] as $detail): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; background: var(--bg-body, #f8fafc); padding: 12px 16px; border-radius: 12px; border: 1px solid var(--card-border); flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 12px; flex: 2; min-width: 200px;">
                            <?php if (!empty($detail['image'])): ?>
                                <img src="<?= escape(appUrl('view/uploads/')) ?>medicaments/<?= escape($detail['image']) ?>" 
                                     alt="<?= escape($detail['nom_commercial']) ?>"
                                     style="width: 48px; height: 48px; object-fit: cover; border-radius: 10px; border: 1px solid var(--card-border);"
                                     onerror="this.parentElement.innerHTML='<div style=\'width:48px;height:48px;background:#e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:24px;\'>💊</div>'">
                            <?php else: ?>
                                <div style="width: 48px; height: 48px; background: #e2e8f0; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid var(--card-border);">💊</div>
                            <?php endif; ?>

                            <div>
                                <div style="font-weight: 800; font-size: 15px; color: var(--text-dark);">
                                    <?= escape($detail['nom_commercial']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted);">
                                    <?= escape($detail['nom_generique'] ?? '') ?> <?= !empty($detail['forme']) ? '• ' . escape($detail['forme']) : '' ?> <?= !empty($detail['dosage']) ? escape($detail['dosage']) : '' ?>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: center; min-width: 80px;">
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Quantité</div>
                            <div style="font-weight: 800; font-size: 15px; color: var(--text-dark);">×<?= $detail['quantite'] ?></div>
                        </div>

                        <div style="text-align: right; min-width: 100px;">
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Prix Unitaire</div>
                            <div style="font-weight: 700; font-size: 14px; color: var(--text-dark);">
                                <?= CurrencyHelper::convertAndFormat($detail['prix_unitaire'], $currentCurrency) ?>
                            </div>
                        </div>

                        <div style="text-align: right; min-width: 110px;">
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Sous-total</div>
                            <div style="font-weight: 800; font-size: 15px; color: var(--primary);">
                                <?= CurrencyHelper::convertAndFormat($detail['prix_unitaire'] * $detail['quantite'], $currentCurrency) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<script>
function filterHistory() {
    const input = document.getElementById('historySearch').value.toLowerCase();
    const items = document.getElementsByClassName('history-card-item');

    for (let i = 0; i < items.length; i++) {
        const text = items[i].innerText.toLowerCase();
        if (text.includes(input)) {
            items[i].style.display = 'block';
        } else {
            items[i].style.display = 'none';
        }
    }
}
</script>

<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
