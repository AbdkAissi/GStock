document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.produit-select').forEach(select => {
        select.addEventListener('change', async function () {
            const produitId = this.value;
            const prixField = this.closest('.ea-form-panel')
                .querySelector('input[name$="[prixUnitaire]"]');

            if (!produitId || !prixField) return;

            const response = await fetch(`/produit/${produitId}/prix`);
            const data = await response.json();
            prixField.value = data.prix;
        });
    });
});
