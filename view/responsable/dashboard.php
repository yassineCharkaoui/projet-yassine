<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Dashboard Responsable';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <h2>📊 Dashboard Responsable</h2>
        <p>Vue d'ensemble de la pharmacie, état des stocks et indicateurs d'activité</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=addMedicament" class="btn btn-primary">
            + Ajouter un Médicament
        </a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=rapports" class="btn btn-secondary">
            📈 Rapports Financiers
        </a>
    </div>
</div>

<!-- Statistiques générales -->
<div class="grid-responsive-4" style="margin-bottom: 28px;">
    <div class="card" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; margin-bottom: 0;">
        <h3 style="margin-bottom: 6px; font-size: 15px; opacity: 0.95; color: white;">Total Médicaments</h3>
        <p style="font-size: 34px; font-weight: 800; margin-bottom: 4px;"><?= $data['stats_medicaments']['total'] ?? 0 ?></p>
        <p style="font-size: 13px; opacity: 0.9;"><?= $data['stats_medicaments']['stock_critique'] ?? 0 ?> en stock critique</p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #ec4899 0%, #d946ef 100%); color: white; margin-bottom: 0;">
        <h3 style="margin-bottom: 6px; font-size: 15px; opacity: 0.95; color: white;">Ordonnances en attente</h3>
        <p style="font-size: 34px; font-weight: 800; margin-bottom: 4px;"><?= $data['stats_ordonnances']['en_attente'] ?? 0 ?></p>
        <p style="font-size: 13px; opacity: 0.9;">Sur un total de <?= $data['stats_ordonnances']['total'] ?? 0 ?></p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%); color: white; margin-bottom: 0;">
        <h3 style="margin-bottom: 6px; font-size: 15px; opacity: 0.95; color: white;">Demandes renouvellement</h3>
        <p style="font-size: 34px; font-weight: 800; margin-bottom: 4px;"><?= $data['stats_demandes']['en_attente'] ?? 0 ?></p>
        <p style="font-size: 13px; opacity: 0.9;">En attente de traitement</p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; margin-bottom: 0;">
        <h3 style="margin-bottom: 6px; font-size: 15px; opacity: 0.95; color: white;">Chiffre d'Affaires du Mois</h3>
        <p style="font-size: 34px; font-weight: 800; margin-bottom: 4px;"><?= number_format($data['stats_transactions']['montant_total'] ?? 0, 2) ?> €</p>
        <p style="font-size: 13px; opacity: 0.9;"><?= $data['stats_transactions']['total_transactions'] ?? 0 ?> ventes effectuées</p>
    </div>
</div>

<div class="form-row" style="grid-template-columns: 2fr 1fr;">
    <!-- Stock critique -->
    <div class="card">
        <div class="card-header">
            <h3>⚠️ Médicaments en stock critique</h3>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=rapportStockCritique" class="btn btn-secondary btn-sm">Voir tout le rapport</a>
        </div>
        <?php if (empty($data['stock_critique'])): ?>
            <p style="color: #10b981; text-align: center; padding: 30px; font-weight: 600;">✓ Tous les stocks sont à un niveau optimal</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Médicament</th>
                            <th>Stock</th>
                            <th>Min</th>
                            <th>État</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($data['stock_critique'], 0, 5) as $med): ?>
                        <tr>
                            <td>
                                <strong><?= escape($med['nom_commercial']) ?></strong><br>
                                <small style="color: var(--text-muted);"><?= escape($med['forme']) ?> <?= escape($med['dosage']) ?></small>
                            </td>
                            <td><strong style="color: var(--primary);"><?= $med['stock_disponible'] ?></strong></td>
                            <td><?= $med['stock_minimum'] ?></td>
                            <td>
                                <?php if ($med['etat_stock'] === 'RUPTURE'): ?>
                                    <span class="badge badge-danger">RUPTURE</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">CRITIQUE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=editMedicament&id=<?= $med['id_medicament'] ?>" class="btn btn-sm btn-primary">Réapprovisionner</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Demandes en attente -->
    <div class="card">
        <div class="card-header">
            <h3>🔄 Demandes de renouvellement</h3>
        </div>
        <?php if (empty($data['demandes_en_attente'])): ?>
            <p style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 14px;">Aucune demande en attente</p>
        <?php else: ?>
            <div style="max-height: 380px; overflow-y: auto;">
                <?php foreach ($data['demandes_en_attente'] as $demande): ?>
                <div style="padding: 12px 0; border-bottom: 1px solid var(--card-border);">
                    <p style="margin-bottom: 4px;"><strong><?= escape($demande['client_nom'] . ' ' . $demande['client_prenom']) ?></strong></p>
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">
                        Ordonnance : <?= escape($demande['numero_ordonnance']) ?>
                    </p>
                    <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listDemandes" class="btn btn-sm btn-primary">Traiter</a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Ordonnances en attente -->
<?php if (!empty($data['ordonnances_en_attente'])): ?>
<div class="card">
    <div class="card-header">
        <h3>📋 Ordonnances en attente de validation</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Ordonnance</th>
                    <th>Client</th>
                    <th>Médecin Prescripteur</th>
                    <th>Date soumission</th>
                    <th>Nb médicaments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($data['ordonnances_en_attente'], 0, 5) as $ord): ?>
                <tr>
                    <td><strong><?= escape($ord['numero_ordonnance']) ?></strong></td>
                    <td><?= escape($ord['nom_client'] . ' ' . $ord['prenom_client']) ?></td>
                    <td><?= escape($ord['nom_medecin']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($ord['date_soumission'])) ?></td>
                    <td><strong><?= $ord['nb_medicaments'] ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
