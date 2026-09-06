<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Catalogue des Médicaments';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../../controller/CurrencyHelper.php';

// Récupérer les paramètres d'URL
$currentSort = $_GET['sort'] ?? '';
$currentCurrency = $_GET['currency'] ?? 'EUR';
$currentCategorie = $_GET['categorie'] ?? '';

// Fonction locale pour construire l'URL du catalogue avec les paramètres actuels
function buildCatalogueUrl($params = []) {
    global $currentSort, $currentCurrency, $currentCategorie;
    $defaults = [
        'controller' => 'client',
        'action' => 'catalogue'
    ];
    
    if ($currentSort) $defaults['sort'] = $currentSort;
    if ($currentCurrency !== 'EUR') $defaults['currency'] = $currentCurrency;
    if ($currentCategorie) $defaults['categorie'] = $currentCategorie;
    
    $merged = array_merge($defaults, $params);
    return appUrl('index.php') . '?' . http_build_query($merged, '', '&', PHP_QUERY_RFC3986);
}
?>

<div class="page-header">
    <div>
        <h2>📖 Catalogue des Médicaments</h2>
        <p>Explorez nos produits disponibles et ajoutez-les à votre panier</p>
    </div>
    <div>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewCart" class="btn btn-primary" style="position: relative;">
            🛒 Voir mon Panier
            <?php if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])): ?>
                <span class="badge badge-danger" style="margin-left: 6px; font-size: 11px;">
                    <?= array_sum(array_column($_SESSION['panier'], 'quantite')) ?>
                </span>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- Barre de recherche et filtres -->
<div class="card">
    <form action="<?= escape(appUrl('index.php')) ?>?controller=client&action=searchMedicaments" method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="controller" value="client">
        <input type="hidden" name="action" value="searchMedicaments">
        
        <div class="form-group" style="flex: 2; min-width: 240px; margin-bottom: 0;">
            <label for="search">🔍 Rechercher un médicament</label>
            <input type="text" id="search" name="q" class="form-control" 
                   placeholder="Nom commercial, générique..." 
                   value="<?= escape($_GET['q'] ?? '') ?>">
        </div>
        
        <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
            <label for="categorie">Catégorie</label>
            <select id="categorie" name="categorie" class="form-control">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= escape($cat) ?>" <?= ($_GET['categorie'] ?? '') === $cat ? 'selected' : '' ?>>
                    <?= escape($cat) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary" style="height: 44px;">Rechercher</button>
    </form>
</div>

<!-- Barre de tri et conversion de devises -->
<div class="card" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: white;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <!-- Tri par prix -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 700; font-size: 13px;">💰 Tri Prix :</span>
            <a href="<?= buildCatalogueUrl(['sort' => '']) ?>" 
               class="btn <?= !$currentSort ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                Par défaut
            </a>
            <a href="<?= buildCatalogueUrl(['sort' => 'asc']) ?>" 
               class="btn <?= $currentSort === 'asc' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                ↑ Croissant
            </a>
            <a href="<?= buildCatalogueUrl(['sort' => 'desc']) ?>" 
               class="btn <?= $currentSort === 'desc' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                ↓ Décroissant
            </a>
        </div>
        
        <!-- Conversion de devises -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 700; font-size: 13px;">💱 Devise :</span>
            <a href="<?= buildCatalogueUrl(['currency' => 'EUR']) ?>" 
               class="btn <?= $currentCurrency === 'EUR' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                € EUR
            </a>
            <a href="<?= buildCatalogueUrl(['currency' => 'USD']) ?>" 
               class="btn <?= $currentCurrency === 'USD' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                $ USD
            </a>
            <a href="<?= buildCatalogueUrl(['currency' => 'TND']) ?>" 
               class="btn <?= $currentCurrency === 'TND' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                د.ت TND
            </a>
            <?php if ($currentCurrency !== 'EUR'): ?>
                <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">
                    (1 EUR = <?= number_format(CurrencyHelper::getRate($currentCurrency), 3) ?> <?= CurrencyHelper::getSymbol($currentCurrency) ?>)
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filtres par catégorie -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
    <a href="<?= buildCatalogueUrl(['categorie' => '']) ?>" 
       class="btn <?= !$currentCategorie ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
        Toutes
    </a>
    <?php foreach ($categories as $cat): ?>
    <a href="<?= buildCatalogueUrl(['categorie' => $cat]) ?>" 
       class="btn <?= $currentCategorie === $cat ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
        <?= escape($cat) ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Liste des médicaments -->
