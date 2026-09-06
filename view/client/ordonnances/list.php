<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Mes Ordonnances';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2>📋 Mes Ordonnances</h2>
        <p>Suivez l'état de vos ordonnances</p>
    </div>
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=soumettre" class="btn btn-primary">
        + Nouvelle ordonnance
    </a>
</div>

<?php if (empty($ordonnances)): ?>
<div class="card">
    <div style="text-align: center; padding: 60px;">
        <h3 style="color: #7f8c8d; margin-bottom: 20px;">Vous n'avez pas encore d'ordonnances</h3>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=soumettre" class="btn btn-primary">
            Soumettre ma première ordonnance
        </a>
    </div>
</div>
<?php else: ?>
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>N° Ordonnance</th>
                <th>Médecin</th>
                <th>Date prescription</th>
                <th>Date soumission</th>
                <th>Statut</th>
                <th>Montant</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ordonnances as $ord): ?>
            <tr>
                <td><strong><?= escape($ord['numero_ordonnance']) ?></strong></td>
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
                    $labels = [
                        'en_attente' => 'En attente',
                        'validee' => 'Validée',
                        'traitee' => 'Traitée',
                        'rejetee' => 'Rejetée'
                    ];
                    $badge = $badges[$ord['statut']] ?? 'secondary';
                    ?>
                    <span class="badge badge-<?= $badge ?>"><?= $labels[$ord['statut']] ?? ucfirst($ord['statut']) ?></span>
                </td>
                <td><strong><?= number_format($ord['montant_total'], 2) ?> €</strong></td>
                <td>
                    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewOrdonnance&id=<?= $ord['id_ordonnance'] ?>" 
                       class="btn btn-sm btn-primary">Voir détails</a>
                    
                    <?php if ($ord['ordonnance_renouvelable'] && in_array($ord['statut'], ['validee', 'traitee'])): ?>
                    <button onclick="showRenouvelleModal(<?= $ord['id_ordonnance'] ?>, '<?= escape($ord['numero_ordonnance']) ?>')" 
                            class="btn btn-sm btn-secondary">
                        🔄 Renouveler
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Modal de renouvellement -->
<div id="modalRenouvellement" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; margin: 50px;">
        <div class="card-header">
            <h3>🔄 Demande de renouvellement</h3>
        </div>
        <form action="<?= escape(appUrl('index.php')) ?>?controller=client&action=demanderRenouvellement" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id_ordonnance" id="renouvellement_id_ordonnance">
            
            <p style="margin-bottom: 15px;">
                Demander le renouvellement de l'ordonnance <strong id="renouvellement_numero"></strong>
            </p>
            
            <div class="form-group">
                <label for="commentaire">Commentaire (optionnel)</label>
                <textarea id="commentaire" name="commentaire" class="form-control" rows="3" 
                          placeholder="Précisions sur votre demande..."></textarea>
            </div>
            
            <div class="alert alert-info" style="font-size: 14px;">
                <strong>ℹ️ Information:</strong> Votre demande sera examinée par le responsable de la pharmacie.
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Confirmer la demande</button>
                <button type="button" onclick="closeRenouvelleModal()" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRenouvelleModal(id, numero) {
    document.getElementById('renouvellement_id_ordonnance').value = id;
    document.getElementById('renouvellement_numero').textContent = numero;
    document.getElementById('modalRenouvellement').style.display = 'flex';
}

function closeRenouvelleModal() {
    document.getElementById('modalRenouvellement').style.display = 'none';
}

// Fermer au clic extérieur
window.onclick = function(event) {
    const modal = document.getElementById('modalRenouvellement');
    if (event.target === modal) {
        closeRenouvelleModal();
    }
}
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
