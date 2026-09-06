<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Gestion des Ordonnances';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="page-header">
    <h2>📋 Gestion des Ordonnances</h2>
    <p>Consultation et validation des ordonnances</p>
</div>

<!-- Filtres -->
<div class="card">
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances" 
           class="btn <?= !isset($_GET['statut']) ? 'btn-primary' : 'btn-secondary' ?>">Toutes</a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances&statut=en_attente" 
           class="btn <?= ($_GET['statut'] ?? '') === 'en_attente' ? 'btn-primary' : 'btn-secondary' ?>">En attente</a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances&statut=validee" 
           class="btn <?= ($_GET['statut'] ?? '') === 'validee' ? 'btn-primary' : 'btn-secondary' ?>">Validées</a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances&statut=traitee" 
           class="btn <?= ($_GET['statut'] ?? '') === 'traitee' ? 'btn-primary' : 'btn-secondary' ?>">Traitées</a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances&statut=rejetee" 
           class="btn <?= ($_GET['statut'] ?? '') === 'rejetee' ? 'btn-primary' : 'btn-secondary' ?>">Rejetées</a>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>N° Ordonnance</th>
                <th>Client</th>
                <th>Médecin</th>
                <th>Date prescription</th>
                <th>Date soumission</th>
                <th>Statut</th>
                <th>Montant</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ordonnances)): ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #7f8c8d;">
                    Aucune ordonnance trouvée
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($ordonnances as $ord): ?>
                <tr>
                    <td><strong><?= escape($ord['numero_ordonnance']) ?></strong></td>
                    <td>
                        <?= escape($ord['client_nom'] . ' ' . $ord['client_prenom']) ?><br>
                        <small style="color: #7f8c8d;"><?= escape($ord['client_telephone'] ?? '') ?></small>
                    </td>
                    <td><?= escape($ord['nom_medecin']) ?></td>
                    <td><?= date('d/m/Y', strtotime($ord['date_prescription'])) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($ord['date_soumission'])) ?></td>
                    <td>
                        <?php 
                        $badges = [
                            'en_attente' => 'warning',
                            'validee' => 'success',
                            'traitee' => 'info',
                            'rejetee' => 'danger'
                        ];
                        $badge = $badges[$ord['statut']] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $ord['statut'])) ?></span>
                    </td>
                    <td><strong><?= number_format($ord['montant_total'], 2) ?> €</strong></td>
                    <td>
                        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=viewOrdonnance&id=<?= $ord['id_ordonnance'] ?>" 
                           class="btn btn-sm btn-primary">Voir détails</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
