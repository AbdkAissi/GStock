console.log('paiement.js chargé ✅');

document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.querySelector('select[name$="[typePaiement]"]');
    const clientRow = document.querySelector('[data-role="client-row"]');
    const fournisseurRow = document.querySelector('[data-role="fournisseur-row"]');

    const clientContainer = clientRow ? clientRow.closest('.field-association') : null;
    const fournisseurContainer = fournisseurRow ? fournisseurRow.closest('.field-association') : null;

    const clientSelect = clientContainer ? clientContainer.querySelector('select') : null;
    const fournisseurSelect = fournisseurContainer ? fournisseurContainer.querySelector('select') : null;

    const commandeAchatSelect = document.querySelector('select[name*="commandeAchat"]');
    const commandeVenteSelect = document.querySelector('select[name*="commandeVente"]');

    const commandeVenteRow = document.querySelector('[data-role="commande-vente-row"]');
    const commandeAchatRow = document.querySelector('[data-role="commande-achat-row"]');

    // Crée l'affichage du reste à payer
    function createResteAPayerElement() {
        const resteAPayerDiv = document.createElement('div');
        resteAPayerDiv.id = 'reste-a-payer';
        resteAPayerDiv.className = 'alert alert-info mt-3';
        resteAPayerDiv.style.display = 'none';

        const montantField = document.querySelector('.field-money');
        if (montantField && montantField.parentNode) {
            montantField.parentNode.insertBefore(resteAPayerDiv, montantField.nextSibling);
        }

        return resteAPayerDiv;
    }

    let resteAPayerDiv = document.querySelector('#reste-a-payer') || createResteAPayerElement();

    async function updateCommandesSelect(type, destinataireId) {
        const select = (type === 'client') ? commandeVenteSelect : commandeAchatSelect;
        if (!select) return;

        const selectContainer = select.closest('.field-association');
        if (selectContainer) selectContainer.style.display = 'block';

        try {
            const url = `/admin/api/commandes/${type}/${destinataireId}`;
            const response = await fetch(url);

            if (!response.ok) {
                console.error(`Erreur API: ${response.status} ${response.statusText}`);
                select.innerHTML = '<option value="">-- Aucune commande disponible --</option>';
                return;
            }

            const commandes = await response.json();
            select.innerHTML = '<option value="">-- Sélectionnez une commande --</option>';

            commandes.forEach(cmd => {
                const option = document.createElement('option');
                option.value = cmd.id;
                option.textContent = cmd.label;
                select.appendChild(option);
            });

            select.value = '';
        } catch (error) {
            console.error('Erreur lors du fetch des commandes:', error);
            select.innerHTML = '<option value="">-- Erreur chargement commandes --</option>';
        }
    }

    function updateCommandVisibility(value) {
        const achatField = commandeAchatSelect?.closest('.field-association');
        const venteField = commandeVenteSelect?.closest('.field-association');

        if (value === 'client') {
            if (achatField) achatField.style.display = 'none';
            if (venteField) venteField.style.display = 'block';
        } else if (value === 'fournisseur') {
            if (achatField) achatField.style.display = 'block';
            if (venteField) venteField.style.display = 'none';
        } else {
            if (achatField) achatField.style.display = 'none';
            if (venteField) venteField.style.display = 'none';
        }
    }

    function updateDisplay() {
        if (!typeSelect) return;

        const value = typeSelect.value;
        console.log('Type sélectionné:', value);

        if (value === 'client') {
            if (clientContainer) clientContainer.style.display = 'block';
            if (fournisseurContainer) fournisseurContainer.style.display = 'none';
            if (fournisseurSelect) fournisseurSelect.value = '';
        } else if (value === 'fournisseur') {
            if (clientContainer) clientContainer.style.display = 'none';
            if (fournisseurContainer) fournisseurContainer.style.display = 'block';
            if (clientSelect) clientSelect.value = '';
        } else {
            if (clientContainer) clientContainer.style.display = 'none';
            if (fournisseurContainer) fournisseurContainer.style.display = 'none';
            if (clientSelect) clientSelect.value = '';
            if (fournisseurSelect) fournisseurSelect.value = '';
        }

        updateCommandVisibility(value);

        if (value === 'client' && clientSelect?.value) {
            updateCommandesSelect('client', clientSelect.value);
        }
        if (value === 'fournisseur' && fournisseurSelect?.value) {
            updateCommandesSelect('fournisseur', fournisseurSelect.value);
        }
    }

    async function updateResteAPayer() {
        try {
            resteAPayerDiv.style.display = 'none';

            let commandeId = null;
            let type = null;

            if (commandeVenteSelect?.value) {
                commandeId = commandeVenteSelect.value;
                type = 'vente';
            } else if (commandeAchatSelect?.value) {
                commandeId = commandeAchatSelect.value;
                type = 'achat';
            }

            if (!commandeId) return;

            resteAPayerDiv.textContent = 'Chargement...';
            resteAPayerDiv.style.display = 'block';

            const url = `/admin/api/reste-a-payer/${type}/${commandeId}`;
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`Erreur réseau: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();

            if (data.resteAPayer != null) {
                resteAPayerDiv.textContent = `Reste à payer : ${new Intl.NumberFormat('fr-FR', {
                    style: 'currency',
                    currency: 'MAD'
                }).format(data.resteAPayer)}`;

                const montantInput = document.querySelector('input[name="montant"]');
                if (montantInput) montantInput.value = data.resteAPayer.toFixed(2);
            } else {
                resteAPayerDiv.textContent = 'Information non disponible';
            }

        } catch (error) {
            console.error('Erreur:', error);
            resteAPayerDiv.textContent = 'Erreur lors du chargement: ' + error.message;
            resteAPayerDiv.style.display = 'block';
        }
    }

    // Événements

    typeSelect?.addEventListener('change', updateDisplay);
    clientSelect?.addEventListener('change', () => {
        if (clientSelect.value) {
            updateCommandesSelect('client', clientSelect.value);
        }
    });
    fournisseurSelect?.addEventListener('change', () => {
        if (fournisseurSelect.value) {
            updateCommandesSelect('fournisseur', fournisseurSelect.value);
        }
    });

    commandeVenteSelect?.addEventListener('change', updateResteAPayer);
    commandeAchatSelect?.addEventListener('change', updateResteAPayer);

    // Initialisation
    updateDisplay();
});
