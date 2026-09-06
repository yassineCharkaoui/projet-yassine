<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Demandes de Renouvellement';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="page-header">
    <div>
        <h2>🔄 Demandes de Renouvellement</h2>
        <p>Gestion et arbitrage des demandes de renouvellement d'ordonnances</p>
    </div>
</div>

<div class="card">
    <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listDemandes" 
           class="btn <?= !isset($_GET['statut']) ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Toutes</a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listDemandes&statut=en_attente" 
           class="btn <?= ($_GET['statut'] ?? '') === 'en_attente' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">En attente</a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listDemandes&statut=approuvee" 
           class="btn <?= ($_GET['statut'] ?? '') === 'approuvee' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Approuvées</a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listDemandes&statut=rejetee" 
           class="btn <?= ($_GET['statut'] ?? '') === 'rejetee' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Rejetées</a>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Demande</th>
                    <th>Client Patient</th>
                    <th>Ordonnance d'Origine</th>
                    <th>Date de la Demande</th>
                    <th>Statut</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($demandes)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Aucune demande trouvée
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($demandes as $demande): ?>
                    <tr>
                        <td><strong>#<?= $demande['id_demande'] ?></strong></td>
                        <td><strong><?= escape($demande['client_nom'] . ' ' . $demande['client_prenom']) ?></strong></td>
                        <td><strong><?= escape($demande['numero_ordonnance']) ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></td>
                        <td>
                            <?php 
                            $badges = [
                                'en_attente' => 'warning',
                                'approuvee' => 'success',
                                'rejetee' => 'danger'
                            ];
                            $badge = $badges[$demande['statut']] ?? 'secondary';
                            ?>
                            <span class="badge badge-<?= $badge ?>"><?= ucfirst($demande['statut']) ?></span>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($demande['statut'] === 'en_attente'): ?>
                            <button onclick="showModal(<?= $demande['id_demande'] ?>, 'approve')" 
                                    class="btn btn-sm btn-success">✓ Approuver</button>
                            <button onclick="showModal(<?= $demande['id_demande'] ?>, 'reject')" 
                                    class="btn btn-sm btn-danger">✕ Rejeter</button>
                            <?php else: ?>
                            <span style="color: var(--text-muted); font-size: 13px;">Traitée</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Approuver -->
<div id="modalApprove" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; padding: 16px;">
    <div class="card" style="max-width: 500px; width: 100%; margin: 0; border-radius: 16px;">
        <div class="card-header">
            <h3 style="color: #10b981;">✅ Approuver la demande</h3>
        </div>
        <form action="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=approuverDemande" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id_demande" id="approve_id_demande">
            
            <div class="form-group">
                <label for="commentaire_approve">Commentaire d'approbation (optionnel)</label>
                <textarea id="commentaire_approve" name="commentaire" class="form-control" rows="3" placeholder="Notes pour le pharmacien ou le patient..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-success" style="flex: 1;">Confirmer l'approbation</button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rejeter -->
<div id="modalReject" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; padding: 16px;">
    <div class="card" style="max-width: 500px; width: 100%; margin: 0; border-radius: 16px;">
        <div class="card-header">
            <h3 style="color: #ef4444;">❌ Rejeter la demande</h3>
        </div>
        <form action="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=rejeterDemande" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id_demande" id="reject_id_demande">
            
            <div class="form-group">
                <label for="commentaire_reject">Motif du rejet <span style="color: #ef4444;">*</span></label>
                <textarea id="commentaire_reject" name="commentaire" class="form-control" rows="3" required placeholder="Motif explicatif obligatoire..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-danger" style="flex: 1;">Confirmer le rejet</button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function showModal(id, type) {
    if (type === 'approve') {
        document.getElementById('approve_id_demande').value = id;
        document.getElementById('modalApprove').style.display = 'flex';
    } else {
        document.getElementById('reject_id_demande').value = id;
        document.getElementById('modalReject').style.display = 'flex';
    }
}

function closeModal() {
    document.getElementById('modalApprove').style.display = 'none';
    document.getElementById('modalReject').style.display = 'none';
}

window.onclick = function(event) {
    const modalApprove = document.getElementById('modalApprove');
    const modalReject = document.getElementById('modalReject');
    if (event.target === modalApprove) closeModal();
    if (event.target === modalReject) closeModal();
}
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
