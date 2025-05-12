// Sélection des éléments
console.log('paiement.js chargé ✅');

document.addEventListener('DOMContentLoaded', function () {
    // Sélectionner les éléments avec une gestion d'erreur
    const typeSelect = document.querySelector('select[name$="[typePaiement]"]');
    if (!typeSelect) {
        console.error('⚠️ Élément #type-paiement introuvable');
    }
    
    const clientRow = document.querySelector('[data-role="client-row"]');
    const fournisseurRow = document.querySelector('[data-role="fournisseur-row"]');
    
    if (!clientRow) {
        console.error('⚠️ Élément [data-role="client-row"] introuvable');
    }
    if (!fournisseurRow) {
        console.error('⚠️ Élément [data-role="fournisseur-row"] introuvable');
    }
    
    // Trouver les conteneurs des champs
    const clientContainer = clientRow ? clientRow.closest('.field-association') : null;
    const fournisseurContainer = fournisseurRow ? fournisseurRow.closest('.field-association') : null;
    
    // Trouver les selects à l'intérieur des conteneurs
    const clientSelect = clientContainer ? clientContainer.querySelector('select') : null;
    const fournisseurSelect = fournisseurContainer ? fournisseurContainer.querySelector('select') : null;
    
    // Trouver les selects des commandes
    const commandeAchatSelect = document.querySelector('select[name*="commandeAchat"]');
    const commandeVenteSelect = document.querySelector('select[name*="commandeVente"]');
    
    if (!commandeAchatSelect) {
        console.log('Information: select commandeAchat non trouvé');
    }
    if (!commandeVenteSelect) {
        console.log('Information: select commandeVente non trouvé');
    }
    // Création de l'élément pour afficher le reste à payer
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

    // Fonction pour remettre à zéro une sélection
    function resetSelect(el) {
        if (el) {
            el.value = '';
            el.dispatchEvent(new Event('change'));
        }
    }
    
    // Masquer les deux champs au départ
    if (clientContainer) clientContainer.style.display = 'none';
    if (fournisseurContainer) fournisseurContainer.style.display = 'none';

    // Fonction pour mettre à jour l'affichage selon le type sélectionné
    function updateDisplay() {
        if (!typeSelect) return;
        
        const value = typeSelect.value;
        
        console.log('Type sélectionné:', value);
        
        // Affichage conditionnel des champs client/fournisseur
        if (value === 'client') {
            if (clientContainer) clientContainer.style.display = 'block';
            if (fournisseurContainer) fournisseurContainer.style.display = 'none';
            resetSelect(fournisseurSelect);
        } else if (value === 'fournisseur') {
            if (clientContainer) clientContainer.style.display = 'none';
            if (fournisseurContainer) fournisseurContainer.style.display = 'block';
            resetSelect(clientSelect);
        } else {
            if (clientContainer) clientContainer.style.display = 'none';
            if (fournisseurContainer) fournisseurContainer.style.display = 'none';
            resetSelect(clientSelect);
            resetSelect(fournisseurSelect);
        }
        
       // Mise à jour de l'affichage des commandes
        updateCommandVisibility(value);

        // 👉 Mettre à jour la liste des commandes associées
        if (value === 'client' && clientSelect && clientSelect.value) {
            updateCommandesSelect('client', clientSelect.value);
        }
        if (value === 'fournisseur' && fournisseurSelect && fournisseurSelect.value) {
            updateCommandesSelect('fournisseur', fournisseurSelect.value);
        }
    }
    
    // Fonction pour mettre à jour la visibilité des champs de commande
    function updateCommandVisibility(value) {
        const achatField = commandeAchatSelect ? commandeAchatSelect.closest('.field-association') : null;
        const venteField = commandeVenteSelect ? commandeVenteSelect.closest('.field-association') : null;
        
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

    // Fonction pour mettre à jour le reste à payer
    async function updateResteAPayer() {
        try {
            resteAPayerDiv.style.display = 'none';

            let commandeId = null;
            let type = null;

            if (commandeVenteSelect && commandeVenteSelect.value) {
                commandeId = commandeVenteSelect.value;
                type = 'vente';
            } else if (commandeAchatSelect && commandeAchatSelect.value) {
                commandeId = commandeAchatSelect.value;
                type = 'achat';
            }

            if (!commandeId) return;

            resteAPayerDiv.textContent = 'Chargement...';
            resteAPayerDiv.style.display = 'block';

            // Construire l'URL de l'API
            const url = `/admin/api/reste-a-payer/${type}/${commandeId}`;
            console.log('Récupération du reste à payer:', url);

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`Erreur réseau: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Données reçues:', data);

            if (data.resteAPayer !== null && data.resteAPayer !== undefined) {
                resteAPayerDiv.textContent = `Reste à payer : ${new Intl.NumberFormat('fr-FR', {
                    style: 'currency',
                    currency: 'MAD'
                }).format(data.resteAPayer)}`;

                const montantInput = document.querySelector('input[name="montant"]');
                if (montantInput) {
                    montantInput.value = data.resteAPayer.toFixed(2);
                }
            } else {
                resteAPayerDiv.textContent = 'Information non disponible';
            }

            resteAPayerDiv.style.display = 'block';
        } catch (error) {
            console.error('Erreur:', error);
            resteAPayerDiv.textContent = 'Erreur lors du chargement: ' + error.message;
            resteAPayerDiv.style.display = 'block';
        }
    }

    // Configurer les écouteurs d'événements
    if (typeSelect) {
        typeSelect.addEventListener('change', updateDisplay);
        
        // Appliquer l'état initial
        if (typeSelect.value) {
            updateDisplay();
        }
    } else {
        console.error('Élément #type-paiement non trouvé');
    }
    
    // Écouteurs pour les commandes
    if (commandeVenteSelect) {
        commandeVenteSelect.addEventListener('change', updateResteAPayer);
    }

    if (commandeAchatSelect) {
        commandeAchatSelect.addEventListener('change', updateResteAPayer);
    }
    
    // Écouteurs pour mettre à jour les commandes disponibles
    if (clientSelect) {
        clientSelect.addEventListener('change', () => {
            if (clientSelect.value) {
                console.log('Client sélectionné:', clientSelect.value);
                updateCommandesSelect('client', clientSelect.value);
            }
        });
    }

    if (fournisseurSelect) {
        fournisseurSelect.addEventListener('change', () => {
            if (fournisseurSelect.value) {
                console.log('Fournisseur sélectionné:', fournisseurSelect.value);
                updateCommandesSelect('fournisseur', fournisseurSelect.value);
            }
        });
    }
    
    // Fonction pour récupérer les commandes
    async function fetchCommandes(type, destinataireId) {
        if (!type || !destinataireId) return [];

        try {
            console.log(`Récupération des commandes pour ${type} #${destinataireId}`);
            const url = `/admin/api/commandes/${type}/${destinataireId}`;
            console.log('URL de l\'API:', url);
            
            const response = await fetch(url);
            if (!response.ok) {
                console.error(`Erreur API: ${response.status} ${response.statusText}`);
                return [];
            }

            const data = await response.json();
            console.log('Commandes récupérées:', data);
            return data;
        } catch (error) {
            console.error('Erreur lors de la récupération des commandes:', error);
            return [];
        }
    }

    // Fonction pour mettre à jour les options de commande
    async function updateCommandesSelect(type, destinataireId) {
        const select = type === 'client' ? commandeVenteSelect : commandeAchatSelect;

        if (!select) {
            console.warn(`Select pour les commandes de ${type} non trouvé`);
            return;
        }

        // Afficher le conteneur du select
        const selectContainer = select.closest('.field-association');
        if (selectContainer) {
            selectContainer.style.display = 'block';
        }

        const commandes = await fetchCommandes(type, destinataireId);

        // Réinitialise le select
        select.innerHTML = '<option value="">-- Sélectionnez une commande --</option>';

        commandes.forEach(cmd => {
            const option = document.createElement('option');
            option.value = cmd.id;
            option.textContent = cmd.label;
            select.appendChild(option);
        });
    }
    // Fonction pour remplir les champs en fonction du type de paiement
function fillFieldsBasedOnPaymentType(paymentType) {
    const montantInput = document.querySelector('input[name="montant"]');
    const detailsInput = document.querySelector('input[name="details"]'); // Exemple pour un champ "détails"

    // Remplir les champs pour espèces ou chèque
    if (paymentType === 'especes' || paymentType === 'cheque') {
        if (montantInput) {
            montantInput.value = "Montant payé en espèces/chèque"; // Exemple de valeur
        }
        if (detailsInput) {
            detailsInput.value = "Détails du paiement"; // Exemple pour un champ "détails"
        }
    } 
    // Remplir les champs pour carte bancaire ou virement bancaire
    else if (paymentType === 'carte_bancaire' || paymentType === 'virement_bancaire') {
        if (montantInput) {
            montantInput.value = "Montant payé par carte/virement"; // Exemple de valeur
        }
        if (detailsInput) {
            detailsInput.value = "Numéro de carte ou référence virement"; // Exemple pour un champ "détails"
        }
    } 
    else {
        // Si le type de paiement est inconnu ou autre
        if (montantInput) {
            montantInput.value = "";
        }
        if (detailsInput) {
            detailsInput.value = "";
        }
    }
}

// Ajouter un événement sur le champ de méthode de paiement pour remplir les champs dynamiquement
if (typeSelect) {
    typeSelect.addEventListener('change', function() {
        const selectedValue = typeSelect.value;
        fillFieldsBasedOnPaymentType(selectedValue);
    });

    // Appliquer l'état initial si un type est déjà sélectionné
    if (typeSelect.value) {
        fillFieldsBasedOnPaymentType(typeSelect.value);
    }
}

    // Initialisation
    updateDisplay();
});