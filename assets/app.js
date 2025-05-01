import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */


import './styles/app.css';
// assets/admin.js
import './js/auto-prix-achat.js';
import './js/auto-prix-vente.js';
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
import Swal from 'sweetalert2';

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


