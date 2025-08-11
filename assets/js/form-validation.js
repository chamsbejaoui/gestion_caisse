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
        });

        form.addEventListener('blur', function(event) {
            if (event.target.matches('input, select, textarea')) {
                touchedFields.add(event.target);
                event.target.classList.add('field-touched');
            }
        }, true);

        form.addEventListener('submit', function(event) {
            // Marquer tous les champs comme touchés lors de la soumission
            const allFields = form.querySelectorAll('input, select, textarea');
            allFields.forEach(field => {
                touchedFields.add(field);
                field.classList.add('field-touched');
            });

            // Vérifier si le formulaire est vide (tous les champs requis sont vides)
            const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isEmpty = true;

            requiredFields.forEach(function(field) {
                // Pour les champs de type select, vérifier si une option valide est sélectionnée
                if (field.type === 'select-one') {
                    if (field.selectedIndex > 0 || (field.selectedIndex === 0 && field.options[0].value !== '')) {
                        isEmpty = false;
                    }
                }
                // Pour les autres champs, vérifier si la valeur n'est pas vide
                else if (field.value.trim() !== '') {
                    isEmpty = false;
                }
            });

            // Si le formulaire est complètement vide, empêcher la soumission
            if (isEmpty && requiredFields.length > 0) {
                event.preventDefault();
                event.stopPropagation();

                // Afficher un message d'erreur
                showFormEmptyError(form);
                return false;
            }

            // Validation HTML5 standard - seulement pour les champs touchés
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // Afficher un message d'erreur de validation seulement pour les champs touchés
                showValidationErrorForTouchedFields(form, touchedFields);
            }

            form.classList.add('was-validated');

            // Empêcher la double soumission
            preventDoubleSubmission(form);
        });
    });

    // Validation spécifique pour les montants - seulement après interaction
    const montantInputs = document.querySelectorAll('input[type="number"][name*="montant"]');
    montantInputs.forEach(function(input) {
        let hasBeenTouched = false;

        input.addEventListener('focus', function() {
            hasBeenTouched = true;
        });

        input.addEventListener('input', function() {
            if (hasBeenTouched) {
                const value = parseFloat(this.value);
                if (this.value !== '' && value < 0) {
                    this.setCustomValidity('Le montant doit être positif');
                } else {
                    this.setCustomValidity('');
                }
            }
        });

        input.addEventListener('blur', function() {
            if (hasBeenTouched) {
                const value = parseFloat(this.value);
                if (this.required && this.value === '') {
                    this.setCustomValidity('Veuillez saisir un montant');
                } else if (this.value !== '' && value < 0) {
                    this.setCustomValidity('Le montant doit être positif');
                } else {
                    this.setCustomValidity('');
                }
            }
        });
    });

    // Validation des plages de montants (min/max)
    function validateMontantRange() {
        const montantMin = document.querySelector('input[name*="montantMin"]');
        const montantMax = document.querySelector('input[name*="montantMax"]');
        
        if (montantMin && montantMax) {
            const min = parseFloat(montantMin.value);
            const max = parseFloat(montantMax.value);
            
            if (!isNaN(min) && !isNaN(max) && min > max) {
                montantMax.setCustomValidity('Le montant maximum doit être supérieur ou égal au montant minimum');
                return false;
            } else {
                montantMax.setCustomValidity('');
                return true;
            }
        }
        return true;
    }

    // Appliquer la validation des plages sur les changements
    const montantMinInputs = document.querySelectorAll('input[name*="montantMin"]');
    const montantMaxInputs = document.querySelectorAll('input[name*="montantMax"]');
    
    montantMinInputs.forEach(input => input.addEventListener('input', validateMontantRange));
    montantMaxInputs.forEach(input => input.addEventListener('input', validateMontantRange));

    // Validation des plages de dates
    function validateDateRange() {
        const dateMin = document.querySelector('input[name*="dateMin"]');
        const dateMax = document.querySelector('input[name*="dateMax"]');
        
        if (dateMin && dateMax && dateMin.value && dateMax.value) {
            const min = new Date(dateMin.value);
            const max = new Date(dateMax.value);
            
            if (min > max) {
                dateMax.setCustomValidity('La date maximum doit être postérieure ou égale à la date minimum');
                return false;
            } else {
                dateMax.setCustomValidity('');
                return true;
            }
        }
        return true;
    }

    // Appliquer la validation des plages de dates
    const dateMinInputs = document.querySelectorAll('input[name*="dateMin"]');
    const dateMaxInputs = document.querySelectorAll('input[name*="dateMax"]');
    
    dateMinInputs.forEach(input => input.addEventListener('change', validateDateRange));
    dateMaxInputs.forEach(input => input.addEventListener('change', validateDateRange));

    // Validation des champs de date
    const dateInputs = document.querySelectorAll('input[type="datetime-local"], input[type="date"]');
    dateInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.required && !this.value) {
                this.setCustomValidity('Veuillez sélectionner une date.');
            } else {
                this.setCustomValidity('');
            }
        });

        input.addEventListener('blur', function() {
            if (this.required && !this.value) {
                this.setCustomValidity('Veuillez sélectionner une date.');
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Validation des mots de passe (exclut les formulaires de login)
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(function(input) {
        // Ignorer les champs de mot de passe dans les formulaires de login
        const form = input.closest('form');
        const isLoginForm = form && (
            form.action.includes('/login') ||
            input.name === 'password' && input.id === 'inputPassword'
        );

        if (!isLoginForm) {
            input.addEventListener('input', function() {
                const value = this.value;
                if (value.length > 0 && value.length < 6) {
                    this.setCustomValidity('Le mot de passe doit contenir au moins 6 caractères');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    });

    // Validation de confirmation de mot de passe
    const passwordConfirmInputs = document.querySelectorAll('input[name*="second"]');
    passwordConfirmInputs.forEach(function(confirmInput) {
        const passwordInput = confirmInput.form.querySelector('input[name*="first"]');
        if (passwordInput) {
            function validatePasswordMatch() {
                if (confirmInput.value !== passwordInput.value) {
                    confirmInput.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    confirmInput.setCustomValidity('');
                }
            }
            
            passwordInput.addEventListener('input', validatePasswordMatch);
            confirmInput.addEventListener('input', validatePasswordMatch);
        }
    });

    // Validation spécifique pour les formulaires utilisateur
    const userForms = document.querySelectorAll('form[name*="user"]');
    userForms.forEach(function(form) {
        const emailInput = form.querySelector('input[type="email"]');
        const passwordInput = form.querySelector('input[type="password"]');
        const rolesCheckboxes = form.querySelectorAll('input[type="checkbox"][name*="roles"]');

        // Validation de l'email en temps réel
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                if (this.value) {
                    // Vérification basique du format email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.value)) {
                        this.setCustomValidity('Veuillez saisir une adresse email valide.');
                    } else {
                        this.setCustomValidity('');
                    }
                }
            });
        }

        // Validation des rôles
        if (rolesCheckboxes.length > 0) {
            rolesCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const checkedRoles = form.querySelectorAll('input[type="checkbox"][name*="roles"]:checked');
                    if (checkedRoles.length === 0) {
                        // Marquer le premier checkbox comme invalide
                        rolesCheckboxes[0].setCustomValidity('Veuillez sélectionner au moins un rôle.');
                    } else {
                        // Supprimer l'erreur de tous les checkboxes
                        rolesCheckboxes.forEach(cb => cb.setCustomValidity(''));
                    }
                });
            });
        }

        // Validation du mot de passe pour les nouveaux utilisateurs
        if (passwordInput && form.action.includes('/new')) {
            passwordInput.addEventListener('blur', function() {
                if (!this.value) {
                    this.setCustomValidity('Le mot de passe est obligatoire lors de la création d\'un utilisateur.');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    });

    // Fonction pour afficher un message d'erreur de formulaire vide
    function showFormEmptyError(form) {
        removeExistingAlerts(form);

        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Formulaire vide !</strong> Veuillez remplir au moins un champ requis avant de soumettre.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        form.insertBefore(alertDiv, form.firstChild);

        // Faire défiler vers le message d'erreur
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Fonction pour afficher un message d'erreur de validation
    function showValidationError(form) {
        removeExistingAlerts(form);

        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning alert-dismissible fade show mt-3';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Erreurs de validation !</strong> Veuillez corriger les erreurs dans le formulaire.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        form.insertBefore(alertDiv, form.firstChild);

        // Faire défiler vers le premier champ en erreur
        const firstInvalidField = form.querySelector(':invalid');
        if (firstInvalidField) {
            firstInvalidField.focus();
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Fonction pour afficher les erreurs seulement pour les champs touchés
    function showValidationErrorForTouchedFields(form, touchedFields) {
        removeExistingAlerts(form);

        // Compter les erreurs dans les champs touchés
        let touchedErrorCount = 0;
        let firstTouchedInvalidField = null;

        touchedFields.forEach(field => {
            if (!field.checkValidity()) {
                touchedErrorCount++;
                if (!firstTouchedInvalidField) {
                    firstTouchedInvalidField = field;
                }
            }
        });

        // Afficher l'alerte seulement s'il y a des erreurs dans les champs touchés
        if (touchedErrorCount > 0) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning alert-dismissible fade show mt-3';
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Erreurs de validation !</strong> Veuillez corriger les erreurs dans les champs modifiés.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            form.insertBefore(alertDiv, form.firstChild);

            // Faire défiler vers le premier champ touché en erreur
            if (firstTouchedInvalidField) {
                firstTouchedInvalidField.focus();
                firstTouchedInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    // Fonction pour supprimer les alertes existantes
    function removeExistingAlerts(form) {
        const existingAlerts = form.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
    }

    // Supprimer les alertes quand l'utilisateur commence à taper
    document.addEventListener('input', function(event) {
        if (event.target.matches('input, select, textarea')) {
            const form = event.target.closest('form');
            if (form) {
                const alerts = form.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (alert.textContent.includes('Formulaire vide') ||
                        alert.textContent.includes('Erreurs de validation')) {
                        alert.remove();
                    }
                });
            }
        }
    });

    // Fonction pour empêcher la double soumission
    function preventDoubleSubmission(form) {
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');

        submitButtons.forEach(button => {
            // Désactiver le bouton
            button.disabled = true;

            // Changer le texte du bouton
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Traitement en cours...';

            // Réactiver après 5 secondes en cas d'erreur
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            }, 5000);
        });
    }

    // Réactiver les boutons si la page est rechargée (navigation arrière)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            const allSubmitButtons = document.querySelectorAll('button[type="submit"], input[type="submit"]');
            allSubmitButtons.forEach(button => {
                button.disabled = false;
                // Restaurer le texte original si possible
                if (button.innerHTML.includes('Traitement en cours')) {
                    button.innerHTML = button.innerHTML.replace(
                        '<i class="fas fa-spinner fa-spin me-2"></i>Traitement en cours...',
                        '<i class="fas fa-save me-2"></i>Enregistrer'
                    );
                }
            });
        }
    });
});
