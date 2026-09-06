<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Mon Espace Client';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <h2>🏠 Mon Espace Client</h2>
        <p>Bienvenue <?= escape($_SESSION['user_prenom']) ?> ! Retrouvez vos informations et commandes ci-dessous.</p>
    </div>
    <div>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue" class="btn btn-primary">
            📦 Consulter le Catalogue
        </a>
    </div>
</div>

<!-- Actions rapides -->
<div class="grid-responsive-4" style="margin-bottom: 28px;">
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue" style="text-decoration: none;">
        <div class="card" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; margin-bottom: 0;">
            <h3 style="margin-bottom: 8px; font-size: 18px; color: white;">📖 Catalogue</h3>
            <p style="font-size: 13px; opacity: 0.9;">Consulter les médicaments sans ordonnance</p>
        </div>
    </a>
    
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=soumettre" style="text-decoration: none;">
        <div class="card" style="background: linear-gradient(135deg, #ec4899 0%, #d946ef 100%); color: white; margin-bottom: 0;">
            <h3 style="margin-bottom: 8px; font-size: 18px; color: white;">📋 Nouvelle Ordonnance</h3>
            <p style="font-size: 13px; opacity: 0.9;">Soumettre un scan d'ordonnance</p>
        </div>
    </a>
    
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=mesOrdonnances" style="text-decoration: none;">
        <div class="card" style="background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%); color: white; margin-bottom: 0;">
            <h3 style="margin-bottom: 8px; font-size: 18px; color: white;">📑 Mes Ordonnances</h3>
            <p style="font-size: 13px; opacity: 0.9;">Suivre l'état de validation</p>
        </div>
    </a>
    
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=historique" style="text-decoration: none;">
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; margin-bottom: 0;">
            <h3 style="margin-bottom: 8px; font-size: 18px; color: white;">💰 Historique d'Achats</h3>
            <p style="font-size: 13px; opacity: 0.9;">Consulter l'historique détaillé</p>
        </div>
    </a>
</div>

<div class="form-row" style="grid-template-columns: 2fr 1fr;">
    <!-- Mes dernières ordonnances -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Mes dernières ordonnances</h3>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=mesOrdonnances" class="btn btn-secondary btn-sm">Tout voir</a>
        </div>
        <?php if (empty($data['mes_ordonnances'])): ?>
            <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                Vous n'avez pas encore soumis d'ordonnance
            </p>
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=soumettre" class="btn btn-primary">
                    Soumettre ma première ordonnance
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Ordonnance</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Montant</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($data['mes_ordonnances'], 0, 5) as $ord): ?>
                        <tr>
                            <td><strong><?= escape($ord['numero_ordonnance']) ?></strong></td>
                            <td><?= date('d/m/Y', strtotime($ord['date_soumission'])) ?></td>
                            <td>
                                <?php 
                                $badges = [
                                    'en_attente' => 'warning',
                                    'validee' => 'success',
                                    'traitee' => 'info',
                                    'rejetee' => 'danger'
                                ];
                                $badge = $badges[$ord['statut']] ?? 'secondary';
                                $labels = [
                                    'en_attente' => 'En attente',
                                    'validee' => 'Validée',
                                    'traitee' => 'Traitée',
                                    'rejetee' => 'Rejetée'
                                ];
                                ?>
                                <span class="badge badge-<?= $badge ?>"><?= $labels[$ord['statut']] ?></span>
                            </td>
                            <td><strong><?= number_format($ord['montant_total'], 2) ?> €</strong></td>
                            <td>
                                <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewOrdonnance&id=<?= $ord['id_ordonnance'] ?>" 
                                   class="btn btn-sm btn-primary">Voir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Mes demandes de renouvellement -->
    <div class="card">
        <div class="card-header">
            <h3>🔄 Mes demandes</h3>
        </div>
        <?php if (empty($data['mes_demandes'])): ?>
            <p style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 14px;">
                Aucune demande de renouvellement
            </p>
        <?php else: ?>
            <div style="max-height: 380px; overflow-y: auto;">
                <?php foreach ($data['mes_demandes'] as $demande): ?>
                <div style="padding: 12px 0; border-bottom: 1px solid var(--card-border);">
                    <p style="margin-bottom: 4px;"><strong><?= escape($demande['numero_ordonnance']) ?></strong></p>
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">
                        <?= date('d/m/Y', strtotime($demande['date_demande'])) ?>
                    </p>
                    <?php 
                    $badges = [
                        'en_attente' => 'warning',
                        'approuvee' => 'success',
                        'rejetee' => 'danger'
                    ];
                    $badge = $badges[$demande['statut']] ?? 'secondary';
                    ?>
                    <span class="badge badge-<?= $badge ?>"><?= ucfirst($demande['statut']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Dernières transactions -->
<?php if (!empty($data['mes_transactions'])): ?>
<div class="card">
    <div class="card-header">
        <h3>💳 Mes derniers achats</h3>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=historique" class="btn btn-secondary btn-sm">Voir tout l'historique</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Ordonnance / Ref</th>
                    <th>Médicaments</th>
                    <th>Mode paiement</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($data['mes_transactions'], 0, 5) as $trans): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($trans['date_transaction'])) ?></td>
                    <td><strong><?= escape($trans['numero_ordonnance'] ?? 'Achat Panier #' . $trans['id_transaction']) ?></strong></td>
                    <td><?= escape($trans['medicaments'] ?? 'Produits parapharmacie') ?></td>
                    <td>
                        <?php
                        $modes = [
                            'especes' => '💵 Espèces',
                            'carte' => '💳 Carte',
                            'cheque' => '📝 Chèque',
                            'mutuelle' => '🏥 Mutuelle'
                        ];
                        echo $modes[$trans['mode_paiement']] ?? $trans['mode_paiement'];
                        ?>
                    </td>
                    <td><strong style="color: var(--primary);"><?= number_format($trans['montant_total'], 2) ?> €</strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
.card:hover {
    box-shadow: var(--shadow-md);
}
</style>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
