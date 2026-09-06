<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Détails Ordonnance';
require_once __DIR__ . '/../../layout/header.php';
?>

<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=mesOrdonnances" class="btn btn-secondary">
        ← Retour à mes ordonnances
    </a>
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=generatePDF&id=<?= $ordonnance['id_ordonnance'] ?>" 
       class="btn btn-danger" 
       title="Télécharger en PDF"
       style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        📄 Télécharger PDF
    </a>
</div>

<div class="page-header">
    <h2>📋 Ordonnance <?= escape($ordonnance['numero_ordonnance']) ?></h2>
    <?php 
    $badges = [
        'en_attente' => 'warning',
        'validee' => 'success',
        'traitee' => 'info',
        'rejetee' => 'danger'
    ];
    $labels = [
        'en_attente' => 'En attente de validation',
        'validee' => 'Validée par le pharmacien',
        'traitee' => 'Traitée et délivrée',
        'rejetee' => 'Rejetée'
    ];
    $badge = $badges[$ordonnance['statut']] ?? 'secondary';
    ?>
    <span class="badge badge-<?= $badge ?>" style="font-size: 16px; padding: 10px 20px;">
        <?= $labels[$ordonnance['statut']] ?? ucfirst($ordonnance['statut']) ?>
    </span>
</div>

<!-- Informations générales -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
    <div class="card">
        <div class="card-header">
            <h3>🩺 Informations de l'ordonnance</h3>
        </div>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 12px; font-weight: bold; width: 180px; background: #f8f9fa;">Médecin prescripteur:</td>
                <td style="padding: 12px;"><?= escape($ordonnance['nom_medecin']) ?></td>
            </tr>
            <tr>
                <td style="padding: 12px; font-weight: bold; background: #f8f9fa;">Date de prescription:</td>
                <td style="padding: 12px;"><?= date('d/m/Y', strtotime($ordonnance['date_prescription'])) ?></td>
            </tr>
            <tr>
                <td style="padding: 12px; font-weight: bold; background: #f8f9fa;">Date de soumission:</td>
                <td style="padding: 12px;"><?= date('d/m/Y à H:i', strtotime($ordonnance['date_soumission'])) ?></td>
            </tr>
            <?php if ($ordonnance['date_validation']): ?>
            <tr>
                <td style="padding: 12px; font-weight: bold; background: #f8f9fa;">Date de validation:</td>
                <td style="padding: 12px;"><?= date('d/m/Y à H:i', strtotime($ordonnance['date_validation'])) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($ordonnance['pharmacien_nom']): ?>
            <tr>
                <td style="padding: 12px; font-weight: bold; background: #f8f9fa;">Pharmacien:</td>
                <td style="padding: 12px;"><?= escape($ordonnance['pharmacien_nom'] . ' ' . $ordonnance['pharmacien_prenom']) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>💰 Montant</h3>
        </div>
        <div style="text-align: center; padding: 30px;">
            <p style="font-size: 42px; font-weight: bold; color: #667eea; margin: 0;">
                <?= number_format($ordonnance['montant_total'], 2) ?> €
            </p>
        </div>
        <?php if ($ordonnance['ordonnance_renouvelable']): ?>
        <div class="alert alert-info" style="font-size: 13px; margin-top: 15px;">
            <strong>🔄 Ordonnance renouvelable</strong><br>
            <?php if ($ordonnance['date_renouvellement']): ?>
                Renouvellement possible à partir du <?= date('d/m/Y', strtotime($ordonnance['date_renouvellement'])) ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Alertes d'interactions -->
