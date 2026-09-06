<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Dashboard Pharmacien';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <h2>💼 Dashboard Pharmacien</h2>
        <p>Bienvenue <?= escape($_SESSION['user_prenom']) ?> ! Validez les ordonnances et suivez les délivrances.</p>
    </div>
    <div>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances" class="btn btn-primary">
            📝 Traiter les Ordonnances
        </a>
    </div>
</div>

<!-- Statistiques rapides -->
<div class="grid-responsive-3" style="margin-bottom: 28px;">
    <div class="card" style="background: linear-gradient(135deg, #ec4899 0%, #d946ef 100%); color: white; margin-bottom: 0;">
        <h3 style="margin-bottom: 6px; font-size: 15px; opacity: 0.95; color: white;">Ordonnances en attente</h3>
        <p style="font-size: 34px; font-weight: 800; margin-bottom: 8px;"><?= count($data['ordonnances_en_attente']) ?></p>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances&statut=en_attente" 
           style="color: white; text-decoration: underline; font-size: 13px; font-weight: 600;">Traiter la file d'attente →</a>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%); color: white; margin-bottom: 0;">
        <h3 style="margin-bottom: 6px; font-size: 15px; opacity: 0.95; color: white;">Mes transactions enregistrées</h3>
        <p style="font-size: 34px; font-weight: 800; margin-bottom: 8px;"><?= count($data['mes_transactions']) ?></p>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=historique" 
           style="color: white; text-decoration: underline; font-size: 13px; font-weight: 600;">Voir l'historique d'achats →</a>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; margin-bottom: 0;">
        <h3 style="margin-bottom: 6px; font-size: 15px; opacity: 0.95; color: white;">Ordonnances validées</h3>
        <p style="font-size: 34px; font-weight: 800; margin-bottom: 4px;"><?= $data['stats']['validee'] ?? 0 ?></p>
        <p style="font-size: 13px; opacity: 0.9;">Sur un total de <?= $data['stats']['total'] ?? 0 ?> réceptions</p>
    </div>
</div>

<div class="form-row" style="grid-template-columns: 2fr 1fr;">
    <!-- Ordonnances en attente -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Ordonnances en attente de traitement</h3>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances" class="btn btn-secondary btn-sm">Voir la liste</a>
        </div>
        <?php if (empty($data['ordonnances_en_attente'])): ?>
            <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                ✓ Aucune ordonnance en attente de validation
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Ordonnance</th>
                            <th>Client Patient</th>
                            <th>Date Soumission</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($data['ordonnances_en_attente'], 0, 10) as $ord): ?>
                        <tr>
                            <td><strong><?= escape($ord['numero_ordonnance']) ?></strong></td>
                            <td>
                                <strong><?= escape($ord['client_nom'] . ' ' . $ord['client_prenom']) ?></strong><br>
                                <small style="color: var(--text-muted);"><?= escape($ord['client_telephone'] ?? '') ?></small>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($ord['date_soumission'])) ?></td>
                            <td>
                                <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=viewOrdonnance&id=<?= $ord['id_ordonnance'] ?>" 
                                   class="btn btn-sm btn-primary">Examiner & Valider</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Mes dernières transactions -->
    <div class="card">
        <div class="card-header">
            <h3>💰 Dernières transactions</h3>
        </div>
        <?php if (empty($data['mes_transactions'])): ?>
            <p style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 14px;">Aucune transaction enregistrée</p>
        <?php else: ?>
            <div style="max-height: 380px; overflow-y: auto;">
                <?php foreach (array_slice($data['mes_transactions'], 0, 5) as $trans): ?>
                <div style="padding: 12px 0; border-bottom: 1px solid var(--card-border);">
                    <p style="margin-bottom: 4px; font-weight: 800; color: var(--primary);">
                        <?= number_format($trans['montant_total'], 2) ?> €
                    </p>
                    <p style="font-size: 13px; color: var(--text-dark); margin-bottom: 2px;">
                        Client: <strong><?= escape($trans['client_nom'] . ' ' . $trans['client_prenom']) ?></strong>
                    </p>
                    <p style="font-size: 12px; color: var(--text-muted);">
                        <?= date('d/m/Y H:i', strtotime($trans['date_transaction'])) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Ordonnances récemment traitées -->
<?php if (!empty($data['mes_ordonnances'])): ?>
<div class="card">
    <div class="card-header">
        <h3>✅ Mes dernières ordonnances traitées</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Ordonnance</th>
                    <th>Client</th>
                    <th>Statut</th>
                    <th>Date validation</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($data['mes_ordonnances'], 0, 10) as $ord): ?>
                <tr>
                    <td><strong><?= escape($ord['numero_ordonnance']) ?></strong></td>
                    <td><?= escape($ord['client_nom'] . ' ' . $ord['client_prenom']) ?></td>
                    <td>
                        <?php 
                        $badges = [
                            'validee' => 'success',
                            'traitee' => 'info',
                            'rejetee' => 'danger'
                        ];
                        $badge = $badges[$ord['statut']] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?= $badge ?>"><?= ucfirst($ord['statut']) ?></span>
                    </td>
                    <td><?= $ord['date_validation'] ? date('d/m/Y H:i', strtotime($ord['date_validation'])) : '-' ?></td>
                    <td><strong><?= number_format($ord['montant_total'], 2) ?> €</strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
