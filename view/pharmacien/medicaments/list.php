<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Consultation des Médicaments';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../../controller/CurrencyHelper.php';

// Récupérer les paramètres d'URL
$currentSort = $_GET['sort'] ?? '';
$currentCurrency = $_GET['currency'] ?? 'EUR';

// Fonction helper pour construire l'URL avec paramètres
function buildUrlPharm($params = []) {
    global $currentSort, $currentCurrency;
    $defaults = [
        'controller' => 'pharmacien',
        'action' => 'consultMedicaments'
    ];
    
    if ($currentSort) $defaults['sort'] = $currentSort;
    if ($currentCurrency !== 'EUR') $defaults['currency'] = $currentCurrency;
    
    $merged = array_merge($defaults, $params);
    return appUrl('index.php') . '?' . http_build_query($merged, '', '&', PHP_QUERY_RFC3986);
}
?>

<div class="page-header">
    <h2>💊 Catalogue des Médicaments</h2>
    <p>Consultation et recherche de médicaments</p>
</div>

<!-- Barre de recherche -->
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="<?= escape(appUrl('index.php')) ?>" style="display: flex; gap: 15px; align-items: end;">
        <input type="hidden" name="controller" value="pharmacien">
        <input type="hidden" name="action" value="searchMedicaments">
        
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <label for="search">🔍 Rechercher un médicament</label>
            <input type="text" id="search" name="q" class="form-control" 
                   placeholder="Nom, catégorie, laboratoire..." 
                   value="<?= escape($_GET['q'] ?? '') ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
</div>

<!-- Barre de tri et conversion de devises -->
<div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <!-- Tri par prix -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-weight: bold;">💰 Trier par prix:</span>
            <a href="<?= buildUrlPharm(['sort' => '']) ?>" 
               class="btn <?= !$currentSort ? 'btn-light' : 'btn-secondary' ?> btn-sm">
                Par défaut
            </a>
            <a href="<?= buildUrlPharm(['sort' => 'asc']) ?>" 
               class="btn <?= $currentSort === 'asc' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
                ↑ Croissant
            </a>
            <a href="<?= buildUrlPharm(['sort' => 'desc']) ?>" 
               class="btn <?= $currentSort === 'desc' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
                ↓ Décroissant
            </a>
        </div>
        
        <!-- Conversion de devises -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-weight: bold;">💱 Devise:</span>
            <a href="<?= buildUrlPharm(['currency' => 'EUR']) ?>" 
               class="btn <?= $currentCurrency === 'EUR' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
                € EUR
            </a>
            <a href="<?= buildUrlPharm(['currency' => 'USD']) ?>" 
               class="btn <?= $currentCurrency === 'USD' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
                $ USD
            </a>
            <a href="<?= buildUrlPharm(['currency' => 'TND']) ?>" 
               class="btn <?= $currentCurrency === 'TND' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
                د.ت TND
            </a>
            <?php if ($currentCurrency !== 'EUR'): ?>
                <span style="font-size: 12px; opacity: 0.9; margin-left: 10px;">
                    📊 Taux: 1 EUR = <?= number_format(CurrencyHelper::getRate($currentCurrency), 3) ?> <?= CurrencyHelper::getSymbol($currentCurrency) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Statistiques rapides -->
<?php
$stockTotal = array_sum(array_column($medicaments, 'stock_disponible'));
$valeurStock = 0;
foreach ($medicaments as $med) {
    $valeurStock += $med['prix_unitaire'] * $med['stock_disponible'];
}
$stockCritique = count(array_filter($medicaments, function($m) {
    return $m['stock_disponible'] <= $m['stock_minimum'];
}));
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center;">
        <p style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Total Médicaments</p>
        <p style="font-size: 32px; font-weight: bold; margin: 0;"><?= count($medicaments) ?></p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; text-align: center;">
        <p style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Stock Total</p>
        <p style="font-size: 32px; font-weight: bold; margin: 0;"><?= number_format($stockTotal) ?></p>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; text-align: center;">
        <p style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Valeur Stock</p>
        <p style="font-size: 28px; font-weight: bold; margin: 0;">
            <?= CurrencyHelper::convertAndFormat($valeurStock, $currentCurrency, 0) ?>
        </p>
    </div>
    
    <?php if ($stockCritique > 0): ?>
    <div class="card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; text-align: center;">
        <p style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">⚠️ Stocks Critiques</p>
        <p style="font-size: 32px; font-weight: bold; margin: 0;"><?= $stockCritique ?></p>
    </div>
    <?php endif; ?>