<?php if (!empty($alertes)): ?>
<div class="alert alert-warning">
    <h3 style="color: #856404; margin-bottom: 15px;">⚠️ Alertes d'interactions médicamenteuses</h3>
    <p style="margin-bottom: 15px;"><strong><?= count($alertes) ?> interaction(s) détectée(s) sur cette ordonnance.</strong></p>
    <ul style="margin-left: 20px;">
        <?php foreach ($alertes as $alerte): ?>
        <li style="margin-bottom: 10px;">
            <strong><?= escape($alerte['med1_nom']) ?></strong> ↔ <strong><?= escape($alerte['med2_nom']) ?></strong>
            <span class="badge badge-<?= in_array($alerte['niveau_gravite'], ['majeur', 'contre_indique']) ? 'danger' : 'warning' ?>">
                <?= ucfirst($alerte['niveau_gravite']) ?>
            </span>
            <br>
            <small><?= escape($alerte['description']) ?></small>
            <?php if ($alerte['recommandation']): ?>
                <br><small><em>Recommandation: <?= escape($alerte['recommandation']) ?></em></small>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <p style="margin-top: 15px; font-size: 14px; font-style: italic;">
        💡 Ces interactions ont été examinées par votre pharmacien.
    </p>
</div>
<?php endif; ?>

<!-- Liste des médicaments -->
<div class="card">
    <div class="card-header">
        <h3>💊 Médicaments prescrits</h3>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Médicament</th>
                <th>Forme/Dosage</th>
                <th>Quantité</th>
                <th>Posologie</th>
                <th>Durée</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ordonnance['medicaments'] as $med): ?>
            <tr>
                <td>
                    <strong><?= escape($med['nom_commercial']) ?></strong><br>
                    <small style="color: #7f8c8d;"><?= escape($med['nom_generique']) ?></small>
                </td>
                <td><?= escape($med['forme']) ?> <?= escape($med['dosage']) ?></td>
                <td><strong>x<?= $med['quantite'] ?></strong></td>
                <td><?= escape($med['posologie_prescrite']) ?></td>
                <td><?= escape($med['duree_traitement']) ?></td>
                <td><?= number_format($med['prix_unitaire_vente'], 2) ?> €</td>
                <td><strong><?= number_format($med['sous_total'], 2) ?> €</strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background: #f8f9fa; font-weight: bold;">
                <td colspan="6" style="text-align: right; padding: 15px;">TOTAL:</td>
                <td style="padding: 15px; font-size: 18px; color: #667eea;">
                    <?= number_format($ordonnance['montant_total'], 2) ?> €
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Commentaires -->
<?php if ($ordonnance['commentaire_pharmacien']): ?>
<div class="alert alert-info">
    <h3 style="color: #0c5460; margin-bottom: 10px;">💬 Commentaire du pharmacien</h3>
    <p style="margin: 0;"><?= nl2br(escape($ordonnance['commentaire_pharmacien'])) ?></p>
</div>
<?php endif; ?>

<?php if ($ordonnance['motif_rejet']): ?>
<div class="alert alert-error">
    <h3 style="color: #721c24; margin-bottom: 10px;">❌ Motif du rejet</h3>
    <p style="margin: 0;"><?= nl2br(escape($ordonnance['motif_rejet'])) ?></p>
</div>
<?php endif; ?>

<!-- Actions -->
<?php if ($ordonnance['ordonnance_renouvelable'] && in_array($ordonnance['statut'], ['validee', 'traitee'])): ?>
<div class="card">
    <div class="card-header">
        <h3>🔄 Renouvellement</h3>
    </div>
    <p>Cette ordonnance est renouvelable. Vous pouvez demander son renouvellement.</p>
    <button onclick="showRenouvelleModal()" class="btn btn-primary">
        Demander le renouvellement
    </button>
</div>

<!-- Modal de renouvellement -->
<div id="modalRenouvellement" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; margin: 50px;">
        <div class="card-header">
            <h3>🔄 Demande de renouvellement</h3>
        </div>
        <form action="<?= escape(appUrl('index.php')) ?>?controller=client&action=demanderRenouvellement" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id_ordonnance" value="<?= $ordonnance['id_ordonnance'] ?>">
            
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
function showRenouvelleModal() {
    document.getElementById('modalRenouvellement').style.display = 'flex';
}

function closeRenouvelleModal() {
    document.getElementById('modalRenouvellement').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('modalRenouvellement');
    if (event.target === modal) {
        closeRenouvelleModal();
    }
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
