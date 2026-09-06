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
    <?php if (!empty($searchQuery)): ?>
        <p>Résultats de recherche pour : <strong>"<?= escape($searchQuery) ?>"</strong></p>
    <?php else: ?>
        <p>Recherchez un médicament par nom, catégorie ou laboratoire</p>
    <?php endif; ?>
</div>

<div style="margin-bottom: 20px;">
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue" class="btn btn-secondary">
        ← Retour au catalogue
    </a>
</div>

<!-- Nouvelle recherche -->
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="<?= escape(appUrl('index.php')) ?>" style="display: flex; gap: 15px; align-items: end;">
        <input type="hidden" name="controller" value="client">
        <input type="hidden" name="action" value="searchMedicaments">
        
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <label for="search">🔍 Rechercher un médicament</label>
            <input type="text" 
                   id="search" 
                   name="q" 
                   class="form-control" 
                   placeholder="Nom, catégorie, laboratoire..." 
                   value="<?= escape($searchQuery) ?>"
                   autofocus>
        </div>
        
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
</div>

<!-- Sélecteur de devise -->
<?php if (!empty($medicaments)): ?>
<div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <span style="font-weight: bold;">💱 Afficher les prix en:</span>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=searchMedicaments&q=<?= urlencode($searchQuery) ?>&currency=EUR" 
           class="btn <?= $currentCurrency === 'EUR' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
            € EUR
        </a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=searchMedicaments&q=<?= urlencode($searchQuery) ?>&currency=USD" 
           class="btn <?= $currentCurrency === 'USD' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
            $ USD
        </a>
        <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=searchMedicaments&q=<?= urlencode($searchQuery) ?>&currency=TND" 
           class="btn <?= $currentCurrency === 'TND' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
            د.ت TND
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Résultats -->
<?php if (empty($searchQuery)): ?>
<div class="card">
    <div style="text-align: center; padding: 60px;">
        <div style="font-size: 80px; margin-bottom: 20px;">🔍</div>
        <h3 style="color: #7f8c8d; margin-bottom: 10px;">Commencez votre recherche</h3>
        <p style="color: #95a5a6;">
            Saisissez le nom d'un médicament, une catégorie ou un laboratoire
        </p>
    </div>
</div>
<?php elseif (empty($medicaments)): ?>
<div class="card">
    <div style="text-align: center; padding: 60px;">
        <div style="font-size: 80px; margin-bottom: 20px;">😕</div>
        <h3 style="color: #7f8c8d; margin-bottom: 10px;">Aucun résultat trouvé</h3>
        <p style="color: #95a5a6; margin-bottom: 20px;">
            Aucun médicament ne correspond à votre recherche "<strong><?= escape($searchQuery) ?></strong>"
        </p>
        <p style="color: #95a5a6;">
            💡 Essayez avec d'autres termes de recherche ou consultez le <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue">catalogue complet</a>
        </p>
    </div>
</div>
<?php else: ?>
<!-- Nombre de résultats -->
<div class="alert alert-info" style="margin-bottom: 20px;">
    <strong>✓ <?= count($medicaments) ?> résultat(s) trouvé(s)</strong>
</div>

<!-- Affichage en grille -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    <?php foreach ($medicaments as $med): ?>
    <div class="card" style="cursor: pointer; transition: transform 0.3s;" 
         onclick="window.location='<?= escape(appUrl('index.php')) ?>?controller=client&action=viewMedicament&id=<?= $med['id_medicament'] ?>&currency=<?= $currentCurrency ?>'"
         onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)'"
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.05)'">
        
        <!-- Image du médicament -->
        <div style="width: 100%; height: 200px; background: #f8f9fa; border-radius: 10px; margin-bottom: 15px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
            <?php if (!empty($med['image'])): ?>
                <img src="<?= escape(appUrl('view/uploads/')) ?>medicaments/<?= escape($med['image']) ?>" 
                     alt="<?= escape($med['nom_commercial']) ?>"
                     style="width: 100%; height: 100%; object-fit: cover;"
                     onerror="this.parentElement.innerHTML='<span style=\'font-size: 80px;\'>📦</span>'">
            <?php else: ?>
                <span style="font-size: 80px;">📦</span>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <div style="flex: 1;">
                <h3 style="color: #667eea; margin-bottom: 5px; font-size: 18px;">
                    <?= escape($med['nom_commercial']) ?>
                </h3>
                <p style="color: #7f8c8d; font-size: 13px; margin-bottom: 5px;">
                    <?= escape($med['nom_generique']) ?>
                </p>
            </div>
            <?php if ($med['prescription_obligatoire']): ?>
                <span class="badge badge-danger" style="font-size: 11px;">⚕️ Prescription</span>
            <?php endif; ?>
        </div>
        
        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <p style="margin-bottom: 5px;">
                <strong>Forme:</strong> <?= escape($med['forme']) ?> <?= escape($med['dosage']) ?>
            </p>
            <p style="margin-bottom: 5px;">
                <strong>Catégorie:</strong> <span class="badge badge-info"><?= escape($med['categorie']) ?></span>
            </p>
            <p style="margin-bottom: 0;">
                <strong>Laboratoire:</strong> <?= escape($med['laboratoire']) ?>
            </p>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="font-size: 24px; font-weight: bold; color: #667eea; margin: 0;">
                    <?= CurrencyHelper::convertAndFormat($med['prix_unitaire'], $currentCurrency) ?>
                </p>
                <?php if ($currentCurrency !== 'EUR'): ?>
                    <p style="font-size: 12px; color: #7f8c8d; margin: 0;">
                        (<?= number_format($med['prix_unitaire'], 2) ?> €)
                    </p>
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
        
        <div style="margin-top: 15px;">
            <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewMedicament&id=<?= $med['id_medicament'] ?>&currency=<?= $currentCurrency ?>" 
               class="btn btn-primary" 
               style="width: 100%;" 
               onclick="event.stopPropagation();">
                Voir les détails
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Conseils de recherche -->
<div class="card" style="margin-top: 30px; background: #f8f9fa;">
    <h4 style="margin-bottom: 15px;">💡 Conseils de recherche</h4>
    <ul style="margin-left: 20px; line-height: 2;">
        <li>Utilisez des termes généraux (ex: "aspirine" au lieu de "Aspirine UPSA 500mg")</li>
        <li>Recherchez par catégorie (ex: "antibiotique", "antidouleur")</li>
        <li>Recherchez par laboratoire (ex: "Pfizer", "Sanofi")</li>
        <li>Essayez différentes orthographes si vous ne trouvez pas le médicament</li>
    </ul>
</div>

<style>
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}
</style>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