</div>

<!-- Liste des médicaments -->
<?php if (empty($medicaments)): ?>
<div class="card">
    <p style="text-align: center; padding: 60px; color: #7f8c8d;">
        Aucun médicament trouvé
    </p>
</div>
<?php else: ?>
<div class="card">
    <table class="table" id="medicamentsTable">
        <thead>
            <tr>
                <th>Image</th>
                <th>ID</th>
                <th>Nom Commercial</th>
                <th>Forme / Dosage</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Stock</th>
                <th>Prescription</th>
                <th>Laboratoire</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($medicaments as $med): ?>
            <tr>
                <td style="width: 80px;">
                    <?php if (!empty($med['image'])): ?>
                        <img src="<?= escape(appUrl('view/uploads/')) ?>medicaments/<?= escape($med['image']) ?>" 
                             alt="<?= escape($med['nom_commercial']) ?>"
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27100%27 height=%27100%27%3E%3Crect fill=%27%23f0f0f0%27 width=%27100%27 height=%27100%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 font-size=%2740%27%3E📦%3C/text%3E%3C/svg%3E'">
                    <?php else: ?>
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <span style="font-size: 30px;">📦</span>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= $med['id_medicament'] ?></td>
                <td>
                    <strong><?= escape($med['nom_commercial']) ?></strong><br>
                    <small style="color: #7f8c8d;"><?= escape($med['nom_generique']) ?></small>
                </td>
                <td><?= escape($med['forme']) ?> <?= escape($med['dosage']) ?></td>
                <td><span class="badge badge-info"><?= escape($med['categorie']) ?></span></td>
                <td>
                    <strong><?= CurrencyHelper::convertAndFormat($med['prix_unitaire'], $currentCurrency) ?></strong>
                    <?php if ($currentCurrency !== 'EUR'): ?>
                        <br><small style="color: #7f8c8d;">(<?= number_format($med['prix_unitaire'], 2) ?> €)</small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($med['stock_disponible'] == 0): ?>
                        <span class="badge badge-danger">RUPTURE (0)</span>
                    <?php elseif ($med['stock_disponible'] <= $med['stock_minimum']): ?>
                        <span class="badge badge-warning">CRITIQUE (<?= $med['stock_disponible'] ?>)</span>
                    <?php else: ?>
                        <span class="badge badge-success"><?= $med['stock_disponible'] ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($med['prescription_obligatoire']): ?>
                        <span class="badge badge-danger">⚕️ Oui</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Non</span>
                    <?php endif; ?>
                </td>
                <td>
                    <small><?= escape($med['laboratoire']) ?></small>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Légende -->
<div class="card" style="margin-top: 20px; background: #f8f9fa;">
    <h4 style="margin-bottom: 15px;">ℹ️ Légende des statuts de stock</h4>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
        <div>
            <span class="badge badge-success">Stock</span> : Stock normal disponible
        </div>
        <div>
            <span class="badge badge-warning">CRITIQUE</span> : Stock en dessous du minimum
        </div>
        <div>
            <span class="badge badge-danger">RUPTURE</span> : Plus de stock disponible
        </div>
    </div>
</div>

<script>
// Filtre de recherche rapide dans le tableau
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = '🔍 Filtrer dans cette page...';
    searchInput.className = 'form-control';
    searchInput.style.marginBottom = '20px';
    searchInput.style.maxWidth = '400px';
    
    const table = document.getElementById('medicamentsTable');
    if (table) {
        table.parentElement.insertBefore(searchInput, table);
        
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
