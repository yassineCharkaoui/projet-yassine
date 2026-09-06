<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Historique des Transactions';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../../controller/CurrencyHelper.php';

$currentCurrency = $_GET['currency'] ?? 'EUR';
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <div>
        <h2 style="font-size: 26px; font-weight: 800; color: var(--text-dark); margin-bottom: 4px;">
            📜 Historique des Transactions
        </h2>
        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
            Historique complet des validations, ventes et délivrances d'ordonnances
        </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button onclick="window.print()" class="btn btn-secondary btn-sm">
            🖨️ Imprimer le rapport
        </button>
    </div>
</div>

<!-- Filtres et Recherche -->
<div class="card" style="margin-bottom: 24px; border-radius: 16px;">
    <form method="GET" action="<?= escape(appUrl('index.php')) ?>" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="controller" value="pharmacien">
        <input type="hidden" name="action" value="historique">
        
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label for="date_debut" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">📅 Date début</label>
            <input type="date" id="date_debut" name="date_debut" class="form-control" 
                   value="<?= escape($_GET['date_debut'] ?? '') ?>">
        </div>
        
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label for="date_fin" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">📅 Date fin</label>
            <input type="date" id="date_fin" name="date_fin" class="form-control" 
                   value="<?= escape($_GET['date_fin'] ?? '') ?>">
        </div>
        
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label for="statut" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">Statut</label>
            <select id="statut" name="statut" class="form-control">
                <option value="">Tous les statuts</option>
                <option value="validee" <?= ($_GET['statut'] ?? '') === 'validee' ? 'selected' : '' ?>>Validée</option>
                <option value="traitee" <?= ($_GET['statut'] ?? '') === 'traitee' ? 'selected' : '' ?>>Traitée</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 700;">🔍 Filtrer</button>
            <?php if (!empty($_GET['date_debut']) || !empty($_GET['date_fin']) || !empty($_GET['statut'])): ?>
                <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=historique" class="btn btn-secondary" style="padding: 10px 16px;">Réinitialiser</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Statistiques KPI -->
<?php if (!empty($transactions)): 
    $totalVal = array_sum(array_column($transactions, 'montant_total'));
    $moyenneVal = count($transactions) > 0 ? $totalVal / count($transactions) : 0;
?>
<div class="grid-responsive-3" style="margin-bottom: 24px;">
    <div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%); color: white; border: none; border-radius: 18px;">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; opacity: 0.85; margin-bottom: 4px;">Total Transactions</div>
        <div style="font-size: 36px; font-weight: 800;"><?= count($transactions) ?></div>
        <div style="font-size: 12px; opacity: 0.85;">Délivrances enregistrées</div>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); color: white; border: none; border-radius: 18px;">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; opacity: 0.85; margin-bottom: 4px;">Montant Total Recouvré</div>
        <div style="font-size: 32px; font-weight: 800;">
            <?= CurrencyHelper::convertAndFormat($totalVal, $currentCurrency) ?>
        </div>
        <div style="font-size: 12px; opacity: 0.85;">Chiffre d'affaires historique</div>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white; border: none; border-radius: 18px;">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; opacity: 0.85; margin-bottom: 4px;">Panier Moyen</div>
        <div style="font-size: 32px; font-weight: 800;">
            <?= CurrencyHelper::convertAndFormat($moyenneVal, $currentCurrency) ?>
        </div>
        <div style="font-size: 12px; opacity: 0.85;">Par transaction traitée</div>
    </div>
</div>
<?php endif; ?>

<!-- Liste des transactions -->
<?php if (empty($transactions)): ?>
<div class="card" style="text-align: center; padding: 60px; border-radius: 18px;">
    <div style="font-size: 60px; margin-bottom: 12px; opacity: 0.6;">🔍</div>
    <h3 style="color: var(--text-muted); font-weight: 700;">Aucune transaction trouvée</h3>
    <p style="color: var(--text-muted); font-size: 13px;">Ajustez vos filtres ou la période sélectionnée.</p>
</div>
<?php else: ?>
<div class="card" style="border-radius: 18px; padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table" style="margin: 0;">
            <thead>
                <tr>
                    <th>N° ID</th>
                    <th>Date & Heure</th>
                    <th>Client</th>
                    <th>Ordonnance</th>
                    <th>Articles</th>
                    <th>Mode Paiement</th>
                    <th>Montant Total</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $trans): 
                    $modes = [
                        'especes' => '💵 Espèces',
                        'carte' => '💳 Carte',
                        'cheque' => '📝 Chèque',
                        'mutuelle' => '🏥 Mutuelle'
                    ];
                    $modeLabel = paymentLabel($trans['mode_paiement'] ?? null);
                ?>
                <tr>
                    <td><strong style="color: var(--primary);">#<?= $trans['id_transaction'] ?></strong></td>
                    <td>
                        <div style="font-weight: 700; color: var(--text-dark);"><?= date('d/m/Y', strtotime($trans['date_transaction'])) ?></div>
                        <div style="font-size: 11px; color: var(--text-muted);"><?= date('H:i', strtotime($trans['date_transaction'])) ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: var(--text-dark);"><?= escape($trans['client_nom'] ?? 'Client Anonyme') ?> <?= escape($trans['client_prenom'] ?? '') ?></div>
                        <div style="font-size: 11px; color: var(--text-muted);"><?= escape($trans['client_email'] ?? '') ?></div>
                    </td>
                    <td>
                        <?php if (!empty($trans['id_ordonnance'])): ?>
                            <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=viewOrdonnance&id=<?= $trans['id_ordonnance'] ?>" 
                               class="badge badge-info" style="text-decoration: none;">
                                📜 ORD-<?= $trans['id_ordonnance'] ?>
                            </a>
                        <?php else: ?>
                            <span class="badge badge-secondary">Vente libre</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-secondary" style="font-size: 11px;">
                            💊 <?= $trans['nb_medicaments'] ?? 1 ?> article(s)
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 12px; font-weight: 700; color: var(--text-dark);">
                            <?= escape($modeLabel) ?>
                        </span>
                    </td>
                    <td>
                        <strong style="color: #10b981; font-size: 16px;">
                            <?= CurrencyHelper::convertAndFormat($trans['montant_total'], $currentCurrency) ?>
                        </strong>
                    </td>
                    <td>
                        <?php 
                        $statutOrd = $trans['statut_ordonnance'] ?? null;
                        if ($statutOrd === 'traitee'): 
                        ?>
                            <span class="badge badge-success">✓ Traitée</span>
                        <?php elseif ($statutOrd === 'validee'): ?>
                            <span class="badge badge-info">✓ Validée</span>
                        <?php elseif ($statutOrd === 'en_attente'): ?>
                            <span class="badge badge-warning">En attente</span>
                        <?php else: ?>
                            <span class="badge badge-success">✓ Complétée</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <?php if (!empty($trans['id_ordonnance'])): ?>
                            <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=viewOrdonnance&id=<?= $trans['id_ordonnance'] ?>" 
                               class="btn btn-sm btn-primary">
                                👁️ Voir
                            </a>
                        <?php else: ?>
                            <span style="font-size: 12px; color: var(--text-muted);">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
