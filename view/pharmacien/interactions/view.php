<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
$pageTitle = 'Détails de l’interaction';
require __DIR__ . '/../../layout/header.php';
?>
<div class="page-header"><h2><?= escape($pageTitle) ?></h2></div>
<div class="card">
<h3><?= escape($interaction['med1_nom']) ?> / <?= escape($interaction['med2_nom']) ?></h3>
<p>Gravité : <?= escape($interaction['niveau_gravite']) ?></p>
<h4>Description</h4><p><?= nl2br(escape($interaction['description'])) ?></p>
<h4>Recommandation</h4><p><?= nl2br(escape($interaction['recommandation'] ?? 'Aucune recommandation enregistrée.')) ?></p>
<a class="btn btn-secondary" href="<?= escape(buildUrl('pharmacien', 'interactions')) ?>">Retour aux interactions</a></div>
<?php require __DIR__ . '/../../layout/footer.php'; ?>
