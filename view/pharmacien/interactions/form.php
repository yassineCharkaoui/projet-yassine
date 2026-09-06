<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
$isEdit = !empty($interaction['id_interaction']);
$pageTitle = $isEdit ? 'Modifier une interaction' : 'Ajouter une interaction';
$pageStyles = ['view/assets/css/interactions.css'];
require __DIR__ . '/../../layout/header.php';
?>
<section class="interactions-page"><div class="page-header interaction-heading"><div><span class="interaction-eyebrow">RÉFÉRENTIEL PHARMACIEN</span><h2><?= escape($pageTitle) ?></h2><p>Choisissez deux médicaments différents et précisez leur interaction.</p></div><a class="btn btn-secondary" href="<?= escape(buildUrl('pharmacien', 'interactions')) ?>">← Retour à la liste</a></div>
<div class="card interaction-form-card">
<?php if ($errors): ?><div class="alert alert-error interaction-errors" role="alert"><strong>Veuillez corriger les champs suivants :</strong><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST" action="<?= escape(buildUrl('pharmacien', 'saveInteraction')) ?>">
<input type="hidden" name="id" value="<?= (int)($interaction['id_interaction'] ?? 0) ?>"><input type="hidden" name="csrf_token" value="<?= escape(generateCSRFToken()) ?>">
<div class="interaction-form-grid">
<?php foreach ([1, 2] as $number): $field = 'id_medicament_' . $number; ?>
<div class="form-group"><label for="<?= $field ?>">Médicament <?= $number ?> *</label><select class="form-control" id="<?= $field ?>" name="<?= $field ?>" required><option value="">Sélectionner un médicament</option>
<?php foreach ($medicaments as $medicament): ?><option value="<?= (int)$medicament['id_medicament'] ?>" <?= (int)($interaction[$field] ?? 0) === (int)$medicament['id_medicament'] ? 'selected' : '' ?>><?= escape($medicament['nom_commercial'] . ' — ' . ($medicament['dosage'] ?? '') . (empty($medicament['actif']) ? ' (inactif)' : '')) ?></option><?php endforeach; ?></select></div><?php endforeach; ?></div>
<div class="form-group"><label for="niveau_gravite">Niveau de gravité *</label><select class="form-control" id="niveau_gravite" name="niveau_gravite" required><option value="">Sélectionner une gravité</option><?php foreach (['mineur'=>'Mineure', 'modere'=>'Modérée', 'majeur'=>'Majeure', 'contre_indique'=>'Contre-indiquée'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($interaction['niveau_gravite'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label for="description">Description de l’interaction *</label><textarea class="form-control" id="description" name="description" rows="5" maxlength="15000" required placeholder="Décrivez l’interaction entre les deux médicaments."><?= escape($interaction['description'] ?? '') ?></textarea></div>
<div class="form-group"><label for="recommandation">Recommandation <span class="interaction-muted">(facultatif)</span></label><textarea class="form-control" id="recommandation" name="recommandation" rows="4" maxlength="15000" placeholder="Précautions ou recommandations à appliquer."><?= escape($interaction['recommandation'] ?? '') ?></textarea></div>
<div class="interaction-actions interaction-footer"><button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer les modifications' : 'Créer l’interaction' ?></button><a class="btn btn-secondary" href="<?= escape(buildUrl('pharmacien', 'interactions')) ?>">Annuler</a></div></form></div></section>
<?php require __DIR__ . '/../../layout/footer.php'; ?>
