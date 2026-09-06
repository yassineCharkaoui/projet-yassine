<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Détails Ordonnance';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="page-header">
    <div>
        <h2>📋 Ordonnance <?= escape($ordonnance['numero_ordonnance']) ?></h2>
        <p>Examen du dossier patient, des médicaments prescrits et des alertes</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=generatePDF&id=<?= $ordonnance['id_ordonnance'] ?>" 
           class="btn btn-danger" 
           title="Télécharger en PDF"
           style="background: linear-gradient(135deg, #ec4899 0%, #d946ef 100%);">
            📄 Télécharger PDF
        </a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances" class="btn btn-secondary">
            ← Retour à la liste
        </a>
    </div>
</div>

<!-- Informations générales -->
<div class="form-row" style="grid-template-columns: 2fr 1fr; margin-bottom: 24px;">
    <div class="card">
        <div class="card-header">
            <h3>👤 Informations Patient</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <td style="font-weight: 700; width: 140px;">Nom complet :</td>
                    <td><strong><?= escape($ordonnance['client_nom'] . ' ' . $ordonnance['client_prenom']) ?></strong></td>
                </tr>
                <tr>
                    <td style="font-weight: 700;">Email :</td>
                    <td><?= escape($ordonnance['client_email']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 700;">Téléphone :</td>
                    <td><?= escape($ordonnance['client_telephone']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 700;">Adresse :</td>
                    <td><?= escape($ordonnance['client_adresse']) ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>🩺 Prescripteur & Statut</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <td style="font-weight: 700;">Médecin :</td>
                    <td><?= escape($ordonnance['nom_medecin']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 700;">Prescription :</td>
                    <td><?= date('d/m/Y', strtotime($ordonnance['date_prescription'])) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 700;">Soumission :</td>
                    <td><?= date('d/m/Y H:i', strtotime($ordonnance['date_soumission'])) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 700;">Statut :</td>
                    <td>
                        <?php 
                        $badges = [
                            'en_attente' => 'warning',
                            'validee' => 'success',
                            'traitee' => 'info',
                            'rejetee' => 'danger'
                        ];
                        $badge = $badges[$ordonnance['statut']] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $ordonnance['statut'])) ?></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Alertes d'interactions -->
<?php if (!empty($interactions)): ?>
<div class="alert alert-warning">
    <strong>⚠️ Attention: <?= count($interactions) ?> interaction(s) médicamenteuse(s) détectée(s)</strong>
    <ul style="margin: 8px 0 0 20px;">
        <?php foreach ($interactions as $inter): ?>
        <li>
            <strong><?= escape($inter['med1_nom']) ?></strong> ↔ <strong><?= escape($inter['med2_nom']) ?></strong>
            - Gravité : <strong><?= ucfirst($inter['niveau_gravite']) ?></strong>
            <br><small><?= escape($inter['description']) ?></small>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Liste des médicaments -->
<div class="card">
    <div class="card-header">
        <h3>💊 Médicaments prescrits</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Médicament</th>
                    <th>Forme / Dosage</th>
                    <th>Quantité</th>
                    <th>Posologie</th>
                    <th>Durée</th>
                    <th>Prix unitaire</th>
                    <th>Total</th>
                    <th>Disponibilité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordonnance['medicaments'] as $med): ?>
                <tr>
                    <td>
                        <strong><?= escape($med['nom_commercial']) ?></strong><br>
                        <small style="color: var(--text-muted);"><?= escape($med['nom_generique']) ?></small>
                    </td>
                    <td><?= escape($med['forme']) ?> <?= escape($med['dosage']) ?></td>
                    <td><strong>x<?= $med['quantite'] ?></strong></td>
                    <td><?= escape($med['posologie_prescrite']) ?></td>
                    <td><?= escape($med['duree_traitement']) ?></td>
                    <td><?= number_format($med['prix_unitaire_vente'], 2) ?> €</td>
                    <td><strong><?= number_format($med['sous_total'], 2) ?> €</strong></td>
                    <td>
                        <?php if ($med['stock_disponible'] < $med['quantite']): ?>
                            <span class="badge badge-danger">Insuffisant (<?= $med['stock_disponible'] ?>)</span>
                        <?php else: ?>
                            <span class="badge badge-success">OK (<?= $med['stock_disponible'] ?>)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="6" style="text-align: right; padding: 15px;">TOTAL GENERAL:</td>
                    <td colspan="2" style="padding: 15px; font-size: 18px; color: var(--primary);">
                        <?= number_format($ordonnance['montant_total'], 2) ?> €
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Actions -->
<?php if ($ordonnance['statut'] === 'en_attente'): ?>
<div class="card">
    <div class="card-header">
        <h3>⚡ Actions de Validation Pharmacien</h3>
    </div>
    
    <div class="form-row">
        <!-- Valider -->
        <form action="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=validerOrdonnance" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id_ordonnance" value="<?= $ordonnance['id_ordonnance'] ?>">
            
            <h4 style="color: #10b981; margin-bottom: 12px; font-weight: 700;">✅ Valider l'ordonnance</h4>
            <div class="form-group">
                <label for="commentaire_validation">Commentaire d'approbation (optionnel)</label>
                <textarea id="commentaire_validation" name="commentaire" class="form-control" rows="3" placeholder="Notes de posologie, conseils au patient..."></textarea>
            </div>
            <button type="submit" class="btn btn-success" style="width: 100%;">
                ✓ Valider l'ordonnance
            </button>
        </form>
        
        <!-- Rejeter -->
        <form action="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=rejeterOrdonnance" method="POST" 
              onsubmit="return confirm('Êtes-vous sûr de vouloir rejeter cette ordonnance ?');">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id_ordonnance" value="<?= $ordonnance['id_ordonnance'] ?>">
            
            <h4 style="color: #ef4444; margin-bottom: 12px; font-weight: 700;">❌ Rejeter l'ordonnance</h4>
            <div class="form-group">
                <label for="motif_rejet">Motif du rejet <span style="color: #ef4444;">*</span></label>
                <textarea id="motif_rejet" name="motif_rejet" class="form-control" rows="3" required placeholder="Ordonnance illisible, péremption, surdosage..."></textarea>
            </div>
            <button type="submit" class="btn btn-danger" style="width: 100%;">
                ✕ Rejeter l'ordonnance
            </button>
        </form>
    </div>
