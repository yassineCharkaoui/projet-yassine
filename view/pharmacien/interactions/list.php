<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
$pageTitle = 'Interactions médicamenteuses';
require __DIR__ . '/../../layout/header.php';
?>
<div class="page-header"><h2><?= escape($pageTitle) ?></h2></div>
<div class="card">
<?php if (!$interactions): ?><p>Aucune interaction enregistrée.</p><?php else: ?>
<div style="overflow-x:auto"><table><thead><tr><th>Médicament 1</th><th>Médicament 2</th><th>Gravité</th><th>Détails</th></tr></thead><tbody>
<?php foreach ($interactions as $interaction): ?><tr><td><?= escape($interaction['med1_nom']) ?></td><td><?= escape($interaction['med2_nom']) ?></td>
<td><?= escape($interaction['niveau_gravite']) ?></td><td><a href="<?= escape(buildUrl('pharmacien', 'viewInteraction', ['id' => $interaction['id_interaction']])) ?>">Consulter</a></td></tr>
<?php endforeach; ?></tbody></table></div><?php endif; ?></div>
<?php require __DIR__ . '/../../layout/footer.php'; ?>
