document.addEventListener('DOMContentLoaded', function () {
    const clientField = document.querySelector('[name$="[client]"]');
    const fournisseurField = document.querySelector('[name$="[fournisseur]"]');

    if (clientField && fournisseurField) {
        const toggleFields = () => {
            if (clientField.value) {
                fournisseurField.disabled = true;
            } else {
                fournisseurField.disabled = false;
            }

            if (fournisseurField.value) {
                clientField.disabled = true;
            } else {
                clientField.disabled = false;
            }
        };

        clientField.addEventListener('change', toggleFields);
        fournisseurField.addEventListener('change', toggleFields);

        // Initial check on page load
        toggleFields();
    }
});
