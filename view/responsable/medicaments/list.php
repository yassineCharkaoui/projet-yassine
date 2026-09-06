<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Gestion des Médicaments';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../../controller/CurrencyHelper.php';

// Récupérer les paramètres d'URL
$currentSort = $_GET['sort'] ?? '';
$currentCurrency = $_GET['currency'] ?? 'EUR';

// Fonction helper pour construire l'URL avec paramètres
function buildUrlResp($params = []) {
    global $currentSort, $currentCurrency;
    $defaults = [
        'controller' => 'responsable',
        'action' => 'listMedicaments'
    ];
    
    if ($currentSort) $defaults['sort'] = $currentSort;
    if ($currentCurrency !== 'EUR') $defaults['currency'] = $currentCurrency;
    
    $merged = array_merge($defaults, $params);
    return appUrl('index.php') . '?' . http_build_query($merged, '', '&', PHP_QUERY_RFC3986);
}
?>

<div class="page-header">
    <div>
        <h2>💊 Gestion du Stock Médicaments</h2>
        <p>Inventaire complet, modification et suivi des produits de la pharmacie</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=searchMedicaments" class="btn btn-secondary">🔍 Recherche avancée</a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=addMedicament" class="btn btn-primary">+ Ajouter un médicament</a>
    </div>
</div>

<!-- Barre de tri et conversion de devises -->
<div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: white;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <!-- Tri par prix -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 700; font-size: 13px;">💰 Trier par prix :</span>
            <a href="<?= buildUrlResp(['sort' => '']) ?>" 
               class="btn <?= !$currentSort ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                Par défaut
            </a>
            <a href="<?= buildUrlResp(['sort' => 'asc']) ?>" 
               class="btn <?= $currentSort === 'asc' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                ↑ Croissant
            </a>
            <a href="<?= buildUrlResp(['sort' => 'desc']) ?>" 
               class="btn <?= $currentSort === 'desc' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                ↓ Décroissant
            </a>
        </div>
        
        <!-- Conversion de devises -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 700; font-size: 13px;">💱 Devise d'affichage :</span>
            <a href="<?= buildUrlResp(['currency' => 'EUR']) ?>" 
               class="btn <?= $currentCurrency === 'EUR' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                € EUR
            </a>
            <a href="<?= buildUrlResp(['currency' => 'USD']) ?>" 
               class="btn <?= $currentCurrency === 'USD' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                $ USD
            </a>
            <a href="<?= buildUrlResp(['currency' => 'TND']) ?>" 
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

<div class="card">
    <div class="card-header">
        <h3>📋 Liste globale des produits</h3>
    </div>
    
    <div class="table-responsive">
        <table class="table" id="medicamentsTable">
            <thead>
                <tr>
                    <th style="width: 70px;">Visuel</th>
                    <th>ID</th>
                    <th>Nom Commercial & Générique</th>
                    <th>Forme / Dosage</th>
                    <th>Catégorie</th>
                    <th>Prix unitaire</th>
                    <th>Stock dispo</th>
                    <th>Prescription</th>
                    <th style="text-align: center; width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($medicaments)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Aucun médicament enregistré dans le catalogue
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($medicaments as $med): ?>
                    <tr>
                        <td>
                            <?php if (!empty($med['image'])): ?>
                                <img src="<?= escape(appUrl('view/uploads/')) ?>medicaments/<?= escape($med['image']) ?>" 
                                     alt="<?= escape($med['nom_commercial']) ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 10px; border: 1px solid var(--card-border);"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27100%27 height=%27100%27%3E%3Crect fill=%27%23f8fafc%27 width=%27100%27 height=%27100%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 font-size=%2735%27%3E📦%3C/text%3E%3C/svg%3E'">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #f8fafc; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--card-border);">
                                    <span style="font-size: 26px;">📦</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong>#<?= $med['id_medicament'] ?></strong></td>
                        <td>
                            <strong><?= escape($med['nom_commercial']) ?></strong><br>
                            <small style="color: var(--text-muted);"><?= escape($med['nom_generique'] ?? '') ?></small>
                        </td>
                        <td><?= escape($med['forme']) ?> <?= escape($med['dosage']) ?></td>
                        <td><span class="badge badge-info"><?= escape($med['categorie']) ?></span></td>
                        <td>
                            <strong style="color: var(--primary);"><?= CurrencyHelper::convertAndFormat($med['prix_unitaire'], $currentCurrency) ?></strong>
                            <?php if ($currentCurrency !== 'EUR'): ?>
                                <br><small style="color: var(--text-muted);">(<?= number_format($med['prix_unitaire'], 2) ?> €)</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($med['stock_disponible'] == 0): ?>
                                <span class="badge badge-danger">RUPTURE (0)</span>
                            <?php elseif ($med['stock_disponible'] <= $med['stock_minimum']): ?>
                                <span class="badge badge-warning">CRITIQUE (<?= $med['stock_disponible'] ?>)</span>
                            <?php else: ?>
                                <span class="badge badge-success"><?= $med['stock_disponible'] ?> en stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($med['prescription_obligatoire']): ?>
                                <span class="badge badge-danger">Requise</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Libre</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=editMedicament&id=<?= $med['id_medicament'] ?>" 
                               class="btn btn-sm btn-primary" title="Modifier">✏️</a>
                            <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=deleteMedicament&id=<?= $med['id_medicament'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?')"
                               title="Supprimer">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = '🔍 Filtre rapide...';
    searchInput.className = 'form-control';
    searchInput.style.marginBottom = '16px';
    searchInput.style.maxWidth = '320px';
    
    const table = document.getElementById('medicamentsTable');
    table.parentElement.insertBefore(searchInput, table);
    
    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let row of rows) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
