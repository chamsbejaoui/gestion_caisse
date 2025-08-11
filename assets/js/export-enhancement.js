// Améliorations pour les pages d'exportation
document.addEventListener('DOMContentLoaded', function() {
    
    // Vérifier si nous sommes sur une page d'export
    const exportContainer = document.querySelector('.export-container');
    if (!exportContainer) return;

    // Initialisation des améliorations
    initializeAnimations();
    initializeTooltips();
    initializeProgressIndicators();
    initializeKeyboardShortcuts();
    initializeAccessibility();

    // Animations d'entrée
    function initializeAnimations() {
        // Animation en cascade des éléments
        const animatedElements = document.querySelectorAll('.card, .btn, .form-control, .form-select');
        animatedElements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.6s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animation des icônes
        const icons = document.querySelectorAll('.card-icon');
        icons.forEach(icon => {
            icon.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.1) rotate(5deg)';
            });
            
            icon.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1) rotate(0deg)';
            });
        });
    }

    // Tooltips informatifs
    function initializeTooltips() {
        const tooltipData = {
            'CSV': 'Format idéal pour Excel et les tableurs. Léger et universel.',
            'Excel (XLSX)': 'Format Microsoft Excel avec formatage avancé et formules.',
            'PDF': 'Format de document portable, idéal pour l\'impression et l\'archivage.'
        };

        const formatOptions = document.querySelectorAll('select[name="export_options[format]"] option');
        formatOptions.forEach(option => {
            if (tooltipData[option.textContent]) {
                option.title = tooltipData[option.textContent];
            }
        });

        // Tooltip pour les champs de date
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.addEventListener('focus', function() {
                showTooltip(this, 'Utilisez le format JJ/MM/AAAA ou cliquez sur l\'icône calendrier');
            });
            
            input.addEventListener('blur', function() {
                hideTooltip(this);
            });
        });
    }

    // Indicateurs de progression
    function initializeProgressIndicators() {
        const form = document.querySelector('form');
        if (!form) return;

        // Créer un indicateur de progression
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-container position-fixed';
        progressContainer.style.cssText = 'top: 0; left: 0; right: 0; z-index: 9999; height: 4px; background: rgba(0,0,0,0.1);';
        
        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        progressBar.style.cssText = 'height: 100%; width: 0%; background: linear-gradient(90deg, #667eea, #764ba2); transition: width 0.3s ease;';
        
        progressContainer.appendChild(progressBar);
        document.body.appendChild(progressContainer);

        // Calculer le progrès basé sur les champs remplis
        function updateProgress() {
            const requiredFields = form.querySelectorAll('input[required], select[required]');
            const filledFields = Array.from(requiredFields).filter(field => {
                if (field.type === 'radio') {
                    return form.querySelector(`input[name="${field.name}"]:checked`);
                }
                return field.value.trim() !== '';
            });

            const progress = (filledFields.length / requiredFields.length) * 100;
            progressBar.style.width = progress + '%';

            // Changer la couleur selon le progrès
            if (progress < 50) {
                progressBar.style.background = 'linear-gradient(90deg, #ff6b6b, #ee5a52)';
            } else if (progress < 100) {
                progressBar.style.background = 'linear-gradient(90deg, #feca57, #ff9ff3)';
            } else {
                progressBar.style.background = 'linear-gradient(90deg, #48dbfb, #0abde3)';
            }
        }

        // Écouter les changements dans le formulaire
        form.addEventListener('input', updateProgress);
        form.addEventListener('change', updateProgress);
        
        // Initialiser le progrès
        updateProgress();
    }

    // Raccourcis clavier
    function initializeKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + Enter pour soumettre
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.click();
                }
            }

            // Échap pour retourner
            if (e.key === 'Escape') {
                const backBtn = document.querySelector('a[href*="index"]');
                if (backBtn) {
                    backBtn.click();
                }
            }

            // Flèches pour naviguer entre les options radio
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                const radioButtons = document.querySelectorAll('input[type="radio"]');
                const focusedRadio = document.activeElement;
                
                if (radioButtons.length > 0 && Array.from(radioButtons).includes(focusedRadio)) {
                    e.preventDefault();
                    const currentIndex = Array.from(radioButtons).indexOf(focusedRadio);
                    let nextIndex;
                    
                    if (e.key === 'ArrowUp') {
                        nextIndex = currentIndex > 0 ? currentIndex - 1 : radioButtons.length - 1;
                    } else {
                        nextIndex = currentIndex < radioButtons.length - 1 ? currentIndex + 1 : 0;
                    }
                    
                    radioButtons[nextIndex].focus();
                    radioButtons[nextIndex].checked = true;
                    radioButtons[nextIndex].dispatchEvent(new Event('change'));
                }
            }
        });
    }

    // Améliorations d'accessibilité
    function initializeAccessibility() {
        // Ajouter des labels ARIA
        const cards = document.querySelectorAll('.period-card, .format-card');
        cards.forEach((card, index) => {
            card.setAttribute('role', 'region');
            card.setAttribute('aria-label', `Section ${index + 1} de configuration d'export`);
        });

        // Améliorer les messages d'erreur
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('invalid', function(e) {
                e.preventDefault();
                const invalidField = e.target;
                
                // Créer un message d'erreur accessible
                let errorMessage = invalidField.validationMessage;
                if (invalidField.type === 'date' && !invalidField.value) {
                    errorMessage = 'Veuillez sélectionner une date valide';
                }
                
                showAccessibleError(invalidField, errorMessage);
            }, true);
        }

        // Annoncer les changements d'état
        const exportAllRadios = document.querySelectorAll('input[name="export_options[exportAll]"]');
        exportAllRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const announcement = this.value === '1' ? 
                    'Toutes les données seront exportées' : 
                    'Période personnalisée sélectionnée. Veuillez choisir les dates.';
                
                announceToScreenReader(announcement);
            });
        });
    }

    // Fonctions utilitaires
    function showTooltip(element, message) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip-popup';
        tooltip.textContent = message;
        tooltip.style.cssText = `
            position: absolute;
            background: rgba(0,0,0,0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            z-index: 10000;
            max-width: 200px;
            word-wrap: break-word;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        `;

        document.body.appendChild(tooltip);

        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = (rect.bottom + 5) + 'px';

        // Ajuster la position si le tooltip dépasse
        const tooltipRect = tooltip.getBoundingClientRect();
        if (tooltipRect.right > window.innerWidth) {
            tooltip.style.left = (window.innerWidth - tooltipRect.width - 10) + 'px';
        }
        if (tooltipRect.bottom > window.innerHeight) {
            tooltip.style.top = (rect.top - tooltipRect.height - 5) + 'px';
        }

        element._tooltip = tooltip;
    }

    function hideTooltip(element) {
        if (element._tooltip) {
            element._tooltip.remove();
            delete element._tooltip;
        }
    }

    function showAccessibleError(field, message) {
        // Supprimer l'ancien message d'erreur
        const oldError = field.parentNode.querySelector('.error-message');
        if (oldError) {
            oldError.remove();
        }

        // Créer le nouveau message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-danger mt-1';
        errorDiv.textContent = message;
        errorDiv.setAttribute('role', 'alert');
        errorDiv.setAttribute('aria-live', 'polite');

        field.parentNode.appendChild(errorDiv);
        field.setAttribute('aria-describedby', 'error-' + Date.now());
        field.classList.add('is-invalid');

        // Focus sur le champ en erreur
        field.focus();
    }

    function announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.style.cssText = 'position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;';
        announcement.textContent = message;

        document.body.appendChild(announcement);

        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }

    // Amélioration de la validation en temps réel
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            validateDateRange();
        });
    });

    function validateDateRange() {
        const dateMin = document.querySelector('input[name="export_options[dateMin]"]');
        const dateMax = document.querySelector('input[name="export_options[dateMax]"]');
        
        if (dateMin && dateMax && dateMin.value && dateMax.value) {
            if (new Date(dateMin.value) > new Date(dateMax.value)) {
                showAccessibleError(dateMax, 'La date de fin doit être postérieure à la date de début');
                return false;
            } else {
                // Supprimer les erreurs existantes
                const errors = document.querySelectorAll('.error-message');
                errors.forEach(error => error.remove());
                
                dateMin.classList.remove('is-invalid');
                dateMax.classList.remove('is-invalid');
                return true;
            }
        }
        return true;
    }
});
