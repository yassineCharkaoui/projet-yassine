<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView();
$pageTitle = 'Mes alertes d’interactions';
require __DIR__ . '/../layout/header.php';
?>
<div class="page-header"><h2><?= escape($pageTitle) ?></h2></div>
<?php if (!$alertesParOrdonnance): ?><div class="card"><p>Aucune alerte enregistrée.</p></div><?php endif; ?>
<?php foreach ($alertesParOrdonnance as $id => $groupe): ?><div class="card">
<h3><a href="<?= escape(buildUrl('client', 'viewOrdonnance', ['id' => $id])) ?>">Ordonnance <?= escape($groupe['ordonnance']['numero_ordonnance']) ?></a></h3>
<?php foreach ($groupe['alertes'] as $alerte): ?><div class="alert alert-warning">
<strong><?= escape($alerte['med1_nom']) ?> / <?= escape($alerte['med2_nom']) ?></strong>
<p>Gravité : <?= escape($alerte['niveau_gravite']) ?></p><p><?= nl2br(escape($alerte['description'])) ?></p>
<?php if (!empty($alerte['recommandation'])): ?><p><?= nl2br(escape($alerte['recommandation'])) ?></p><?php endif; ?>
</div><?php endforeach; ?></div><?php endforeach; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
