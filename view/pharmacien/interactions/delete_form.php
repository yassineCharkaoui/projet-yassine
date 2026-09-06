<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<form method="POST" action="<?= escape(buildUrl('pharmacien', 'deleteInteraction')) ?>" onsubmit="return confirm('Supprimer cette interaction ? Les interactions liées à des alertes sont conservées.');">
<input type="hidden" name="csrf_token" value="<?= escape(generateCSRFToken()) ?>"><input type="hidden" name="id" value="<?= (int)$interaction['id_interaction'] ?>"><button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
</form>
