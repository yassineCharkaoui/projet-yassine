<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
require_once __DIR__ . '/../../../controller/CurrencyHelper.php';
$pageTitle = $medicament['nom_commercial'];
$pageStyles = ['view/assets/css/medicine-detail.css'];
$currentCurrency = in_array($_GET['currency'] ?? '', ['EUR', 'USD', 'TND'], true) ? $_GET['currency'] : 'EUR';
$requiresPrescription = (bool)$medicament['prescription_obligatoire'];
$inStock = (int)$medicament['stock_disponible'] > 0;
$severityLabels = ['mineur'=>'Mineure', 'modere'=>'Modérée', 'majeur'=>'Majeure', 'contre_indique'=>'Contre-indiquée'];
require __DIR__ . '/../../layout/header.php';
?>
<section class="medicine-detail">
<div class="medicine-toolbar">
    <a class="btn btn-secondary" href="<?= escape(buildUrl('client', 'catalogue', ['currency'=>$currentCurrency])) ?>">← Retour au catalogue</a>
    <div class="medicine-currencies" aria-label="Devise des prix"><span>Afficher les prix en</span><?php foreach (['EUR'=>'€ EUR', 'USD'=>'$ USD', 'TND'=>'د.ت TND'] as $currency=>$label): ?><a class="medicine-currency <?= $currentCurrency === $currency ? 'is-active' : '' ?>" <?= $currentCurrency === $currency ? 'aria-current="true"' : '' ?> href="<?= escape(buildUrl('client', 'viewMedicament', ['id'=>$medicament['id_medicament'], 'currency'=>$currency])) ?>"><?= escape($label) ?></a><?php endforeach; ?></div>
