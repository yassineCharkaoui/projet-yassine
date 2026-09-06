<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Rapports et Statistiques';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="page-header">
    <div>
        <h2>📊 Rapports et Statistiques</h2>
        <p>Vue d'ensemble complète de l'activité, des stocks et des performances financières</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=rapportStockCritique" class="btn btn-warning">
            ⚠️ Rapport stocks critiques
        </a>
        <button onclick="window.print()" class="btn btn-secondary">
            🖨️ Imprimer ce rapport
        </button>
    </div>
</div>

<!-- Statistiques des médicaments -->
<div class="card">
    <div class="card-header">
        <h3>💊 Statistiques des Médicaments</h3>
    </div>
    <div class="grid-responsive-4">
        <div style="text-align: center; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--card-border);">
            <p style="color: var(--text-muted); margin-bottom: 8px; font-size: 13px; font-weight: 700;">Total Médicaments</p>
            <p style="font-size: 34px; font-weight: 800; color: var(--primary); margin: 0;">
                <?= $data['stats_medicaments']['total'] ?? 0 ?>
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: #fef2f2; border-radius: 12px; border: 1px solid #fecaca;">
            <p style="color: #991b1b; margin-bottom: 8px; font-size: 13px; font-weight: 700;">Stock Critique</p>
            <p style="font-size: 34px; font-weight: 800; color: #ef4444; margin: 0;">
                <?= $data['stats_medicaments']['stock_critique'] ?? 0 ?>
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: #fff1f2; border-radius: 12px; border: 1px solid #ffe4e6;">
            <p style="color: #9f1239; margin-bottom: 8px; font-size: 13px; font-weight: 700;">Rupture de Stock</p>
            <p style="font-size: 34px; font-weight: 800; color: #e11d48; margin: 0;">
                <?= $data['stats_medicaments']['rupture_stock'] ?? 0 ?>
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: #fffbe6; border-radius: 12px; border: 1px solid #ffe58f;">
            <p style="color: #d48806; margin-bottom: 8px; font-size: 13px; font-weight: 700;">Avec Prescription</p>
            <p style="font-size: 34px; font-weight: 800; color: #d97706; margin: 0;">
                <?= $data['stats_medicaments']['avec_prescription'] ?? 0 ?>
            </p>
        </div>
    </div>
</div>

<!-- Statistiques des ordonnances -->
<div class="card">
    <div class="card-header">
        <h3>📋 Statistiques des Ordonnances</h3>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
        <div style="text-align: center; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--card-border);">
            <p style="color: var(--text-muted); margin-bottom: 8px; font-size: 13px; font-weight: 700;">Total Reçues</p>
            <p style="font-size: 30px; font-weight: 800; color: var(--text-dark); margin: 0;">
                <?= $data['stats_ordonnances']['total'] ?? 0 ?>
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: #fef3c7; border-radius: 12px; border: 1px solid #fde68a;">
            <p style="color: #92400e; margin-bottom: 8px; font-size: 13px; font-weight: 700;">En Attente</p>
            <p style="font-size: 30px; font-weight: 800; color: #d97706; margin: 0;">
                <?= $data['stats_ordonnances']['en_attente'] ?? 0 ?>
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: #dcfce7; border-radius: 12px; border: 1px solid #bbf7d0;">
            <p style="color: #166534; margin-bottom: 8px; font-size: 13px; font-weight: 700;">Validées</p>
            <p style="font-size: 30px; font-weight: 800; color: #16a34a; margin: 0;">
                <?= $data['stats_ordonnances']['validee'] ?? 0 ?>
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: #e0f2fe; border-radius: 12px; border: 1px solid #bae6fd;">
            <p style="color: #075985; margin-bottom: 8px; font-size: 13px; font-weight: 700;">Traitées</p>
            <p style="font-size: 30px; font-weight: 800; color: #0284c7; margin: 0;">
                <?= $data['stats_ordonnances']['traitee'] ?? 0 ?>
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: #fee2e2; border-radius: 12px; border: 1px solid #fecaca;">
            <p style="color: #991b1b; margin-bottom: 8px; font-size: 13px; font-weight: 700;">Rejetées</p>
            <p style="font-size: 30px; font-weight: 800; color: #dc2626; margin: 0;">
                <?= $data['stats_ordonnances']['rejetee'] ?? 0 ?>
            </p>
        </div>
    </div>
    <div style="margin-top: 20px; padding: 20px; background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: white; border-radius: 14px; text-align: center;">
        <p style="font-size: 16px; margin-bottom: 6px; opacity: 0.9;">Montant Total des Ordonnances</p>
        <p style="font-size: 42px; font-weight: 800; margin: 0; color: #34d399;">
            <?= number_format($data['stats_ordonnances']['montant_total'] ?? 0, 2) ?> €
        </p>
    </div>
