document.addEventListener('DOMContentLoaded', function () {
    console.log("✅ JS EasyAdmin chargé !");
    alert("✅ JS EasyAdmin chargé !");
    console.log('✅ easyadmin-ligne-commande.js chargé !');

    // Sélectionner tous les éléments avec la classe .select-produit
    const produitSelects = document.querySelectorAll('.select-produit');

    // Vérification si des éléments .select-produit existent
    if (produitSelects.length === 0) {
        console.warn('⛔ Aucun élément avec la classe .select-produit trouvé.');
    }

    produitSelects.forEach((select) => {
        select.addEventListener('change', function () {
            console.log('🌀 Produit sélectionné');

            const selectedOption = this.options[this.selectedIndex];
            const prixVente = selectedOption.getAttribute('data-prixvente');
            console.log('💰 Prix récupéré :', prixVente);

            // Si aucun prix n'est trouvé, ne rien faire et sortir
            if (!prixVente) {
                console.warn('⛔ Aucun prix trouvé pour ce produit.');
                return;
            }

            // Trouver le nom du champ produit sélectionné
            const name = this.getAttribute('name'); // Exemple : CommandeVente[lignesCommandeVente][0][produit]

            // Replacer "produit" par "prixUnitaire" dans le nom
            const prixUnitaireName = name.replace('[produit]', '[prixUnitaire]');

            // Rechercher l'input correspondant dans le document
            const prixInput = document.querySelector(`[name="${prixUnitaireName}"]`);

            if (prixInput) {
                console.log('🎯 Champ trouvé, mise à jour...');
                prixInput.value = prixVente;
            } else {
                console.warn('⛔ Champ prixUnitaire introuvable pour', prixUnitaireName);
            }
        });
    });
});
