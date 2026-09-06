<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Ajouter un Médicament';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="page-header">
    <h2>➕ Ajouter un Médicament</h2>
    <p>Remplissez les informations du nouveau médicament</p>
</div>

<div class="card">
    <form action="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=processAddMedicament" method="POST" id="medicamentForm">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        
        <h3 style="margin-bottom: 20px; color: #667eea;">Informations générales</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="nom_commercial">Nom Commercial <span style="color: red;">*</span></label>
                <input type="text" id="nom_commercial" name="nom_commercial" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="nom_generique">Nom Générique</label>
                <input type="text" id="nom_generique" name="nom_generique" class="form-control">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="dci">DCI (Dénomination Commune Internationale)</label>
                <input type="text" id="dci" name="dci" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="laboratoire">Laboratoire</label>
                <input type="text" id="laboratoire" name="laboratoire" class="form-control">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="forme">Forme <span style="color: red;">*</span></label>
                <select id="forme" name="forme" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="Comprimé">Comprimé</option>
                    <option value="Gélule">Gélule</option>
                    <option value="Sirop">Sirop</option>
                    <option value="Injectable">Injectable</option>
                    <option value="Crème">Crème</option>
                    <option value="Pommade">Pommade</option>
                    <option value="Inhalateur">Inhalateur</option>
                    <option value="Suppositoire">Suppositoire</option>
                    <option value="Gouttes">Gouttes</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="dosage">Dosage <span style="color: red;">*</span></label>
                <input type="text" id="dosage" name="dosage" class="form-control" placeholder="Ex: 500mg, 10ml" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="categorie">Catégorie <span style="color: red;">*</span></label>
                <select id="categorie" name="categorie" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="Antalgique">Antalgique</option>
                    <option value="Anti-inflammatoire">Anti-inflammatoire</option>
                    <option value="Antibiotique">Antibiotique</option>
                    <option value="Antispasmodique">Antispasmodique</option>
                    <option value="Anxiolytique">Anxiolytique</option>
                    <option value="Bronchodilatateur">Bronchodilatateur</option>
                    <option value="Hormone">Hormone</option>
                    <option value="Inhibiteur pompe à protons">Inhibiteur pompe à protons</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="prix_unitaire">Prix Unitaire (€) <span style="color: red;">*</span></label>
                <input type="number" id="prix_unitaire" name="prix_unitaire" class="form-control" step="0.01" min="0" required>
            </div>
        </div>
        
        <h3 style="margin: 30px 0 20px; color: #667eea;">Stock et disponibilité</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="stock_disponible">Stock Disponible <span style="color: red;">*</span></label>
                <input type="number" id="stock_disponible" name="stock_disponible" class="form-control" min="0" value="0" required>
            </div>
            
            <div class="form-group">
                <label for="stock_minimum">Stock Minimum <span style="color: red;">*</span></label>
                <input type="number" id="stock_minimum" name="stock_minimum" class="form-control" min="0" value="10" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="date_peremption">Date de Péremption</label>
                <input type="date" id="date_peremption" name="date_peremption" class="form-control">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="prescription_obligatoire" name="prescription_obligatoire" style="margin-right: 8px;">
                    Prescription obligatoire
                </label>
            </div>
        </div>
        
        <h3 style="margin: 30px 0 20px; color: #667eea;">Informations médicales</h3>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
        </div>
        
        <div class="form-group">
            <label for="posologie">Posologie</label>
            <textarea id="posologie" name="posologie" class="form-control" rows="2"></textarea>
        </div>
        
        <div class="form-group">
            <label for="contre_indications">Contre-indications</label>
            <textarea id="contre_indications" name="contre_indications" class="form-control" rows="2"></textarea>
        </div>
        
        <div class="form-group">
            <label for="effets_secondaires">Effets Secondaires</label>
            <textarea id="effets_secondaires" name="effets_secondaires" class="form-control" rows="2"></textarea>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Enregistrer le médicament</button>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=responsable&action=listMedicaments" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
document.getElementById('medicamentForm').addEventListener('submit', function(e) {
    const nomCommercial = document.getElementById('nom_commercial').value.trim();
    const forme = document.getElementById('forme').value;
    const dosage = document.getElementById('dosage').value.trim();
    const categorie = document.getElementById('categorie').value;
    const prix = document.getElementById('prix_unitaire').value;
    
    if (!nomCommercial || !forme || !dosage || !categorie || !prix) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires (marqués d\'un *)');
        return false;
    }
    
    if (parseFloat(prix) <= 0) {
        e.preventDefault();
        alert('Le prix doit être supérieur à 0');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
