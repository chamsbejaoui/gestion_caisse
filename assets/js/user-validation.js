// Validation spécifique pour les formulaires utilisateur
document.addEventListener('DOMContentLoaded', function() {
    
    // Validation de l'unicité de l'email via AJAX
    const emailInputs = document.querySelectorAll('input[type="email"]');
    console.log('Email inputs found:', emailInputs.length);

    emailInputs.forEach(function(emailInput) {
        console.log('Setting up validation for email input:', emailInput);
        let debounceTimer;
        let originalEmail = emailInput.value; // Pour l'édition
        
        emailInput.addEventListener('input', function() {
            console.log('Email input changed:', this.value);
            clearTimeout(debounceTimer);
            const email = this.value.trim();
            const form = this.closest('form');
            const userId = form.querySelector('input[name*="[id]"]')?.value || null;

            console.log('Email:', email, 'Original:', originalEmail, 'UserId:', userId);

            // Supprimer les messages d'erreur existants
            removeEmailValidationMessage(this);

            if (email && email !== originalEmail) {
                console.log('Starting email check for:', email);
                debounceTimer = setTimeout(() => {
                    checkEmailUniqueness(email, userId, this);
                }, 500); // Attendre 500ms après la dernière frappe
            }
        });
        
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            const form = this.closest('form');
            const userId = form.querySelector('input[name*="[id]"]')?.value || null;
            
            if (email && email !== originalEmail) {
                checkEmailUniqueness(email, userId, this);
            }
        });
    });
    
    function checkEmailUniqueness(email, userId, inputElement) {
        console.log('checkEmailUniqueness called with:', email, userId);
        // Créer l'URL pour vérifier l'unicité
        const url = '/admin/check-email-uniqueness';
        const params = new URLSearchParams({
            email: email,
            userId: userId || ''
        });

        console.log('Making request to:', `${url}?${params}`);

        // Afficher un indicateur de chargement
        showEmailValidationLoading(inputElement);
        
        fetch(`${url}?${params}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            removeEmailValidationMessage(inputElement);
            
            if (data.exists) {
                showEmailValidationError(inputElement, 'Cet email est déjà utilisé par un autre utilisateur.');
                inputElement.setCustomValidity('Cet email est déjà utilisé par un autre utilisateur.');
            } else {
                showEmailValidationSuccess(inputElement);
                inputElement.setCustomValidity('');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la vérification de l\'email:', error);
            removeEmailValidationMessage(inputElement);
        });
    }
    
    function showEmailValidationLoading(inputElement) {
        removeEmailValidationMessage(inputElement);
        
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'email-validation-message text-info mt-1';
        loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Vérification de l\'email...';
        
        inputElement.parentNode.appendChild(loadingDiv);
    }
    
    function showEmailValidationError(inputElement, message) {
        removeEmailValidationMessage(inputElement);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'email-validation-message text-danger mt-1';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>${message}`;
        
        inputElement.parentNode.appendChild(errorDiv);
        inputElement.classList.add('is-invalid');
    }
    
    function showEmailValidationSuccess(inputElement) {
        removeEmailValidationMessage(inputElement);
        
        const successDiv = document.createElement('div');
        successDiv.className = 'email-validation-message text-success mt-1';
        successDiv.innerHTML = '<i class="fas fa-check me-1"></i>Email disponible';
        
        inputElement.parentNode.appendChild(successDiv);
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
    }
    
    function removeEmailValidationMessage(inputElement) {
        const existingMessage = inputElement.parentNode.querySelector('.email-validation-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        inputElement.classList.remove('is-invalid', 'is-valid');
    }
    
    // Validation des rôles en temps réel
    const roleCheckboxes = document.querySelectorAll('input[type="checkbox"][name*="roles"]');
    if (roleCheckboxes.length > 0) {
        roleCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const form = this.closest('form');
                const checkedRoles = form.querySelectorAll('input[type="checkbox"][name*="roles"]:checked');
                const roleContainer = form.querySelector('.roles-container') || form.querySelector('[data-role-container]');
                
                // Supprimer les messages d'erreur existants
                const existingError = form.querySelector('.roles-validation-error');
                if (existingError) {
                    existingError.remove();
                }
                
                if (checkedRoles.length === 0) {
                    // Afficher un message d'erreur
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'roles-validation-error text-danger mt-2';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Veuillez sélectionner au moins un rôle.';
                    
                    if (roleContainer) {
                        roleContainer.appendChild(errorDiv);
                    } else {
                        checkbox.closest('.form-group, .mb-3')?.appendChild(errorDiv);
                    }
                    
                    // Marquer comme invalide
                    roleCheckboxes.forEach(cb => cb.setCustomValidity('Veuillez sélectionner au moins un rôle.'));
                } else {
                    // Supprimer l'erreur
                    roleCheckboxes.forEach(cb => cb.setCustomValidity(''));
                }
            });
        });
    }
});
