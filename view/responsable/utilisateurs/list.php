<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
$pageTitle = 'Utilisateurs';
require __DIR__ . '/../../layout/header.php';
?>
<div class="page-header"><h2><?= escape($pageTitle) ?></h2></div>
<div class="card">
<?php if (!$utilisateurs): ?><p>Aucun utilisateur.</p><?php else: ?>
<div style="overflow-x:auto"><table><thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th></tr></thead><tbody>
<?php foreach ($utilisateurs as $utilisateur): ?><tr><td><?= escape($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?></td>
<td><?= escape($utilisateur['email']) ?></td><td><?= escape($utilisateur['telephone'] ?? '') ?></td><td><?= escape($utilisateur['role']) ?></td></tr>
<?php endforeach; ?></tbody></table></div><?php endif; ?></div>
<?php require __DIR__ . '/../../layout/footer.php'; ?>
