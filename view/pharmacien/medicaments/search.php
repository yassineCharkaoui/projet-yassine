<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Recherche de Médicaments';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../../controller/CurrencyHelper.php';

$currentCurrency = $_GET['currency'] ?? 'EUR';
$searchQuery = $_GET['q'] ?? '';
?>

<div class="page-header">
    <h2>🔍 Recherche de Médicaments</h2>
    <p>Résultats de recherche pour : <strong>"<?= escape($searchQuery) ?>"</strong></p>
</div>

<div style="margin-bottom: 20px;">
    <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=consultMedicaments" class="btn btn-secondary">
        ← Retour à la liste complète
    </a>
</div>

<!-- Nouvelle recherche -->
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="<?= escape(appUrl('index.php')) ?>" style="display: flex; gap: 15px; align-items: end;">
        <input type="hidden" name="controller" value="pharmacien">
        <input type="hidden" name="action" value="searchMedicaments">
        
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <label for="search">🔍 Nouvelle recherche</label>
            <input type="text" id="search" name="q" class="form-control" 
                   placeholder="Nom, catégorie, laboratoire..." 
                   value="<?= escape($searchQuery) ?>"
                   autofocus>
        </div>
        
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
</div>

<!-- Sélecteur de devise -->
<div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <span style="font-weight: bold;">💱 Afficher les prix en:</span>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=searchMedicaments&q=<?= urlencode($searchQuery) ?>&currency=EUR" 
           class="btn <?= $currentCurrency === 'EUR' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
            € EUR
        </a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=searchMedicaments&q=<?= urlencode($searchQuery) ?>&currency=USD" 
           class="btn <?= $currentCurrency === 'USD' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
            $ USD
        </a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=pharmacien&action=searchMedicaments&q=<?= urlencode($searchQuery) ?>&currency=TND" 
           class="btn <?= $currentCurrency === 'TND' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
            د.ت TND
        </a>
    </div>
</div>

<!-- Résultats -->
<?php if (empty($searchQuery)): ?>
<div class="card">
    <p style="text-align: center; padding: 60px; color: #7f8c8d;">
        Veuillez saisir un terme de recherche
    </p>
</div>
<?php elseif (empty($medicaments)): ?>
<div class="card">
    <p style="text-align: center; padding: 60px; color: #7f8c8d;">
        Aucun médicament trouvé pour "<?= escape($searchQuery) ?>"
    </p>
    <p style="text-align: center; color: #95a5a6;">
        Essayez avec un autre terme de recherche
    </p>
</div>
<?php else: ?>
<div class="alert alert-info" style="margin-bottom: 20px;">
    <strong>✓ <?= count($medicaments) ?> résultat(s) trouvé(s)</strong>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Image</th>
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

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