</div>
<?php elseif ($ordonnance['statut'] === 'validee'): ?>
<div class="card">
    <div class="card-header">
        <h3>💰 Finaliser la délivrance et le paiement</h3>
    </div>
    
    <form action="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=traiterOrdonnance" method="POST">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <input type="hidden" name="id_ordonnance" value="<?= $ordonnance['id_ordonnance'] ?>">
        
        <div class="form-group">
            <label for="mode_paiement">Mode de paiement du client <span style="color: #ef4444;">*</span></label>
            <select id="mode_paiement" name="mode_paiement" class="form-control" required>
                <option value="especes">💵 Espèces</option>
                <option value="carte">💳 Carte bancaire</option>
                <option value="cheque">📝 Chèque</option>
                <option value="mutuelle">🏥 Tiers-payant / Mutuelle</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">
            Enregistrer la vente - <?= number_format($ordonnance['montant_total'], 2) ?> €
        </button>
    </form>
</div>
<?php endif; ?>

<!-- Commentaires -->
<?php if ($ordonnance['commentaire_pharmacien']): ?>
<div class="alert alert-info">
    <strong>💬 Commentaire du pharmacien :</strong><br>
    <?= nl2br(escape($ordonnance['commentaire_pharmacien'])) ?>
</div>
<?php endif; ?>

<?php if ($ordonnance['motif_rejet']): ?>
<div class="alert alert-error">
    <strong>❌ Motif du rejet :</strong><br>
    <?= nl2br(escape($ordonnance['motif_rejet'])) ?>
</div>
<?php endif; ?>

<div style="margin-top: 20px;">
    <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=listOrdonnances" class="btn btn-secondary">
        ← Retour à la liste
    </a>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
