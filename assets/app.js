import './bootstrap.js'; // Si tu as un fichier bootstrap.js personnalisé
import 'bootstrap'; // Charger Bootstrap en JS (le CSS peut être inclus via SCSS)
import './styles/app.scss'; // Ton SCSS personnalisé
import './paiement.js';
import Swal from 'sweetalert2';



import $ from 'jquery';

import '@fortawesome/fontawesome-free/js/fontawesome';
import '@fortawesome/fontawesome-free/js/solid';
import '@fortawesome/fontawesome-free/js/regular';
import '@fortawesome/fontawesome-free/js/brands';





// Attente du chargement complet du DOM
document.addEventListener('DOMContentLoaded', function() {
    const flashes = document.querySelectorAll('.flash-message');

    flashes.forEach(flash => {
        const message = flash.dataset.message;
        const type = flash.dataset.type;

        if (message && type) {
            // Log pour déboguer si les données sont bien récupérées
            console.log(`Message: ${message}, Type: ${type}`);

            // Affichage de l'alerte SweetAlert2
            Swal.fire({
                icon: type === 'success' ? 'success' : (type === 'error' ? 'error' : 'info'),
                title: message,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK',
                timer: 5000,  // Message disparait après 5 secondes
                showClass: {
                    popup: 'animate__animated animate__fadeInDown',  // Animation d'apparition
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp',  // Animation de disparition
                }
            });
        }
    });
});
