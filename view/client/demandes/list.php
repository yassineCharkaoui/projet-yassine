<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
$pageTitle = 'Mes demandes de renouvellement';
require __DIR__ . '/../../layout/header.php';
?>
<div class="page-header"><h2><?= escape($pageTitle) ?></h2></div>
<div class="card">
<?php if (!$demandes): ?><p>Aucune demande de renouvellement.</p><?php else: ?>
<div style="overflow-x:auto"><table><thead><tr><th>Ordonnance</th><th>Date</th><th>Statut</th><th>Commentaire</th></tr></thead><tbody>
<?php foreach ($demandes as $demande): ?><tr>
<td><a href="<?= escape(buildUrl('client', 'viewOrdonnance', ['id' => $demande['id_ordonnance_origine']])) ?>"><?= escape($demande['numero_ordonnance']) ?></a></td>
<td><?= escape($demande['date_demande']) ?></td><td><?= escape($demande['statut']) ?></td><td><?= escape($demande['commentaire'] ?? '') ?></td>
</tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<a class="btn btn-secondary" href="<?= escape(buildUrl('client', 'mesOrdonnances')) ?>">Mes ordonnances</a></div>
<?php require __DIR__ . '/../../layout/footer.php'; ?>
