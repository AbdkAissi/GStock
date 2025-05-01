document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table tbody tr').forEach(row => {
        const commandeId = row.dataset.id;
        // Faites un appel AJAX pour vérifier le stock
        fetch(`/admin/commande/${commandeId}/check-stock`)
            .then(response => response.json())
            .then(data => {
                if (data.has_low_stock) {
                    row.classList.add('table-danger');
                }
            });
    });
});