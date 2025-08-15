// Validation côté client pour améliorer l'expérience utilisateur
document.addEventListener('DOMContentLoaded', function() {
    
    // Validation des formulaires avec la classe 'needs-validation'
    const forms = document.querySelectorAll('.needs-validation');

    forms.forEach(function(form) {
        // Marquer les champs qui ont été touchés/modifiés
        const touchedFields = new Set();

        // Écouter les interactions utilisateur pour marquer les champs comme "touchés"
        form.addEventListener('input', function(event) {
            touchedFields.add(event.target);
            event.target.classList.add('field-touched');
            validateField(event.target);
        });

        form.addEventListener('blur', function(event) {
            if (event.target.matches('input, select, textarea')) {
                touchedFields.add(event.target);
                event.target.classList.add('field-touched');
                validateField(event.target);
            }
        }, true);

        form.addEventListener('submit', function(event) {
            // Marquer tous les champs comme touchés lors de la soumission
            const allFields = form.querySelectorAll('input, select, textarea');
            allFields.forEach(field => {
                touchedFields.add(field);
                field.classList.add('field-touched');
                validateField(field);
            });

            // Vérifier si le formulaire est valide
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // Afficher les messages d'erreur
                showValidationErrors(form);
            }

            form.classList.add('was-validated');
        });
    });

    // Fonction pour valider un champ spécifique
    function validateField(field) {
        // Réinitialiser les messages d'erreur
        field.setCustomValidity('');

        // Validation spécifique selon le type de champ
        if (field.name === 'email') {
            validateEmail(field);
        } else if (field.name === 'plainPassword') {
            validatePassword(field);
        } else if (field.name === 'roles[]') {
            validateRoles(field);
        }

        // Mettre à jour l'affichage des erreurs
        updateFieldValidationUI(field);
    }

    // Validation de l'email
    function validateEmail(field) {
        if (field.required && !field.value) {
            field.setCustomValidity('L\'adresse email est obligatoire');
        } else if (field.value && !isValidEmail(field.value)) {
            field.setCustomValidity('Veuillez entrer une adresse email valide');
        }
    }

    // Validation du mot de passe
    function validatePassword(field) {
        const value = field.value;
        if (value) { // Seulement valider si une valeur est présente (pour permettre de laisser vide en édition)
            if (value.length < 6) {
                field.setCustomValidity('Le mot de passe doit contenir au moins 6 caractères');
            } else if (!/[a-z]/.test(value)) {
                field.setCustomValidity('Le mot de passe doit contenir au moins une lettre minuscule');
            } else if (!/[A-Z]/.test(value)) {
                field.setCustomValidity('Le mot de passe doit contenir au moins une lettre majuscule');
            } else if (!/[0-9]/.test(value)) {
                field.setCustomValidity('Le mot de passe doit contenir au moins un chiffre');
            }
        }
    }

    // Validation des rôles
    function validateRoles(field) {
        const roleCheckboxes = field.closest('form').querySelectorAll('input[name="roles[]"]');
        const hasSelectedRole = Array.from(roleCheckboxes).some(checkbox => checkbox.checked);
        
        roleCheckboxes.forEach(checkbox => {
            if (!hasSelectedRole) {
                checkbox.setCustomValidity('Veuillez sélectionner au moins un rôle');
            } else {
                checkbox.setCustomValidity('');
            }
        });
    }

    // Mise à jour de l'interface utilisateur pour la validation
    function updateFieldValidationUI(field) {
        const feedback = field.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = field.validationMessage;
            if (!field.validity.valid) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                feedback.style.display = 'block';
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                feedback.style.display = 'none';
            }
        }
    }

    // Afficher tous les messages d'erreur du formulaire
    function showValidationErrors(form) {
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            updateFieldValidationUI(field);
        });
    }

    // Vérifier si une adresse email est valide
    function isValidEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailRegex.test(email);
    }
});
