<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
/**
 * Rapport des Stocks Critiques - Vue Responsable
 * PHP 8 - Architecture MVC
 */
$pageTitle = 'Rapport - Stocks Critiques';
require_once __DIR__ . '/../../layout/header.php';

// Calcul des métriques clés
$totalArticles = count($stocks);
$nbRupture = count(array_filter($stocks, fn($s) => ($s['etat_stock'] ?? '') === 'RUPTURE' || ($s['stock_disponible'] ?? 0) == 0));
$nbCritique = $totalArticles - $nbRupture;

$totalUnitsToOrder = 0;
$coutTotalReappro = 0;

foreach ($stocks as $s) {
    $min = (int)($s['stock_minimum'] ?? 10);
    $disp = (int)($s['stock_disponible'] ?? 0);
    $prix = (float)($s['prix_unitaire'] ?? 0);
    $toOrder = max(($min * 2) - $disp, 0);
    $totalUnitsToOrder += $toOrder;
    $coutTotalReappro += ($toOrder * $prix);
}
?>

<style>
    /* Styling spécifique pour le Rapport de Stock Critique */
    .report-header {
        background: white;
        padding: 24px 30px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border-left: 6px solid #e74c3c;
    }

    .report-title h2 {
        color: #2c3e50;
        font-size: 26px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 4px;
    }

    .report-title p {
        color: #7f8c8d;
        font-size: 14px;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .kpi-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid #edf2f7;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .kpi-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 6px;
        height: 100%;
    }

    .kpi-card.rupture::after { background: #e74c3c; }
    .kpi-card.critique::after { background: #f39c12; }
    .kpi-card.budget::after { background: #27ae60; }
    .kpi-card.units::after { background: #667eea; }

    .kpi-label {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #7f8c8d;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .kpi-value {
        font-size: 32px;
        font-weight: 800;
        color: #2c3e50;
        line-height: 1.1;
    }

    .kpi-subtext {
        font-size: 12px;
        color: #95a5a6;
        margin-top: 6px;
    }

    /* Interactive Toolbar */
    .filter-toolbar {
        background: white;
        padding: 16px 24px;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border: 1px solid #edf2f7;
    }

    .filter-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .tab-btn {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid #cbd5e0;
        background: #f8fafc;
        color: #4a5568;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .tab-btn.active, .tab-btn:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .search-box {
        position: relative;
        min-width: 260px;
    }

    .search-box input {
        width: 100%;
        padding: 9px 16px 9px 38px;
        border: 1px solid #cbd5e0;
        border-radius: 20px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s ease;
    }

    .search-box input:focus {
        border-color: #667eea;
    }

    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
        font-size: 14px;
    }

    /* Table Styling */
    .stock-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid #edf2f7;
        overflow: hidden;
    }

    .stock-table {
        width: 100%;
        border-collapse: collapse;
    }

    .stock-table th {
        background: #f8fafc;
        color: #4a5568;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        border-bottom: 2px solid #edf2f7;
        text-align: left;
    }

    .stock-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #2d3748;
        vertical-align: middle;
    }

    .stock-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .stock-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .row-rupture {
        background-color: #fff5f5 !important;
    }

    .row-rupture:hover {
        background-color: #fed7d7 !important;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .badge-rupture-glow {
        background: #fed7d7;
        color: #9b2c2c;
        border: 1px solid #feb2b2;
    }

    .badge-critique-glow {
        background: #feebc8;
        color: #9c4221;
        border: 1px solid #fbd38d;
    }

    /* Progress bar for stock level */
    .progress-bar-container {
        width: 120px;
        background: #e2e8f0;
        height: 8px;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 6px;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.4s ease;
    }

    .fill-rupture { background: #e74c3c; }
    .fill-critique { background: #f39c12; }

    .med-img-thumb {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        object-fit: cover;
        background: #edf2f7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        border: 1px solid #e2e8f0;
    }

    .summary-footer-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: white;
        border-radius: 16px;
        padding: 24px 30px;
        margin-top: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
    }

    /* Print styling */
    @media print {
        .navbar, .btn, .filter-toolbar, .btn-action, .header-actions {
            display: none !important;
        }
        body { background: white !important; }
        .report-header { box-shadow: none; border-left: 4px solid #000; padding: 10px 0; }
        .stock-table-card { box-shadow: none; border: 1px solid #ccc; }
        .summary-footer-card { background: #f8fafc !important; color: #000 !important; border: 1px solid #ccc; }
    }
</style>

<!-- Header Banner -->
<div class="report-header">
    <div class="report-title">
        <h2>⚠️ Rapport des Stocks Critiques</h2>
        <p>Analyse des besoins de réapprovisionnement et alertes de rupture</p>
    </div>
    <div class="header-actions" style="display: flex; gap: 10px;">
        <button onclick="window.print()" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            🖨️ Imprimer le Rapport
        </button>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=addMedicament" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
            + Ajouter un Médicament
        </a>
    </div>
</div>

<?php if (empty($stocks)): ?>
    <!-- Empty State -->
    <div class="card" style="text-align: center; padding: 70px 20px; border-radius: 16px;">
        <div style="font-size: 64px; margin-bottom: 16px;">🎉</div>
        <h3 style="color: #27ae60; font-size: 24px; font-weight: 700; margin-bottom: 8px;">Aucun stock critique détecté !</h3>
        <p style="color: #7f8c8d; font-size: 15px; max-width: 500px; margin: 0 auto 24px auto;">
            Tous les médicaments disposent d'une quantité en réserve supérieure à leur seuil de sécurité minimum.
        </p>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listMedicaments" class="btn btn-primary">
            Consulter le Catalogue Complet
        </a>
    </div>
<?php else: ?>

    <!-- KPI Summary Grid -->
    <div class="kpi-grid">
        <div class="kpi-card rupture">
            <div class="kpi-label">🚨 Ruptures de Stock</div>
            <div class="kpi-value" style="color: #e74c3c;"><?= $nbRupture ?></div>
            <div class="kpi-subtext">Articles nécessitant un réapprovisionnement immédiat</div>
        </div>

        <div class="kpi-card critique">
            <div class="kpi-label">⚠️ Stocks Critiques</div>
            <div class="kpi-value" style="color: #f39c12;"><?= $nbCritique ?></div>
            <div class="kpi-subtext">Stock inférieur au seuil de sécurité minimum</div>
        </div>

        <div class="kpi-card units">
            <div class="kpi-label">📦 Quantité Totale à Commander</div>
            <div class="kpi-value" style="color: #4f46e5;"><?= number_format($totalUnitsToOrder) ?></div>
            <div class="kpi-subtext">Unités recommandées (Cible = 2x Stock min)</div>
        </div>

        <div class="kpi-card budget">
            <div class="kpi-label">💰 Coût Total Estimé</div>
            <div class="kpi-value" style="color: #10b981;"><?= number_format($coutTotalReappro, 2) ?> €</div>
            <div class="kpi-subtext">Budget prévisionnel de réapprovisionnement</div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="filter-toolbar">
        <div class="filter-tabs">
            <button class="tab-btn active" onclick="filterTable('all', this)">Tous (<?= $totalArticles ?>)</button>
            <button class="tab-btn" onclick="filterTable('RUPTURE', this)">🚨 Ruptures (<?= $nbRupture ?>)</button>
            <button class="tab-btn" onclick="filterTable('CRITIQUE', this)">⚠️ Critiques (<?= $nbCritique ?>)</button>
        </div>

        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Filtrer par médicament, labo...">
        </div>
    </div>

    <!-- Main Data Table -->
    <div class="stock-table-card">
        <table class="stock-table" id="stockReportTable">
            <thead>
                <tr>
                    <th style="width: 140px;">Priorité</th>
                    <th>Médicament</th>
                    <th>Forme & Dosage</th>
                    <th>Laboratoire</th>
                    <th style="text-align: center;">Stock Actuel / Min</th>
                    <th style="text-align: center;">À Commander</th>
                    <th style="text-align: right;">Prix Unitaire</th>
                    <th style="text-align: right;">Coût Est.</th>
                    <th style="text-align: center; width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Tri par gravité : Rupture d'abord, puis stock disponible croissant
                usort($stocks, function($a, $b) {
                    $isRuptureA = (($a['etat_stock'] ?? '') === 'RUPTURE' || ($a['stock_disponible'] ?? 0) == 0);
                    $isRuptureB = (($b['etat_stock'] ?? '') === 'RUPTURE' || ($b['stock_disponible'] ?? 0) == 0);
                    if ($isRuptureA && !$isRuptureB) return -1;
                    if (!$isRuptureA && $isRuptureB) return 1;
                    return ($a['stock_disponible'] ?? 0) - ($b['stock_disponible'] ?? 0);
                });

                foreach ($stocks as $stock): 
                    $isRupture = (($stock['etat_stock'] ?? '') === 'RUPTURE' || ($stock['stock_disponible'] ?? 0) == 0);
                    $disp = (int)($stock['stock_disponible'] ?? 0);
                    $min = max((int)($stock['stock_minimum'] ?? 10), 1);
                    $pct = min(round(($disp / $min) * 100), 100);
                    
                    $aCommander = max(($min * 2) - $disp, 0);
                    $prix = (float)($stock['prix_unitaire'] ?? 0);
                    $subtotal = $aCommander * $prix;
                    
                    $imagePath = !empty($stock['image']) ? appUrl('view/uploads/medicaments/' . rawurlencode($stock['image'])) : null;
                ?>
                <tr class="report-row <?= $isRupture ? 'row-rupture' : '' ?>" data-status="<?= $isRupture ? 'RUPTURE' : 'CRITIQUE' ?>">
                    <!-- Priorité Badge -->
                    <td>
                        <?php if ($isRupture): ?>
                            <span class="status-badge badge-rupture-glow">🚨 RUPTURE</span>
                        <?php else: ?>
                            <span class="status-badge badge-critique-glow">⚠️ CRITIQUE</span>
                        <?php endif; ?>
                    </td>

                    <!-- Nom Médicament + Miniature -->
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <?php if ($imagePath && file_exists(__DIR__ . '/../../uploads/medicaments/' . $stock['image'])): ?>
                                <img src="<?= escape($imagePath) ?>" alt="<?= escape($stock['nom_commercial']) ?>" class="med-img-thumb">
                            <?php else: ?>
                                <div class="med-img-thumb">💊</div>
                            <?php endif; ?>
                            <div>
                                <div style="font-weight: 700; color: #1a202c; font-size: 15px;">
                                    <?= escape($stock['nom_commercial']) ?>
                                </div>
                                <?php if (!empty($stock['nom_generique'])): ?>
                                    <div style="font-size: 12px; color: #718096; font-style: italic;">
                                        <?= escape($stock['nom_generique']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>

                    <!-- Forme & Dosage -->
                    <td>
                        <span style="background: #edf2f7; color: #4a5568; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                            <?= escape($stock['forme'] ?? 'N/A') ?>
                        </span>
                        <span style="font-size: 13px; color: #718096; margin-left: 4px;">
                            <?= escape($stock['dosage'] ?? '') ?>
                        </span>
                    </td>

                    <!-- Laboratoire -->
                    <td style="color: #4a5568; font-weight: 500;">
                        <?= escape($stock['laboratoire'] ?? 'Non spécifié') ?>
                    </td>

                    <!-- Stock Actuel vs Min + Bar de progression -->
                    <td style="text-align: center;">
                        <div style="font-weight: 800; font-size: 16px; color: <?= $isRupture ? '#e74c3c' : '#d69e2e' ?>;">
                            <?= $disp ?> <span style="font-size: 12px; font-weight: 500; color: #a0aec0;">/ min <?= $min ?></span>
                        </div>
                        <div class="progress-bar-container" style="margin: 4px auto 0 auto;">
                            <div class="progress-bar-fill <?= $isRupture ? 'fill-rupture' : 'fill-critique' ?>" style="width: <?= max($pct, 5) ?>%;"></div>
                        </div>
                    </td>

                    <!-- Quantité à Commander -->
                    <td style="text-align: center;">
                        <span style="display: inline-block; background: #e0e7ff; color: #4338ca; padding: 6px 14px; border-radius: 12px; font-weight: 800; font-size: 15px;">
                            +<?= $aCommander ?>
                        </span>
                    </td>

                    <!-- Prix Unitaire -->
                    <td style="text-align: right; color: #4a5568; font-weight: 600;">
                        <?= number_format($prix, 2) ?> €
                    </td>

                    <!-- Coût Estimé -->
                    <td style="text-align: right;">
                        <strong style="color: #2d3748; font-size: 15px;">
                            <?= number_format($subtotal, 2) ?> €
                        </strong>
                    </td>

                    <!-- Action Directe -->
                    <td style="text-align: center;">
                        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=editMedicament&id=<?= $stock['id_medicament'] ?>" 
                           class="btn btn-sm btn-primary" 
                           style="border-radius: 8px; padding: 6px 12px; font-weight: 600; text-decoration: none; font-size: 12px;">
                            🔄 Réapprovisionner
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Summary Footer Card -->
    <div class="summary-footer-card">
        <div>
            <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; font-weight: 700; margin-bottom: 4px;">
                Récapitulatif Financier Prévisionnel
            </div>
            <div style="font-size: 22px; font-weight: 800;">
                Coût Global Réapprovisionnement : <span style="color: #34d399;"><?= number_format($coutTotalReappro, 2) ?> €</span>
            </div>
            <div style="font-size: 13px; color: #cbd5e1; margin-top: 4px;">
                Besoins calculés automatiquement pour reconstituer le stock à 200% du seuil minimal de sécurité.
            </div>
        </div>

        <div style="display: flex; gap: 12px;">
            <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=dashboard" class="btn btn-secondary">
                ← Tableau de Bord
            </a>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listMedicaments" class="btn btn-primary" style="background: #4f46e5;">
                Catalogue Médicaments
            </a>
        </div>
    </div>

<?php endif; ?>

<!-- JavaScript pour le Filtrage Dynamique & la Recherche Instantanée -->
<script>
    function filterTable(status, btnElement) {
        // Mettre à jour les boutons actifs
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        if (btnElement) btnElement.classList.add('active');

        const rows = document.querySelectorAll('#stockReportTable tbody tr');
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            if (status === 'all' || rowStatus === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function searchTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#stockReportTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
