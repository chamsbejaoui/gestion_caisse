document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les formulaires qui ont besoin de validation
    var forms = document.querySelectorAll('.needs-validation');

    // Boucle sur les formulaires et empêche la soumission si non valide
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Ajouter la classe was-validated pour afficher les messages d'erreur
            form.classList.add('was-validated');

            // Vérifier et afficher les erreurs pour chaque champ
            var inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(function(input) {
                // Gérer l'affichage des messages d'erreur
                var feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    if (!input.validity.valid) {
                        feedback.style.display = 'block';
                    } else {
                        feedback.style.display = 'none';
                    }
                }

                // Ajouter des écouteurs d'événements pour la validation en temps réel
                input.addEventListener('input', function() {
                    if (input.validity.valid) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                    }
                });
            });
        }, false);
    });
});