<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = $medicament['nom_commercial'];
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../../controller/CurrencyHelper.php';

// Récupérer la devise depuis l'URL (héritée du catalogue)
$currentCurrency = $_GET['currency'] ?? 'EUR';
?>

<div style="margin-bottom: 20px;">
    <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=catalogue<?= $currentCurrency !== 'EUR' ? '&currency=' . $currentCurrency : '' ?>" class="btn btn-secondary">
        ← Retour au catalogue
    </a>
</div>

<!-- Sélecteur de devise en haut de page -->
<div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px;">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <span style="font-weight: bold;">💱 Afficher les prix en:</span>
        <div style="display: flex; gap: 10px;">
            <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewMedicament&id=<?= $medicament['id_medicament'] ?>&currency=EUR" 
               class="btn <?= $currentCurrency === 'EUR' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
                € EUR
            </a>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewMedicament&id=<?= $medicament['id_medicament'] ?>&currency=USD" 
               class="btn <?= $currentCurrency === 'USD' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
                $ USD
            </a>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=viewMedicament&id=<?= $medicament['id_medicament'] ?>&currency=TND" 
               class="btn <?= $currentCurrency === 'TND' ? 'btn-light' : 'btn-secondary' ?> btn-sm">
                د.ت TND
            </a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    <!-- Image du médicament -->
    <div>
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="width: 100%; height: 400px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: flex; align-items: center; justify-content: center;">
                <?php if (!empty($medicament['image'])): ?>
                    <img src="<?= escape(appUrl('view/uploads/')) ?>medicaments/<?= escape($medicament['image']) ?>" 
                         alt="<?= escape($medicament['nom_commercial']) ?>"
                         style="width: 100%; height: 100%; object-fit: contain; padding: 20px;"
                         onerror="this.parentElement.innerHTML='<span style=\'font-size: 120px;\'>📦</span>'">
                <?php else: ?>
                    <span style="font-size: 120px;">📦</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Informations principales -->
    <div>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                <div>
                    <h2 style="color: #667eea; margin-bottom: 10px;">
                        <?= escape($medicament['nom_commercial']) ?>
                    </h2>
                    <p style="color: #7f8c8d; font-size: 16px;">
                        <?= escape($medicament['nom_generique']) ?>
                    </p>
                </div>
                <?php if ($medicament['prescription_obligatoire']): ?>
                    <span class="badge badge-danger" style="font-size: 14px; padding: 8px 15px;">
                        ⚕️ Prescription obligatoire
                    </span>
                <?php endif; ?>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    <div>
                        <p style="color: #7f8c8d; margin-bottom: 5px; font-size: 14px;">Forme</p>
                        <p style="font-weight: bold; font-size: 16px;"><?= escape($medicament['forme']) ?></p>
                    </div>
                    <div>
                        <p style="color: #7f8c8d; margin-bottom: 5px; font-size: 14px;">Dosage</p>
                        <p style="font-weight: bold; font-size: 16px;"><?= escape($medicament['dosage']) ?></p>
                    </div>
                    <div>
                        <p style="color: #7f8c8d; margin-bottom: 5px; font-size: 14px;">Catégorie</p>
                        <p><span class="badge badge-info" style="font-size: 14px;"><?= escape($medicament['categorie']) ?></span></p>
                    </div>
                    <div>
                        <p style="color: #7f8c8d; margin-bottom: 5px; font-size: 14px;">Laboratoire</p>
                        <p style="font-weight: bold; font-size: 16px;"><?= escape($medicament['laboratoire']) ?></p>
                    </div>
                </div>
            </div>
            
            <?php if ($medicament['description']): ?>
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; margin-bottom: 10px; font-size: 18px;">📝 Description</h3>
                <p style="line-height: 1.6; color: #495057;">
                    <?= nl2br(escape($medicament['description'])) ?>
                </p>
            </div>
            <?php endif; ?>
            
            <?php if ($medicament['posologie']): ?>
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; margin-bottom: 10px; font-size: 18px;">💊 Posologie</h3>
                <p style="line-height: 1.6; color: #495057;">
                    <?= nl2br(escape($medicament['posologie'])) ?>
                </p>
            </div>
            <?php endif; ?>
            
            <?php if ($medicament['contre_indications']): ?>
            <div class="alert alert-warning" style="margin-bottom: 25px;">
                <h3 style="color: #856404; margin-bottom: 10px; font-size: 18px;">⚠️ Contre-indications</h3>
                <p style="line-height: 1.6; margin: 0;">
                    <?= nl2br(escape($medicament['contre_indications'])) ?>
                </p>
            </div>
            <?php endif; ?>
            
            <?php if ($medicament['effets_secondaires']): ?>
            <div class="alert alert-info" style="margin-bottom: 25px;">
                <h3 style="color: #0c5460; margin-bottom: 10px; font-size: 18px;">ℹ️ Effets secondaires possibles</h3>
                <p style="line-height: 1.6; margin: 0;">
                    <?= nl2br(escape($medicament['effets_secondaires'])) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Interactions médicamenteuses -->
        <?php if (!empty($interactions)): ?>
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3>⚠️ Interactions médicamenteuses connues</h3>
            </div>
            <div class="alert alert-warning">
                <p style="margin-bottom: 10px;">
                    <strong>Ce médicament peut interagir avec <?= count($interactions) ?> autre(s) médicament(s).</strong>
                </p>
                <ul style="margin-left: 20px;">
                    <?php foreach (array_slice($interactions, 0, 5) as $inter): ?>
                    <li style="margin-bottom: 10px;">
                        <strong>
                            <?php
                            // Afficher l'autre médicament de l'interaction
                            $autreMed = ($inter['id_medicament_1'] == $medicament['id_medicament']) 
                                ? $inter['med2_nom'] 
                                : $inter['med1_nom'];
                            echo escape($autreMed);
                            ?>
                        </strong>
                        <span class="badge badge-<?= $inter['niveau_gravite'] === 'majeur' || $inter['niveau_gravite'] === 'contre_indique' ? 'danger' : 'warning' ?>">
                            <?= ucfirst($inter['niveau_gravite']) ?>
                        </span>
                        <br>
                        <small><?= escape($inter['description']) ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <p style="margin-top: 15px; font-size: 14px; font-style: italic;">
                    ⚕️ Consultez toujours votre médecin ou pharmacien avant de prendre ce médicament avec d'autres traitements.
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Panel latéral -->
    <div>
        <div class="card" style="position: sticky; top: 20px;">
            <div style="text-align: center; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; margin-bottom: 20px;">
                <p style="font-size: 16px; opacity: 0.9; margin-bottom: 10px;">Prix unitaire</p>
                <p style="font-size: 42px; font-weight: bold; margin: 0;">
                    <?= CurrencyHelper::convertAndFormat($medicament['prix_unitaire'], $currentCurrency) ?>
                </p>
                <?php if ($currentCurrency !== 'EUR'): ?>
                    <p style="font-size: 14px; opacity: 0.8; margin-top: 10px;">
                        (<?= number_format($medicament['prix_unitaire'], 2) ?> €)
                    </p>
                    <p style="font-size: 12px; opacity: 0.7; margin-top: 5px;">
                        📊 Taux: 1 EUR = <?= number_format(CurrencyHelper::getRate($currentCurrency), 3) ?> <?= CurrencyHelper::getSymbol($currentCurrency) ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 15px; color: #2c3e50;">Disponibilité</h4>
                <?php if ($medicament['stock_disponible'] > 0): ?>
                    <div class="alert alert-success" style="text-align: center;">
                        <p style="font-size: 18px; margin: 0;">✓ En stock</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-error" style="text-align: center;">
                        <p style="font-size: 18px; margin: 0;">❌ Rupture de stock</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 15px; color: #2c3e50;">Informations complémentaires</h4>
                <table style="width: 100%; font-size: 14px;">
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px 0; color: #7f8c8d;">DCI</td>
                        <td style="padding: 10px 0; font-weight: bold; text-align: right;">
                            <?= escape($medicament['dci']) ?: '-' ?>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px 0; color: #7f8c8d;">Date péremption</td>
                        <td style="padding: 10px 0; font-weight: bold; text-align: right;">
                            <?= $medicament['date_peremption'] ? date('d/m/Y', strtotime($medicament['date_peremption'])) : '-' ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php if (!$medicament['prescription_obligatoire'] && $medicament['stock_disponible'] > 0): ?>
                <!-- Ajouter au panier -->
                <form method="POST" action="<?= escape(appUrl('index.php')) ?>?controller=client&action=addToCart" style="margin-bottom: 15px;">
                    <input type="hidden" name="id_medicament" value="<?= $medicament['id_medicament'] ?>">
                    <input type="hidden" name="referer" value="details">
                    
                    <label style="display: block; margin-bottom: 10px; font-weight: bold; color: #2c3e50;">
                        Quantité:
                    </label>
                    <input type="number" 
                           name="quantite" 
                           value="1" 
                           min="1" 
                           max="<?= $medicament['stock_disponible'] ?>" 
                           style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; margin-bottom: 15px;">
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        🛒 Ajouter au panier
                    </button>
                </form>
                
                <div class="alert alert-success" style="font-size: 13px; text-align: center;">
                    ✓ Médicament sans ordonnance<br>
                    🚀 Disponible immédiatement
                </div>
            <?php elseif ($medicament['prescription_obligatoire']): ?>
                <div class="alert alert-info" style="font-size: 13px;">
                    <strong>⚕️ Prescription obligatoire</strong><br>
                    Ce médicament nécessite une ordonnance valide.
                </div>
                
                <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=soumettre" class="btn btn-primary" style="width: 100%;">
                    📋 Soumettre une ordonnance
                </a>
            <?php else: ?>
                <div class="alert alert-warning" style="font-size: 13px; text-align: center;">
                    ❌ Rupture de stock<br>
                    Médicament temporairement indisponible
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
