<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
$pageTitle = 'Détails de l’interaction';
$pageStyles = ['view/assets/css/interactions.css'];
$severityLabels = ['mineur' => 'Mineure', 'modere' => 'Modérée', 'majeur' => 'Majeure', 'contre_indique' => 'Contre-indiquée'];
require __DIR__ . '/../../layout/header.php';
?>
<section class="interactions-page"><div class="page-header interaction-heading"><div><span class="interaction-eyebrow">FICHE INTERACTION</span><h2><?= escape($interaction['med1_nom']) ?> <span class="interaction-plus">+</span> <?= escape($interaction['med2_nom']) ?></h2><p>Informations enregistrées dans le référentiel.</p></div><a class="btn btn-secondary" href="<?= escape(buildUrl('pharmacien', 'interactions')) ?>">← Retour à la liste</a></div>
<div class="card"><div class="card-header"><h3>Évaluation de l’association</h3><span class="severity severity-<?= escape($interaction['niveau_gravite']) ?>"><?= escape($severityLabels[$interaction['niveau_gravite']] ?? $interaction['niveau_gravite']) ?></span></div>
<div class="interaction-detail-grid"><div><h4>Description de l’interaction</h4><p class="interaction-prose"><?= nl2br(escape($interaction['description'])) ?></p></div><div class="interaction-recommendation"><h4>Recommandation</h4><p class="interaction-prose"><?= nl2br(escape($interaction['recommandation'] ?? 'Aucune recommandation enregistrée.')) ?></p></div></div>
<div class="interaction-actions interaction-footer"><a class="btn btn-primary" href="<?= escape(buildUrl('pharmacien', 'editInteraction', ['id' => $interaction['id_interaction']])) ?>">Modifier l’interaction</a><?php require __DIR__ . '/delete_form.php'; ?></div></div></section>
<?php require __DIR__ . '/../../layout/footer.php'; ?>
