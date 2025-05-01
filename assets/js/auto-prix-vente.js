document.addEventListener('DOMContentLoaded', function () {
    const produitSelects = document.querySelectorAll('select[id^="CommandeAchat_lignesCommandeAchat_"][id$="_produit"]');

    produitSelects.forEach(function (produitSelect) {
        produitSelect.addEventListener('change', function () {
            const selectedOption = produitSelect.options[produitSelect.selectedIndex];
            const prix = selectedOption.getAttribute('data-prix-achat');

            const idPrixUnitaire = produitSelect.id.replace('_produit', '_prixUnitaire');
            const prixUnitaireInput = document.getElementById(idPrixUnitaire);

            if (prix && prixUnitaireInput) {
                prixUnitaireInput.value = prix;
                console.log("Prix unitaire mis à jour:", prix);
            } else {
                console.log("Champ prixUnitaire introuvable ou prix non défini");
            }
        });
    });
});
