document.addEventListener('DOMContentLoaded', () => {
    function updateLigne(ligne) {
        const quantite = parseFloat(ligne.querySelector('.champ-quantite input')?.value || 0);
        const prix = parseFloat(ligne.querySelector('.champ-prix input')?.value || 0);
        const total = quantite * prix;

        const totalLigneInput = ligne.querySelector('.total-ligne');
        if (totalLigneInput) {
            totalLigneInput.value = total.toFixed(2) + ' €';
        }
    }

    function calculerTotalGeneral() {
        let total = 0;
        document.querySelectorAll('.ea-form-collection-item').forEach(ligne => {
            updateLigne(ligne);
            const quantite = parseFloat(ligne.querySelector('.champ-quantite input')?.value || 0);
            const prix = parseFloat(ligne.querySelector('.champ-prix input')?.value || 0);
            total += quantite * prix;
        });

        let totalDiv = document.querySelector('#total-general');
        if (!totalDiv) {
            totalDiv = document.createElement('div');
            totalDiv.id = 'total-general';
            totalDiv.style.marginTop = '1rem';
            totalDiv.style.fontWeight = 'bold';
            totalDiv.style.fontSize = '16px';
            document.querySelector('form').appendChild(totalDiv);
        }
        totalDiv.innerHTML = `<strong>Total général :</strong> ${total.toFixed(2)} €`;
    }

    // Event listeners
    const refreshAll = () => {
        document.querySelectorAll('.ea-form-collection-item').forEach(ligne => {
            updateLigne(ligne);
        });
        calculerTotalGeneral();
    };

    document.querySelectorAll('.combo-produit').forEach(select => {
        select.addEventListener('change', (event) => {
            const prix = event.target.selectedOptions[0].dataset.prix;
            const ligne = event.target.closest('.ea-form-collection-item');
            const prixInput = ligne.querySelector('.champ-prix input');
            if (prixInput && prix) {
                prixInput.value = prix;
                updateLigne(ligne);
                calculerTotalGeneral();
            }
        });
    });

    document.querySelectorAll('.champ-quantite input, .champ-prix input').forEach(input => {
        input.addEventListener('input', () => {
            const ligne = input.closest('.ea-form-collection-item');
            updateLigne(ligne);
            calculerTotalGeneral();
        });
    });

    refreshAll();
});
