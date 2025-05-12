// Écouteur principal : attend que le DOM soit entièrement chargé
document.addEventListener('DOMContentLoaded', function () {
    console.log("Script auto-prix-achat chargé");

    /**
     * Fonction qui attache un écouteur à chaque champ <select> de produit
     * Quand un produit est sélectionné, le prix d'achat correspondant (data-prix-achat)
     * est automatiquement renseigné dans le champ 'prixAchat' associé.
     */
    function attachProduitListeners() {
        const produitSelects = document.querySelectorAll(
            'select[id^="CommandeAchat_lignesCommandeAchat_"][id$="_produit"]'
        );

        produitSelects.forEach(function (produitSelect) {
            produitSelect.addEventListener('change', function () {
                const selectedOption = produitSelect.options[produitSelect.selectedIndex];
                const prix = selectedOption.getAttribute('data-prix-achat'); // récupère le prix depuis l'attribut HTML

                const idPrixAchat = produitSelect.id.replace('_produit', '_prixAchat'); // reconstitue l'ID du champ prix
                const prixAchatInput = document.getElementById(idPrixAchat); // sélectionne le champ de prix

                if (prix && prixAchatInput) {
                    prixAchatInput.value = prix; // met à jour le champ de prix avec la valeur du produit
                    console.log("Prix achat mis à jour:", prix);
                    calculerTotalGeneral(); // recalcule le total général de la commande
                }
            });
        });
    }

    /**
     * Fonction qui calcule le total général de la commande.
     * Elle additionne (quantité × prixAchat) pour chaque ligne.
     */
    function calculerTotalGeneral() {
        let total = 0.0;
        const lignes = document.querySelectorAll('[id^="CommandeAchat_lignesCommandeAchat_"]');

        lignes.forEach(function (ligne) {
            const quantiteInput = ligne.querySelector('input[id$="_quantite"]');
            const prixInput = ligne.querySelector('input[id$="_prixAchat"]');

            if (quantiteInput && prixInput) {
                const quantite = parseFloat(quantiteInput.value.replace(',', '.')) || 0;
                const prix = parseFloat(prixInput.value.replace(',', '.')) || 0;
                total += quantite * prix;
            }
        });

        // Affiche le total dans l'élément ayant l'id 'total-general'
        const totalDisplay = document.getElementById('total-general');
        if (totalDisplay) {
            totalDisplay.textContent = total.toFixed(2);
        }
    }

    /**
     * Fonction qui écoute les changements dans les champs de quantité et de prixAchat
     * pour mettre à jour le total en temps réel.
     */
    function attachInputListeners() {
        const container = document.querySelector('[data-ea-collection-field]');
        if (!container) return;

        container.addEventListener('input', function (e) {
            if (e.target.matches('input[id$="_quantite"], input[id$="_prixAchat"]')) {
                calculerTotalGeneral();
            }
        });
    }

    /**
     * Fonction qui observe les ajouts/suppressions de lignes de commande dans EasyAdmin
     * et applique automatiquement les écouteurs nécessaires.
     */
    function observeLignesChanges() {
        const collectionContainer = document.querySelector('[data-ea-collection-field]');
        if (!collectionContainer) return;

        const observer = new MutationObserver(function () {
            attachProduitListeners(); // réattache les écouteurs sur les nouveaux <select>
            calculerTotalGeneral();   // recalcule le total si nécessaire
        });

        observer.observe(collectionContainer, {
            childList: true,
            subtree: true
        });
    }

    // Initialisation : attache tous les écouteurs nécessaires au chargement
    attachProduitListeners();
    attachInputListeners();
    observeLignesChanges();
    calculerTotalGeneral();
});