</div>
<header class="medicine-heading"><div><span class="medicine-eyebrow">FICHE MÉDICAMENT</span><h1><?= escape($medicament['nom_commercial']) ?></h1><p><?= escape($medicament['nom_generique'] ?? '') ?></p></div><span class="badge <?= $requiresPrescription ? 'badge-danger' : 'badge-success' ?>"><?= $requiresPrescription ? 'Prescription obligatoire' : 'Sans ordonnance' ?></span></header>
<div class="medicine-grid">
    <aside class="medicine-sidebar" aria-label="Prix et disponibilité">
        <div class="card medicine-purchase">
            <div class="medicine-image">
                <?php if (!empty($medicament['image'])): ?><img src="<?= escape(appUrl('view/uploads/medicaments/' . rawurlencode($medicament['image']))) ?>" alt="<?= escape($medicament['nom_commercial']) ?>" onerror="this.hidden=true;this.nextElementSibling.hidden=false;"><span class="medicine-placeholder" hidden aria-label="Image indisponible">📦</span><?php else: ?><span class="medicine-placeholder" aria-label="Image indisponible">📦</span><?php endif; ?>
            </div>
            <div class="medicine-price"><span>Prix unitaire</span><strong><?= CurrencyHelper::convertAndFormat($medicament['prix_unitaire'], $currentCurrency) ?></strong><?php if ($currentCurrency !== 'EUR'): ?><small>Prix de référence : <?= number_format($medicament['prix_unitaire'], 2) ?> €<br>1 EUR = <?= number_format(CurrencyHelper::getRate($currentCurrency), 3) ?> <?= escape($currentCurrency) ?></small><?php endif; ?></div>
            <div class="medicine-stock"><span class="medicine-stock-dot <?= $inStock ? 'available' : '' ?>"></span><strong><?= $inStock ? 'En stock' : 'Rupture de stock' ?></strong></div>
            <?php if (!$requiresPrescription && $inStock): ?>
            <form method="POST" action="<?= escape(buildUrl('client', 'addToCart')) ?>" class="medicine-cart-form">
                <input type="hidden" name="csrf_token" value="<?= escape(generateCSRFToken()) ?>"><input type="hidden" name="id_medicament" value="<?= (int)$medicament['id_medicament'] ?>"><input type="hidden" name="referer" value="details">
                <label for="medicine-quantity">Quantité</label><input class="form-control" id="medicine-quantity" type="number" name="quantite" value="1" min="1" max="<?= (int)$medicament['stock_disponible'] ?>" required>
                <button type="submit" class="btn btn-primary">Ajouter au panier</button>
            </form>
            <?php elseif ($requiresPrescription): ?>
            <p class="medicine-prescription-note">Ce médicament nécessite une ordonnance valide.</p><a class="btn btn-primary medicine-cta" href="<?= escape(buildUrl('client', 'soumettre')) ?>">Soumettre une ordonnance</a>
            <?php else: ?><p class="medicine-prescription-note">Ce médicament est temporairement indisponible.</p><?php endif; ?>
            <dl class="medicine-extra"><div><dt>DCI</dt><dd><?= escape(($medicament['dci'] ?? '') ?: 'Non renseignée') ?></dd></div><div><dt>Date de péremption</dt><dd><?= !empty($medicament['date_peremption']) ? date('d/m/Y', strtotime($medicament['date_peremption'])) : 'Non renseignée' ?></dd></div></dl>
        </div>
    </aside>
    <div class="medicine-content">
        <article class="card"><div class="card-header"><h2>À propos de ce médicament</h2></div>
            <dl class="medicine-facts"><?php foreach (['forme'=>'Forme', 'dosage'=>'Dosage', 'categorie'=>'Catégorie', 'laboratoire'=>'Laboratoire'] as $field=>$label): ?><div><dt><?= $label ?></dt><dd><?= escape(($medicament[$field] ?? '') ?: 'Non renseigné') ?></dd></div><?php endforeach; ?></dl>
            <?php foreach (['description'=>'Description', 'posologie'=>'Posologie'] as $field=>$label): if (!empty($medicament[$field])): ?><section class="medicine-text"><h3><?= $label ?></h3><p><?= nl2br(escape($medicament[$field])) ?></p></section><?php endif; endforeach; ?>
        </article>
        <?php if (!empty($medicament['contre_indications']) || !empty($medicament['effets_secondaires'])): ?>
        <article class="card"><div class="card-header"><h2>Précautions d’utilisation</h2></div>
            <?php if (!empty($medicament['contre_indications'])): ?><section class="medicine-notice medicine-notice-warning"><h3>Contre-indications</h3><p><?= nl2br(escape($medicament['contre_indications'])) ?></p></section><?php endif; ?>
            <?php if (!empty($medicament['effets_secondaires'])): ?><section class="medicine-notice medicine-notice-info"><h3>Effets secondaires possibles</h3><p><?= nl2br(escape($medicament['effets_secondaires'])) ?></p></section><?php endif; ?>
        </article><?php endif; ?>
        <?php if (!empty($interactions)): ?>
        <article class="card"><div class="card-header"><h2>Interactions médicamenteuses connues</h2><span class="badge badge-warning"><?= count($interactions) ?> association(s)</span></div>
            <p class="medicine-intro">Ce médicament peut interagir avec les médicaments suivants :</p>
            <ul class="medicine-interactions"><?php foreach ($interactions as $interaction):
                $otherName = (int)$interaction['id_medicament_1'] === (int)$medicament['id_medicament'] ? $interaction['med2_nom'] : $interaction['med1_nom'];
                $severity = $interaction['niveau_gravite'];
            ?><li><div class="medicine-interaction-title"><h3><?= escape($otherName) ?></h3><span class="badge <?= in_array($severity, ['majeur', 'contre_indique'], true) ? 'badge-danger' : 'badge-warning' ?>"><?= escape($severityLabels[$severity] ?? $severity) ?></span></div><p><?= nl2br(escape($interaction['description'])) ?></p></li><?php endforeach; ?></ul>
            <p class="medicine-interaction-note">Consultez votre médecin ou pharmacien avant de prendre ce médicament avec d’autres traitements.</p>
        </article><?php endif; ?>
    </div>
</div></section>
<?php require __DIR__ . '/../../layout/footer.php'; ?>
