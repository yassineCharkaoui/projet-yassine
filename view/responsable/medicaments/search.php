<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Recherche Avancée de Médicaments';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="page-header">
    <h2>🔍 Recherche Avancée de Médicaments</h2>
    <p>Recherche multicritères</p>
</div>

<div class="card">
    <form action="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=searchMedicaments" method="GET">
        <input type="hidden" name="controller" value="responsable">
        <input type="hidden" name="action" value="searchMedicaments">
        
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom du médicament</label>
                <input type="text" id="nom" name="nom" class="form-control" 
                       value="<?= escape($_GET['nom'] ?? '') ?>"
                       placeholder="Nom commercial ou générique">
            </div>
            
            <div class="form-group">
                <label for="laboratoire">Laboratoire</label>
                <input type="text" id="laboratoire" name="laboratoire" class="form-control"
                       value="<?= escape($_GET['laboratoire'] ?? '') ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
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
            
            <div class="form-group">
                <label for="forme">Forme</label>
                <select id="forme" name="forme" class="form-control">
                    <option value="">Toutes les formes</option>
                    <?php foreach ($formes as $f): ?>
                    <option value="<?= escape($f) ?>" <?= ($_GET['forme'] ?? '') === $f ? 'selected' : '' ?>>
                        <?= escape($f) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Prescription obligatoire</label>
            <div style="display: flex; gap: 20px;">
                <label><input type="radio" name="prescription_obligatoire" value="" <?= !isset($_GET['prescription_obligatoire']) ? 'checked' : '' ?>> Tous</label>
                <label><input type="radio" name="prescription_obligatoire" value="1" <?= ($_GET['prescription_obligatoire'] ?? '') === '1' ? 'checked' : '' ?>> Oui</label>
                <label><input type="radio" name="prescription_obligatoire" value="0" <?= ($_GET['prescription_obligatoire'] ?? '') === '0' ? 'checked' : '' ?>> Non</label>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=searchMedicaments" class="btn btn-secondary">Réinitialiser</a>
        </div>
    </form>
</div>

<?php if (isset($_GET['nom']) || isset($_GET['categorie']) || isset($_GET['forme']) || isset($_GET['laboratoire'])): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>📊 Résultats de la recherche (<?= count($medicaments) ?> trouvé(s))</h3>
    </div>
    
    <?php if (empty($medicaments)): ?>
        <p style="text-align: center; padding: 40px; color: #7f8c8d;">Aucun médicament ne correspond à vos critères</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nom Commercial</th>
                    <th>Forme / Dosage</th>
                    <th>Catégorie</th>
                    <th>Laboratoire</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Prescription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($medicaments as $med): ?>
                <tr>
                    <td>
                        <strong><?= escape($med['nom_commercial']) ?></strong><br>
                        <small style="color: #7f8c8d;"><?= escape($med['nom_generique']) ?></small>
                    </td>
                    <td><?= escape($med['forme']) ?> <?= escape($med['dosage']) ?></td>
                    <td><span class="badge badge-info"><?= escape($med['categorie']) ?></span></td>
                    <td><?= escape($med['laboratoire']) ?></td>
                    <td><strong><?= number_format($med['prix_unitaire'], 2) ?> €</strong></td>
                    <td>
                        <?php if ($med['stock_disponible'] == 0): ?>
                            <span class="badge badge-danger">RUPTURE</span>
                        <?php elseif ($med['stock_disponible'] <= $med['stock_minimum']): ?>
                            <span class="badge badge-warning">CRITIQUE (<?= $med['stock_disponible'] ?>)</span>
                        <?php else: ?>
                            <span class="badge badge-success"><?= $med['stock_disponible'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $med['prescription_obligatoire'] ? '<span class="badge badge-danger">Oui</span>' : '<span class="badge badge-secondary">Non</span>' ?>
                    </td>
                    <td>
                        <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=editMedicament&id=<?= $med['id_medicament'] ?>" 
                           class="btn btn-sm btn-primary">✏️ Modifier</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
