/**
 * Système de validation JavaScript dynamique
 * Remplace la validation HTML5 par une validation JS complète
 */

// Configuration des messages d'erreur
const ERROR_MESSAGES = {
    required: 'Ce champ est obligatoire',
    email: 'Veuillez entrer une adresse email valide',
    minLength: 'Ce champ doit contenir au moins {min} caractères',
    maxLength: 'Ce champ ne peut pas dépasser {max} caractères',
    min: 'La valeur doit être supérieure ou égale à {min}',
    max: 'La valeur doit être inférieure ou égale à {max}',
    pattern: 'Le format est invalide',
    match: 'Les champs ne correspondent pas',
    phone: 'Numéro de téléphone invalide (10 chiffres)',
    date: 'Date invalide',
    number: 'Veuillez entrer un nombre valide',
    decimal: 'Veuillez entrer un nombre décimal valide',
    fileSize: 'Le fichier est trop volumineux (max {size}MB)',
    fileType: 'Type de fichier non autorisé',
    url: 'URL invalide'
};

// Classe de validation
class FormValidator {
    constructor(formId, options = {}) {
        this.form = document.getElementById(formId);
        if (!this.form) {
            console.error(`Formulaire avec l'id "${formId}" introuvable`);
            return;
        }
        
        this.options = {
            realTime: options.realTime !== false, // Validation en temps réel par défaut
            showSuccess: options.showSuccess || false,
            onSubmit: options.onSubmit || null,
            ...options
        };
        
        this.errors = {};
        this.init();
    }
    
    init() {
        // Désactiver la validation HTML5
        this.form.setAttribute('novalidate', 'novalidate');
        
        // Écouter la soumission du formulaire
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Validation en temps réel
        if (this.options.realTime) {
            const inputs = this.form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => {
                    if (this.errors[input.name]) {
                        this.validateField(input);
                    }
                });
            });
        }
    }
    
    handleSubmit(e) {
        e.preventDefault();
        
        // Valider tous les champs
        this.errors = {};
        const inputs = this.form.querySelectorAll('input, select, textarea');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        if (isValid) {
            // Callback personnalisé ou soumission normale
            if (this.options.onSubmit) {
                this.options.onSubmit(this.form);
            } else {
                this.form.submit();
            }
        } else {
            // Scroller vers la première erreur
            const firstError = this.form.querySelector('.error-message');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }
    
    validateField(input) {
        const name = input.name;
        if (!name) return true;
        
        const value = input.value.trim();
        const rules = this.getValidationRules(input);
        
        // Supprimer l'erreur précédente
        this.clearError(input);
        
        // Appliquer les règles
        for (let rule in rules) {
            const ruleValue = rules[rule];
            let isValid = true;
            let errorMsg = '';
            
            switch (rule) {
                case 'required':
                    if (ruleValue && !value) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.required;
                    }
                    break;
                    
                case 'email':
                    if (ruleValue && value && !this.isValidEmail(value)) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.email;
                    }
                    break;
                    
                case 'minLength':
                    if (value && value.length < ruleValue) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.minLength.replace('{min}', ruleValue);
                    }
                    break;
                    
                case 'maxLength':
                    if (value && value.length > ruleValue) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.maxLength.replace('{max}', ruleValue);
                    }
                    break;
                    
                case 'min':
                    if (value && parseFloat(value) < parseFloat(ruleValue)) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.min.replace('{min}', ruleValue);
                    }
                    break;
                    
                case 'max':
                    if (value && parseFloat(value) > parseFloat(ruleValue)) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.max.replace('{max}', ruleValue);
                    }
                    break;
                    
                case 'pattern':
                    if (value && !new RegExp(ruleValue).test(value)) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.pattern;
                    }
                    break;
                    
                case 'phone':
                    if (ruleValue && value && !this.isValidPhone(value)) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.phone;
                    }
                    break;
                    
                case 'number':
                    if (ruleValue && value && isNaN(value)) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.number;
                    }
                    break;
                    
                case 'decimal':
                    if (ruleValue && value && !this.isValidDecimal(value)) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.decimal;
                    }
                    break;
                    
                case 'match':
                    const matchInput = this.form.querySelector(`[name="${ruleValue}"]`);
                    if (matchInput && value !== matchInput.value) {
                        isValid = false;
                        errorMsg = ERROR_MESSAGES.match;
                    }
                    break;
            }
            
            if (!isValid) {
                this.showError(input, errorMsg);
                this.errors[name] = errorMsg;
                return false;
            }
        }
        
        // Validation des fichiers
        if (input.type === 'file' && input.files.length > 0) {
            const fileError = this.validateFile(input);
            if (fileError) {
                this.showError(input, fileError);
                this.errors[name] = fileError;
                return false;
            }
        }
        
        // Succès
        if (this.options.showSuccess && value) {
            this.showSuccess(input);
        }
        
        return true;
    }
    
    getValidationRules(input) {
        const rules = {};
        
        // Récupérer les règles depuis les attributs data-*
        if (input.dataset.required === 'true' || input.hasAttribute('data-required')) {
            rules.required = true;
        }
        if (input.dataset.minlength) rules.minLength = parseInt(input.dataset.minlength);
        if (input.dataset.maxlength) rules.maxLength = parseInt(input.dataset.maxlength);
        if (input.dataset.min) rules.min = input.dataset.min;
        if (input.dataset.max) rules.max = input.dataset.max;
        if (input.dataset.pattern) rules.pattern = input.dataset.pattern;
        if (input.dataset.match) rules.match = input.dataset.match;
        
        // Règles basées sur le type
        if (input.type === 'email') rules.email = true;
        if (input.type === 'tel' || input.dataset.phone) rules.phone = true;
        if (input.type === 'number') rules.number = true;
        if (input.dataset.decimal) rules.decimal = true;
        
        return rules;
    }
    
    validateFile(input) {
        const file = input.files[0];
        const maxSize = input.dataset.maxsize ? parseInt(input.dataset.maxsize) : 5; // MB
        const allowedTypes = input.dataset.types ? input.dataset.types.split(',') : [];
        
        // Vérifier la taille
        if (file.size > maxSize * 1024 * 1024) {
            return ERROR_MESSAGES.fileSize.replace('{size}', maxSize);
        }
        
        // Vérifier le type
        if (allowedTypes.length > 0) {
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(fileExt)) {
                return ERROR_MESSAGES.fileType + ' (' + allowedTypes.join(', ') + ')';
            }
        }
        
        return null;
    }
    
    showError(input, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        
        // Créer ou mettre à jour le message d'erreur
        let errorDiv = input.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
    
    showSuccess(input) {
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        this.clearError(input);
    }
    
    clearError(input) {
        input.classList.remove('is-invalid', 'is-valid');
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }
    
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    isValidPhone(phone) {
        // Format français: 10 chiffres
        return /^0[1-9][0-9]{8}$/.test(phone.replace(/\s/g, ''));
    }
    
    isValidDecimal(value) {
        return /^\d+(\.\d{1,2})?$/.test(value);
    }
}

// Fonction helper pour initialiser rapidement un formulaire
function initFormValidation(formId, options = {}) {
    return new FormValidator(formId, options);
}

// Export pour utilisation
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { FormValidator, initFormValidation };
}
