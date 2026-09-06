<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = isset($medicament) ? 'Modifier un Médicament' : 'Ajouter un Médicament';
require_once __DIR__ . '/../../layout/header.php';
$isEdit = isset($medicament);
?>

<!-- Styles de validation -->
<link rel="stylesheet" href="<?= escape(appUrl('view/assets/')) ?>css/validation.css">

<style>
.image-upload-container {
    border: 2px dashed #ddd;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s;
    cursor: pointer;
}

.image-upload-container:hover {
    border-color: #667eea;
    background: #f0f2ff;
}

.image-upload-container.dragover {
    border-color: #667eea;
    background: #e8ebff;
}

.image-preview-container {
    margin-top: 20px;
    text-align: center;
}

.image-preview-container img {
    max-width: 300px;
    max-height: 300px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.remove-image-btn {
    margin-top: 10px;
    padding: 8px 20px;
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
</style>

<div class="page-header">
    <h2><?= $isEdit ? '✏️ Modifier un Médicament' : '➕ Ajouter un Médicament' ?></h2>
    <p><?= $isEdit ? 'Modifiez les informations du médicament' : 'Remplissez les informations du nouveau médicament' ?></p>
</div>

<div class="card">
    <form action="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=<?= $isEdit ? 'processEditMedicament' : 'processAddMedicament' ?>" 
          method="POST" 
          id="medicamentForm" 
          enctype="multipart/form-data">
        
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <?php if ($isEdit): ?>
        <input type="hidden" name="id_medicament" value="<?= $medicament['id_medicament'] ?>">
        <?php endif; ?>
        
        <h3 style="margin-bottom: 20px; color: #667eea;">📸 Image du Médicament</h3>
        
        <div class="form-group">
            <div class="image-upload-container" id="imageUploadZone">
                <input type="file" 
                       id="image" 
                       name="image" 
                       accept="image/*" 
                       style="display: none;"
                       data-types="jpg,jpeg,png,gif,webp"
                       data-maxsize="5">
                <div class="upload-text">
                    <p style="font-size: 48px; margin-bottom: 10px;">📷</p>
                    <p style="font-size: 16px; font-weight: 600; margin-bottom: 5px;">Cliquez pour sélectionner une image</p>
                    <p style="font-size: 14px; color: #7f8c8d;">ou glissez-déposez ici</p>
                    <p style="font-size: 12px; color: #95a5a6; margin-top: 10px;">JPG, PNG, GIF, WEBP (max 5MB)</p>
                </div>
            </div>
            
            <div class="image-preview-container" id="imagePreview" style="display: none;">
                <img id="previewImg" src="" alt="Aperçu">
                <br>
                <button type="button" class="remove-image-btn" onclick="removeImage()">🗑️ Supprimer l'image</button>
            </div>
            
            <?php if ($isEdit && !empty($medicament['image'])): ?>
            <div class="image-preview-container">
                <p style="color: #666; margin-bottom: 10px;">Image actuelle :</p>
                <img src="<?= escape(appUrl('view/uploads/')) ?>medicaments/<?= escape($medicament['image'] ?? '') ?>" alt="Image actuelle" style="max-width: 200px;">
            </div>
            <?php endif; ?>
        </div>
        
        <hr style="margin: 30px 0;">
        
        <h3 style="margin-bottom: 20px; color: #667eea;">📋 Informations Générales</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="nom_commercial" class="required-label">Nom Commercial</label>
                <input type="text" 
                       id="nom_commercial" 
                       name="nom_commercial" 
                       class="form-control"
                       data-required="true"
                       data-minlength="2"
                       data-maxlength="200"
                       value="<?= $isEdit ? escape($medicament['nom_commercial'] ?? '') : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="nom_generique">Nom Générique</label>
                <input type="text" 
                       id="nom_generique" 
                       name="nom_generique" 
                       class="form-control"
                       data-maxlength="200"
                       value="<?= $isEdit ? escape($medicament['nom_generique'] ?? '') : '' ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="dci">DCI (Dénomination Commune Internationale)</label>
                <input type="text" 
                       id="dci" 
                       name="dci" 
                       class="form-control"
                       value="<?= $isEdit ? escape($medicament['dci'] ?? '') : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="laboratoire">Laboratoire</label>
                <input type="text" 
                       id="laboratoire" 
                       name="laboratoire" 
                       class="form-control"
                       value="<?= $isEdit ? escape($medicament['laboratoire'] ?? '') : '' ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="forme" class="required-label">Forme</label>
                <select id="forme" name="forme" class="form-control" data-required="true">
                    <option value="">-- Sélectionner --</option>
                    <option value="Comprimé" <?= $isEdit && ($medicament['forme'] ?? '') == 'Comprimé' ? 'selected' : '' ?>>Comprimé</option>
                    <option value="Gélule" <?= $isEdit && ($medicament['forme'] ?? '') == 'Gélule' ? 'selected' : '' ?>>Gélule</option>
                    <option value="Sirop" <?= $isEdit && ($medicament['forme'] ?? '') == 'Sirop' ? 'selected' : '' ?>>Sirop</option>
                    <option value="Injectable" <?= $isEdit && ($medicament['forme'] ?? '') == 'Injectable' ? 'selected' : '' ?>>Injectable</option>
                    <option value="Crème" <?= $isEdit && ($medicament['forme'] ?? '') == 'Crème' ? 'selected' : '' ?>>Crème</option>
                    <option value="Pommade" <?= $isEdit && ($medicament['forme'] ?? '') == 'Pommade' ? 'selected' : '' ?>>Pommade</option>
                    <option value="Inhalateur" <?= $isEdit && ($medicament['forme'] ?? '') == 'Inhalateur' ? 'selected' : '' ?>>Inhalateur</option>
                    <option value="Suppositoire" <?= $isEdit && ($medicament['forme'] ?? '') == 'Suppositoire' ? 'selected' : '' ?>>Suppositoire</option>
                    <option value="Gouttes" <?= $isEdit && ($medicament['forme'] ?? '') == 'Gouttes' ? 'selected' : '' ?>>Gouttes</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="dosage" class="required-label">Dosage</label>
                <input type="text" 
                       id="dosage" 
                       name="dosage" 
                       class="form-control" 
                       placeholder="Ex: 500mg, 10ml"
                       data-required="true"
                       value="<?= $isEdit ? escape($medicament['dosage'] ?? '') : '' ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="categorie" class="required-label">Catégorie</label>
                <select id="categorie" name="categorie" class="form-control" data-required="true">
                    <option value="">-- Sélectionner --</option>
                    <option value="Antalgique" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Antalgique' ? 'selected' : '' ?>>Antalgique</option>
                    <option value="Anti-inflammatoire" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Anti-inflammatoire' ? 'selected' : '' ?>>Anti-inflammatoire</option>
                    <option value="Antibiotique" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Antibiotique' ? 'selected' : '' ?>>Antibiotique</option>
                    <option value="Antispasmodique" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Antispasmodique' ? 'selected' : '' ?>>Antispasmodique</option>
                    <option value="Anxiolytique" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Anxiolytique' ? 'selected' : '' ?>>Anxiolytique</option>
                    <option value="Bronchodilatateur" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Bronchodilatateur' ? 'selected' : '' ?>>Bronchodilatateur</option>
                    <option value="Corticoïde" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Corticoïde' ? 'selected' : '' ?>>Corticoïde</option>
                    <option value="Hormone" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Hormone' ? 'selected' : '' ?>>Hormone</option>
                    <option value="Anti-acide" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Anti-acide' ? 'selected' : '' ?>>Anti-acide</option>
                    <option value="Autre" <?= $isEdit && ($medicament['categorie'] ?? '') == 'Autre' ? 'selected' : '' ?>>Autre</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="prescription_obligatoire">Prescription Obligatoire</label>
                <select id="prescription_obligatoire" name="prescription_obligatoire" class="form-control">
                    <option value="0" <?= $isEdit && !$medicament['prescription_obligatoire'] ? 'selected' : '' ?>>Non</option>
                    <option value="1" <?= $isEdit && $medicament['prescription_obligatoire'] ? 'selected' : '' ?>>Oui</option>
                </select>
            </div>
        </div>
        
        <hr style="margin: 30px 0;">
        
        <h3 style="margin-bottom: 20px; color: #667eea;">💰 Prix et Stock</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="prix_unitaire" class="required-label">Prix Unitaire (€)</label>
                <input type="text" 
                       id="prix_unitaire" 
                       name="prix_unitaire" 
                       class="form-control"
                       placeholder="0.00"
                       data-required="true"
                       data-decimal="true"
                       data-min="0"
                       value="<?= $isEdit ? $medicament['prix_unitaire'] : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="stock_disponible" class="required-label">Stock Disponible</label>
                <input type="number" 
                       id="stock_disponible" 
                       name="stock_disponible" 
                       class="form-control"
                       data-required="true"
                       data-min="0"
                       value="<?= $isEdit ? $medicament['stock_disponible'] : '0' ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="stock_minimum" class="required-label">Stock Minimum</label>
                <input type="number" 
                       id="stock_minimum" 
                       name="stock_minimum" 
                       class="form-control"
                       data-required="true"
                       data-min="0"
                       value="<?= $isEdit ? $medicament['stock_minimum'] : '10' ?>">
            </div>
            
            <div class="form-group">
                <label for="date_peremption">Date de Péremption</label>
                <input type="date" 
                       id="date_peremption" 
                       name="date_peremption" 
                       class="form-control"
                       data-min="<?= date('Y-m-d') ?>"
                       value="<?= $isEdit ? escape($medicament['date_peremption'] ?? '') : '' ?>">
            </div>
        </div>
        
        <hr style="margin: 30px 0;">
        
        <h3 style="margin-bottom: 20px; color: #667eea;">📝 Informations Médicales</h3>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" 
                      name="description" 
                      class="form-control" 
                      rows="3"
                      data-maxlength="500"><?= $isEdit ? escape($medicament['description'] ?? '') : '' ?></textarea>
            <div class="char-counter" id="descriptionCounter">0 / 500</div>
        </div>
        
        <div class="form-group">
            <label for="posologie">Posologie</label>
            <textarea id="posologie" 
                      name="posologie" 
                      class="form-control" 
                      rows="2"><?= $isEdit ? escape($medicament['posologie'] ?? '') : '' ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="contre_indications">Contre-indications</label>
            <textarea id="contre_indications" 
                      name="contre_indications" 
                      class="form-control" 
                      rows="3"><?= $isEdit ? escape($medicament['contre_indications'] ?? '') : '' ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="effets_secondaires">Effets Secondaires</label>
            <textarea id="effets_secondaires" 
                      name="effets_secondaires" 
                      class="form-control" 
                      rows="3"><?= $isEdit ? escape($medicament['effets_secondaires'] ?? '') : '' ?></textarea>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? '💾 Enregistrer les modifications' : '➕ Ajouter le médicament' ?>
            </button>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listMedicaments" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<!-- Script de validation -->
<script src="<?= escape(appUrl('view/assets/')) ?>js/validation.js"></script>
<script>
// Initialiser la validation du formulaire
const validator = new FormValidator('medicamentForm', {
    realTime: true,
    showSuccess: true
});

// Gestion de l'upload d'image
const imageInput = document.getElementById('image');
const uploadZone = document.getElementById('imageUploadZone');
const previewContainer = document.getElementById('imagePreview');
const previewImg = document.getElementById('previewImg');

// Clic sur la zone d'upload
uploadZone.addEventListener('click', () => {
    imageInput.click();
});

// Changement de fichier
imageInput.addEventListener('change', (e) => {
    handleImageSelect(e.target.files[0]);
});

// Drag & Drop
uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    
    if (e.dataTransfer.files.length > 0) {
        imageInput.files = e.dataTransfer.files;
        handleImageSelect(e.dataTransfer.files[0]);
    }
});

function handleImageSelect(file) {
    if (!file) return;
    
    // Vérifier le type
    if (!file.type.startsWith('image/')) {
        alert('Veuillez sélectionner une image valide');
        return;
    }
    
    // Vérifier la taille (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        alert('L\'image est trop volumineuse (max 5MB)');
        return;
    }
    
    // Afficher la preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImg.src = e.target.result;
        previewContainer.style.display = 'block';
        uploadZone.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    imageInput.value = '';
    previewContainer.style.display = 'none';
    uploadZone.style.display = 'block';
}

// Compteur de caractères pour la description
const descriptionField = document.getElementById('description');
const descriptionCounter = document.getElementById('descriptionCounter');

descriptionField.addEventListener('input', () => {
    const length = descriptionField.value.length;
    const maxLength = 500;
    descriptionCounter.textContent = `${length} / ${maxLength}`;
    
    if (length > maxLength * 0.9) {
        descriptionCounter.classList.add('warning');
    } else {
        descriptionCounter.classList.remove('warning');
    }
    
    if (length > maxLength) {
        descriptionCounter.classList.add('danger');
        descriptionField.value = descriptionField.value.substring(0, maxLength);
    } else {
        descriptionCounter.classList.remove('danger');
    }
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
