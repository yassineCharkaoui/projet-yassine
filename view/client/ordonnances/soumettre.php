<?php
require_once __DIR__ . '/../../../controller/config.php';
requireRoutedView();
?>
<?php
$pageTitle = 'Soumettre une Ordonnance';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="page-header">
    <h2>📋 Soumettre une Ordonnance</h2>
    <p>Remplissez les informations de votre ordonnance</p>
</div>

<div class="card">
    <form action="<?= escape(appUrl('index.php')) ?>?controller=client&action=processSoumission" method="POST" enctype="multipart/form-data" id="ordonnanceForm">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        
        <h3 style="margin-bottom: 20px; color: #667eea;">Informations de l'ordonnance</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="nom_medecin">Nom du Médecin <span style="color: red;">*</span></label>
                <input type="text" id="nom_medecin" name="nom_medecin" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="date_prescription">Date de Prescription <span style="color: red;">*</span></label>
                <input type="date" id="date_prescription" name="date_prescription" class="form-control" 
                       max="<?= date('Y-m-d') ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="fichier_scan">Scanner de l'ordonnance (optionnel)</label>
            <input type="file" id="fichier_scan" name="fichier_scan" class="form-control" 
                   accept=".jpg,.jpeg,.png,.pdf">
            <small style="color: #7f8c8d;">Formats acceptés: JPG, PNG, PDF (max 5MB)</small>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" id="ordonnance_renouvelable" name="ordonnance_renouvelable" 
                       style="margin-right: 8px;" onchange="toggleRenouvellement()">
                Cette ordonnance est renouvelable
            </label>
        </div>
        
        <div class="form-group" id="date_renouvellement_group" style="display: none;">
            <label for="date_renouvellement">Date de renouvellement possible</label>
            <input type="date" id="date_renouvellement" name="date_renouvellement" class="form-control" 
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
        </div>
        
        <hr style="margin: 30px 0;">
        
        <h3 style="margin-bottom: 20px; color: #667eea;">Médicaments prescrits</h3>
        
        <div id="medicaments_container">
            <div class="medicament-row" style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin: 0; color: #2c3e50;">Médicament 1</h4>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeMedicament(this)" style="display: none;">
                        ✗ Supprimer
                    </button>
                </div>
                
                <div class="form-group">
                    <label>Médicament <span style="color: red;">*</span></label>
                    <select name="medicaments[]" class="form-control medicament-select" required onchange="updatePrice(this)">
                        <option value="">-- Sélectionner un médicament --</option>
                        <?php foreach ($medicaments as $med): ?>
                        <option value="<?= $med['id_medicament'] ?>" 
                                data-prix="<?= $med['prix_unitaire'] ?>"
                                data-stock="<?= $med['stock_disponible'] ?>"
                                data-prescription="<?= $med['prescription_obligatoire'] ? '1' : '0' ?>">
                            <?= escape($med['nom_commercial']) ?> - <?= escape($med['forme']) ?> <?= escape($med['dosage']) ?> 
                            (<?= number_format($med['prix_unitaire'], 2) ?> €)
                            <?= $med['prescription_obligatoire'] ? ' ⚕️' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Quantité <span style="color: red;">*</span></label>
                        <input type="number" name="quantites[]" class="form-control quantite-input" 
                               min="1" value="1" required onchange="updatePrice(this)">
                    </div>
                    
                    <div class="form-group">
                        <label>Posologie prescrite</label>
                        <input type="text" name="posologies[]" class="form-control" 
                               placeholder="Ex: 1 comprimé 3 fois par jour">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Durée du traitement</label>
                    <input type="text" name="durees[]" class="form-control" 
                           placeholder="Ex: 7 jours">
                </div>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 15px;">
                    <p style="margin: 0; font-weight: bold; color: #667eea;">
                        Prix: <span class="prix-medicament">0.00</span> €
                    </p>
                </div>
            </div>
        </div>
        
        <button type="button" class="btn btn-secondary" onclick="addMedicament()">
            + Ajouter un médicament
        </button>
        
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-top: 30px;">
            <h3 style="margin-bottom: 10px;">Montant total estimé</h3>
            <p style="font-size: 36px; font-weight: bold; margin: 0;" id="total_amount">0.00 €</p>
        </div>
        
        <div class="alert alert-info" style="margin-top: 20px;">
            <strong>ℹ️ Information:</strong> Votre ordonnance sera vérifiée par un pharmacien avant validation. 
            Vous serez notifié de son statut.
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Soumettre l'ordonnance</button>
            <a href="<?= escape(appUrl('index.php')) ?>?controller=client&action=dashboard" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
let medicamentCount = 1;

function toggleRenouvellement() {
    const checkbox = document.getElementById('ordonnance_renouvelable');
    const dateGroup = document.getElementById('date_renouvellement_group');
    const dateInput = document.getElementById('date_renouvellement');
    
    if (checkbox.checked) {
        dateGroup.style.display = 'block';
        dateInput.required = true;
    } else {
        dateGroup.style.display = 'none';
        dateInput.required = false;
    }
}

function addMedicament() {
    medicamentCount++;
    const container = document.getElementById('medicaments_container');
    const newRow = container.children[0].cloneNode(true);
    
    // Réinitialiser les valeurs
    newRow.querySelector('h4').textContent = 'Médicament ' + medicamentCount;
    newRow.querySelectorAll('input, select').forEach(input => {
        if (input.type !== 'button') {
            input.value = input.type === 'number' ? '1' : '';
        }
    });
    
    // Afficher le bouton supprimer
    newRow.querySelector('.btn-danger').style.display = 'inline-block';
    
    // Réinitialiser le prix
    newRow.querySelector('.prix-medicament').textContent = '0.00';
    
    container.appendChild(newRow);
    updateTotal();
}

function removeMedicament(button) {
    button.closest('.medicament-row').remove();
    updateTotal();
}

function updatePrice(element) {
    const row = element.closest('.medicament-row');
    const select = row.querySelector('.medicament-select');
    const quantite = row.querySelector('.quantite-input').value;
    const prixSpan = row.querySelector('.prix-medicament');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        const prix = parseFloat(option.dataset.prix);
        const total = prix * quantite;
        prixSpan.textContent = total.toFixed(2);
    } else {
        prixSpan.textContent = '0.00';
    }
    
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.prix-medicament').forEach(span => {
        total += parseFloat(span.textContent);
    });
    document.getElementById('total_amount').textContent = total.toFixed(2) + ' €';
}

// Validation du formulaire
document.getElementById('ordonnanceForm').addEventListener('submit', function(e) {
    const medicaments = document.querySelectorAll('.medicament-select');
    let hasValidMedicament = false;
    
    medicaments.forEach(select => {
        if (select.value) hasValidMedicament = true;
    });
    
    if (!hasValidMedicament) {
        e.preventDefault();
        alert('Veuillez sélectionner au moins un médicament');
        return false;
    }
    
    const nomMedecin = document.getElementById('nom_medecin').value.trim();
    const datePrescription = document.getElementById('date_prescription').value;
    
    if (!nomMedecin || !datePrescription) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires');
        return false;
    }
});

// Initialiser le total au chargement
updateTotal();
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
