// public/js/stock.js

document.addEventListener("DOMContentLoaded", function() {
    // Initialiser le scanner avec QuaggaJS
    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: document.querySelector('#scanner-video')  // Cible la vidéo où sera affichée la caméra
        },
        decoder: {
            readers: ["code_128_reader", "ean_reader", "ean_13_reader"]  // Types de codes-barres supportés
        }
    }, function(err) {
        if (err) {
            console.log(err);
            return;
        }
        Quagga.start();  // Démarre le scanner
    });

    // Gérer l'événement de détection du code-barres
    Quagga.onDetected(function(result) {
        const barcode = result.codeResult.code;  // Récupère le code-barres détecté
        document.getElementById('barcode').value = barcode;  // Met à jour le champ de texte

        // Optionnel : effectuer une requête AJAX pour afficher les informations du produit scanné
        fetch('/stock/scan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ barcode: barcode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('product-info').innerHTML = `
                    <h3>Produit trouvé :</h3>
                    Nom: ${data.produit.name} <br>
                    Quantité: ${data.produit.quantite} <br>
                    Prix: ${data.produit.prix} €
                `;
            } else {
                document.getElementById('product-info').innerHTML = `<p>Produit non trouvé.</p>`;
            }
        })
        .catch(error => {
            console.log(error);
            document.getElementById('product-info').innerHTML = `<p>Erreur lors du scan.</p>`;
        });
    });
});
