import http from 'k6/http';
import { check } from 'k6';

export let options = {
    stages: [
        { duration: "5s", target: 5 },
        { duration: "10s", target: 10 },
        { duration: "10s", target: 50 },
        { duration: "5s", target: 10 },
        { duration: "5s", target: 5 }
    ]
};

export default function () {
    // 1. Affichage de la liste des livres
    let response = http.get("http://tpmongo-php:80/");
    check(response, { "status is 200": (r) => r.status === 200 });

    // 2. Affichage de la page 30
    response = http.get("http://tpmongo-php:80?page=30/");
    check(response, { "status is 200": (r) => r.status === 200 });

    // 3. Consultation des détails d'un livre (en supposant que vous avez un livre avec ID=1)
    let bookId = 1; // Remplacer par un ID valide de livre
    response = http.get(`http://tpmongo-php:80/get.php?id=${bookId}`);
    check(response, { "status is 200": (r) => r.status === 200 });

    // 4. Retour à la liste des livres (simuler un clic de retour à la liste)
    response = http.get("http://tpmongo-php:80/");
    check(response, { "status is 200": (r) => r.status === 200 });

    // 5. Suppression d'un livre (en supposant que vous avez un livre avec ID=1 à supprimer)
    response = http.get(`http://tpmongo-php:80/delete.php?id=${bookId}`);
    check(response, { "status is 200": (r) => r.status === 200 });

    // 6. Ajout d'un livre via POST
    let payload = {
        title: "Nouveau Livre",
        author: "Auteur Exemple",
        century: "XXI",
        edition: "Exemple",
        language: "Français",
        cote: "ABC123"
    };
    
    response = http.post("http://tpmongo-php:80/create.php", payload, {
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    });
    check(response, { "status is 200": (r) => r.status === 200 });

    // 7. Consultation du livre ajouté
    response = http.get("http://tpmongo-php:80/get.php?id=1"); // ID à mettre à jour selon l'ajout réel
    check(response, { "status is 200": (r) => r.status === 200 });
};
