import { getToken } from './auth/auth.js';

document.addEventListener('DOMContentLoaded', () => {
    fetch('http://localhost:8000/api/employe/infos', {
        headers: { Authorization: `Bearer ${getToken()}` }
    })
        .then(res => res.json())
        .then(data => {
            document.getElementById('infos-employe').innerHTML = `
        <p><strong>Nom :</strong> ${data.nom}</p>
        <p><strong>Poste :</strong> ${data.poste}</p>
      `;
        });
});