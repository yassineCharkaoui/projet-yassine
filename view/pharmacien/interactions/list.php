<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
$pageTitle = 'Interactions médicamenteuses';
$pageStyles = ['view/assets/css/interactions.css'];
$severityLabels = ['mineur' => 'Mineure', 'modere' => 'Modérée', 'majeur' => 'Majeure', 'contre_indique' => 'Contre-indiquée'];
require __DIR__ . '/../../layout/header.php';
?>
<section class="interactions-page">
<div class="page-header interaction-heading">
<div><span class="interaction-eyebrow">RÉFÉRENTIEL PHARMACIEN</span><h2><?= escape($pageTitle) ?></h2><p>Gérez les associations de médicaments et leurs recommandations.</p></div>
<a class="btn btn-primary" href="<?= escape(buildUrl('pharmacien', 'addInteraction')) ?>">+ Ajouter une interaction</a></div>
<div class="card"><div class="card-header"><h3><?= count($interactions) ?> interaction(s) enregistrée(s)</h3><span class="interaction-muted">Répertoire des associations</span></div>
<div class="interaction-filters"><div class="form-group"><label for="interaction-search">Rechercher un médicament</label><input class="form-control" id="interaction-search" type="search" placeholder="Ex. Doliprane, Aspirine…"></div>
<div class="form-group"><label for="interaction-severity">Gravité</label><select class="form-control" id="interaction-severity"><option value="">Toutes les gravités</option><?php foreach ($severityLabels as $value => $label): ?><option value="<?= escape($value) ?>"><?= escape($label) ?></option><?php endforeach; ?></select></div></div>
<?php if (!$interactions): ?><div class="interaction-empty"><h3>Votre répertoire est vide</h3><p>Ajoutez une première association de médicaments pour commencer.</p></div><?php else: ?>
<div class="table-responsive"><table class="table interaction-table"><thead><tr><th scope="col">Association de médicaments</th><th scope="col">Gravité</th><th scope="col">Actions</th></tr></thead><tbody>
<?php foreach ($interactions as $interaction): ?>
<tr data-interaction data-search="<?= escape($interaction['med1_nom'] . ' ' . $interaction['med2_nom']) ?>" data-severity="<?= escape($interaction['niveau_gravite']) ?>">
<td><strong><?= escape($interaction['med1_nom']) ?></strong><span class="interaction-plus"> + </span><strong><?= escape($interaction['med2_nom']) ?></strong></td>
<td><span class="severity severity-<?= escape($interaction['niveau_gravite']) ?>"><?= escape($severityLabels[$interaction['niveau_gravite']] ?? $interaction['niveau_gravite']) ?></span></td>
<td><div class="interaction-actions"><a class="btn btn-secondary btn-sm" href="<?= escape(buildUrl('pharmacien', 'viewInteraction', ['id' => $interaction['id_interaction']])) ?>">Consulter</a><a class="btn btn-primary btn-sm" href="<?= escape(buildUrl('pharmacien', 'editInteraction', ['id' => $interaction['id_interaction']])) ?>">Modifier</a>
<?php require __DIR__ . '/delete_form.php'; ?>
</div></td></tr><?php endforeach; ?></tbody></table></div>
<p class="interaction-empty" id="interaction-no-results" hidden>Aucune interaction ne correspond à votre recherche.</p><?php endif; ?>
<p class="interaction-note">Les interactions liées à des alertes d’ordonnances ne peuvent pas être supprimées afin de conserver leur historique.</p></div></section>
<script>
(() => {
 const search = document.getElementById('interaction-search'), severity = document.getElementById('interaction-severity');
 const rows = [...document.querySelectorAll('[data-interaction]')];
 const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
 function filter() {
  let visible = 0;
  for (const row of rows) { row.hidden = !normalize(row.dataset.search).includes(normalize(search.value.trim())) || (severity.value !== '' && row.dataset.severity !== severity.value); if (!row.hidden) visible++; }
  const empty = document.getElementById('interaction-no-results'); if (empty) empty.hidden = visible !== 0;
 }
 search.addEventListener('input', filter); severity.addEventListener('change', filter);
})();
</script>
<?php require __DIR__ . '/../../layout/footer.php'; ?>
