document.addEventListener('DOMContentLoaded', function () {
    console.log("Script auto-prix-vente chargé");

    // Fonction pour attacher les écouteurs d'événements sur les sélecteurs de produits
    function attachProduitListeners() {
        const produitSelects = document.querySelectorAll('select[id^="CommandeVente_lignesCommandeVente_"][id$="_produit"]');

        produitSelects.forEach(function (produitSelect) {
            produitSelect.addEventListener('change', function () {
                const selectedOption = produitSelect.options[produitSelect.selectedIndex];
                const prix = selectedOption.getAttribute('data-prix');
                const idPrixVente = produitSelect.id.replace('_produit', '_prixVente');
                const prixVenteInput = document.getElementById(idPrixVente);

                // Mise à jour du prix vente dans le champ
                if (prix && prixVenteInput) {
                    prixVenteInput.value = (parseFloat(prix) / 100).toFixed(2);
                    console.log("Prix vente mis à jour:", prix);
                    calculerTotalGeneral();
                }

                // Mise à jour du prix unitaire dans le champ correspondant
                const idPrixUnitaire = produitSelect.id.replace('_produit', '_prixUnitaire');
                const prixUnitaireInput = document.getElementById(idPrixUnitaire);

                if (prix && prixUnitaireInput) {
                    prixUnitaireInput.value = (parseFloat(prix) / 100).toFixed(2);
                    console.log("Prix unitaire mis à jour:", prix);
                }
            });
        });
    }

    // Fonction pour calculer le total général de la commande
    function calculerTotalGeneral() {
        let total = 0.0;
        const lignes = document.querySelectorAll('[id^="CommandeVente_lignesCommandeVente_"]');

        lignes.forEach(function (ligne) {
            const quantiteInput = ligne.querySelector('input[id$="_quantite"]');
            const prixInput = ligne.querySelector('input[id$="_prixVente"]');

            if (quantiteInput && prixInput) {
                const quantite = parseFloat(quantiteInput.value.replace(',', '.')) || 0;
                const prix = parseFloat(prixInput.value.replace(',', '.')) || 0;
                total += quantite * prix;
            }
        });

        const totalDisplay = document.getElementById('total-general');
        if (totalDisplay) {
            totalDisplay.textContent = total.toFixed(2);
        }
    }

    // Attacher des écouteurs d'événements pour les modifications des quantités et prix de vente
    function attachInputListeners() {
        const container = document.querySelector('[data-ea-collection-field]');
        if (!container) return;

        container.addEventListener('input', function (e) {
            if (e.target.matches('input[id$="_quantite"], input[id$="_prixVente"], input[id$="_prixUnitaire"]')) {
                calculerTotalGeneral();
            }
        });
    }

    // Observer les changements dans les lignes de commande (ajout de ligne ou modification)
    function observeLignesChanges() {
        const collectionContainer = document.querySelector('[data-ea-collection-field]');
        if (!collectionContainer) return;

        const observer = new MutationObserver(function () {
            attachProduitListeners();
            calculerTotalGeneral();
        });

        observer.observe(collectionContainer, {
            childList: true,
            subtree: true
        });
    }

    // Initialisation des écouteurs et calculs
    attachProduitListeners();
    attachInputListeners();
    observeLignesChanges();
    calculerTotalGeneral();
});
