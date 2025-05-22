import { getToken } from './auth/auth.js';

document.addEventListener('DOMContentLoaded', () => {
    const id = new URLSearchParams(window.location.search).get('id');
    const container = document.getElementById('trajet-details-container');

    fetch(`http://localhost:8000/api/trajets/${id}`, {
        headers: { Authorization: `Bearer ${getToken()}` }
    })
        .then(res => res.json())
        .then(trajet => {
            container.innerHTML = `
        <h4>${trajet.depart} → ${trajet.destination}</h4>
        <p><strong>Date :</strong> ${trajet.date}</p>
        <p><strong>Heure :</strong> ${trajet.heure}</p>
        <p><strong>Conducteur :</strong> ${trajet.user.pseudo}</p>
        <p><strong>Véhicule :</strong> ${trajet.vehicule || 'Non renseigné'}</p>
        <p><strong>Places :</strong> ${trajet.places}</p>
        <p><strong>Prix :</strong> ${trajet.prix} €</p>
      `;
        });

    document.getElementById('btn-reserver')?.addEventListener('click', () => {
        fetch('http://localhost:8000/api/reservations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${getToken()}`
            },
            body: JSON.stringify({ trajet: `/api/trajets/${id}` })
        })
            .then(res => res.ok ? alert('Réservation confirmée !') : alert('Erreur.'));
    });
});