</div>

<!-- Statistiques des transactions -->
<div class="card">
    <div class="card-header">
        <h3>💰 Statistiques des Transactions</h3>
    </div>
    <div class="grid-responsive-4" style="margin-bottom: 24px;">
        <div style="text-align: center; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--card-border);">
            <p style="color: var(--text-muted); margin-bottom: 8px; font-size: 13px; font-weight: 700;">Total Transactions</p>
            <p style="font-size: 30px; font-weight: 800; color: var(--text-dark); margin: 0;">
                <?= $data['stats_transactions']['total_transactions'] ?? 0 ?>
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 12px;">
            <p style="opacity: 0.9; margin-bottom: 8px; font-size: 13px; font-weight: 700;">Montant Total Ventes</p>
            <p style="font-size: 30px; font-weight: 800; margin: 0;">
                <?= number_format($data['stats_transactions']['montant_total'] ?? 0, 2) ?> €
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: #dcfce7; border-radius: 12px; border: 1px solid #bbf7d0;">
            <p style="color: #166534; margin-bottom: 8px; font-size: 13px; font-weight: 700;">Montant Moyen / Panier</p>
            <p style="font-size: 30px; font-weight: 800; color: #15803d; margin: 0;">
                <?= number_format($data['stats_transactions']['montant_moyen'] ?? 0, 2) ?> €
            </p>
        </div>
        <div style="text-align: center; padding: 20px; background: #f3e8ff; border-radius: 12px; border: 1px solid #e9d5ff;">
            <p style="color: #6b21a8; margin-bottom: 8px; font-size: 13px; font-weight: 700;">Clients Uniques</p>
            <p style="font-size: 30px; font-weight: 800; color: #7e22ce; margin: 0;">
                <?= $data['stats_transactions']['nb_clients'] ?? 0 ?>
            </p>
        </div>
    </div>
    
    <!-- Répartition par mode de paiement -->
    <h4 style="margin-bottom: 16px; color: var(--text-dark); font-size: 16px;">Répartition par Mode de Paiement</h4>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Mode de paiement</th>
                    <th>Nombre de transactions</th>
                    <th>Montant total</th>
                    <th>Pourcentage du CA</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalTransactions = array_sum(array_column($data['transactions_par_mode'], 'nombre'));
                $totalMontant = array_sum(array_column($data['transactions_par_mode'], 'total'));
                foreach ($data['transactions_par_mode'] as $mode): 
                ?>
                <tr>
                    <td><strong><?= escape(paymentLabel($mode['mode_paiement'] ?? null)) ?></strong></td>
                    <td><strong><?= $mode['nombre'] ?></strong></td>
                    <td><strong style="color: var(--primary);"><?= number_format($mode['total'], 2) ?> €</strong></td>
                    <td>
                        <?php $percent = $totalMontant > 0 ? ($mode['total'] / $totalMontant) * 100 : 0; ?>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="flex: 1; background: #e2e8f0; border-radius: 10px; height: 16px; overflow: hidden; min-width: 80px;">
                                <div style="background: linear-gradient(90deg, #4f46e5, #06b6d4); height: 100%; width: <?= $percent ?>%;"></div>
                            </div>
                            <strong><?= number_format($percent, 1) ?>%</strong>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Statistiques des utilisateurs -->
<div class="card">
    <div class="card-header">
        <h3>👥 Répartition des Comptes Utilisateurs</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Rôle Utilisateur</th>
                    <th>Total Inscrit</th>
                    <th>Comptes Actifs</th>
                    <th>Comptes Inactifs</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['stats_utilisateurs'] as $stat): ?>
                <tr>
                    <td>
                        <?php
                        $role_labels = [
                            'responsable' => '👨‍💼 Responsable Stock',
                            'pharmacien' => '💊 Pharmacien Officinal',
                            'client' => '👤 Client Patient'
                        ];
                        echo $role_labels[$stat['role']] ?? ucfirst($stat['role']);
                        ?>
                    </td>
                    <td><strong><?= $stat['total'] ?></strong></td>
                    <td><span class="badge badge-success"><?= $stat['actifs'] ?> Actifs</span></td>
                    <td><span class="badge badge-secondary"><?= $stat['total'] - $stat['actifs'] ?> Inactifs</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style media="print">
    .navbar, .btn, .page-header p, footer {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        page-break-inside: avoid;
    }
</style>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