<?php if (empty($medicaments)): ?>
<div class="card">
    <p style="text-align: center; padding: 50px; color: var(--text-muted);">
        Aucun médicament ne correspond à votre sélection.
    </p>
</div>
<?php else: ?>
<div class="grid-responsive-3">
    <?php foreach ($medicaments as $med): ?>
    <div class="card" style="cursor: pointer; display: flex; flex-direction: column; height: 100%; margin-bottom: 0;" 
         onclick="window.location='<?= buildCatalogueUrl(['action' => 'viewMedicament', 'id' => $med['id_medicament']]) ?>'">
        
        <!-- Image du médicament -->
        <div style="width: 100%; height: 180px; background: #f8fafc; border-radius: 12px; margin-bottom: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid var(--card-border);">
            <?php if (!empty($med['image'])): ?>
                <img src="<?= escape(appUrl('view/uploads/')) ?>medicaments/<?= escape($med['image']) ?>" 
                      alt="<?= escape($med['nom_commercial']) ?>"
                      style="width: 100%; height: 100%; object-fit: cover;"
                      onerror="this.parentElement.innerHTML='<span style=\'font-size: 70px;\'>📦</span>'">
            <?php else: ?>
                <span style="font-size: 70px;">📦</span>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap: 8px;">
            <div style="flex: 1;">
                <h3 style="color: var(--primary); margin-bottom: 4px; font-size: 17px; font-weight: 800;">
                    <?= escape($med['nom_commercial']) ?>
                </h3>
                <p style="color: var(--text-muted); font-size: 13px; font-style: italic;">
                    <?= escape($med['nom_generique'] ?? '') ?>
                </p>
            </div>
            <?php if ($med['prescription_obligatoire']): ?>
                <span class="badge badge-danger" style="font-size: 11px;">⚕️ Ordonnance</span>
            <?php endif; ?>
        </div>
        
        <div style="background: #f8fafc; padding: 12px; border-radius: 10px; margin-bottom: 16px; font-size: 13px; flex-grow: 1;">
            <p style="margin-bottom: 4px;">
                <strong>Forme:</strong> <?= escape($med['forme']) ?> <?= escape($med['dosage']) ?>
            </p>
            <p style="margin-bottom: 4px;">
                <strong>Catégorie:</strong> <span class="badge badge-info"><?= escape($med['categorie']) ?></span>
            </p>
            <p style="margin-bottom: 0; color: var(--text-muted);">
                <strong>Labo:</strong> <?= escape($med['laboratoire']) ?>
            </p>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <div style="font-size: 22px; font-weight: 800; color: var(--text-dark);">
                    <?= CurrencyHelper::convertAndFormat($med['prix_unitaire'], $currentCurrency) ?>
                </div>
                <?php if ($currentCurrency !== 'EUR'): ?>
                    <div style="font-size: 11px; color: var(--text-muted);">
                        (<?= number_format($med['prix_unitaire'], 2) ?> €)
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($med['stock_disponible'] > 0): ?>
                    <span class="badge badge-success">✓ En stock</span>
                <?php else: ?>
                    <span class="badge badge-danger">Rupture</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="display: flex; gap: 8px;" onclick="event.stopPropagation();">
            <a href="<?= buildCatalogueUrl(['action' => 'viewMedicament', 'id' => $med['id_medicament']]) ?>" 
               class="btn btn-secondary btn-sm" 
               style="flex: 1;">
                👁️ Détails
            </a>
            
            <?php if (!$med['prescription_obligatoire'] && $med['stock_disponible'] > 0): ?>
                <form method="POST" action="<?= escape(appUrl('index.php')) ?>?controller=client&action=addToCart" style="flex: 1;">
                    <input type="hidden" name="id_medicament" value="<?= $med['id_medicament'] ?>">
                    <input type="hidden" name="quantite" value="1">
                    <input type="hidden" name="referer" value="catalogue">
                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                        🛒 Panier
                    </button>
                </form>
            <?php elseif ($med['prescription_obligatoire']): ?>
                <button class="btn btn-secondary btn-sm" style="flex: 1; opacity: 0.6; cursor: not-allowed;" disabled title="Sur ordonnance uniquement">
                    ⚕️ Sur ordonnance
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
