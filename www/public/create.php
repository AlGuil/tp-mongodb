<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

$twig = getTwig();
$manager = getMongoDbManager();

// Vérification si le formulaire a été soumis
if (!empty($_POST)) {
    // Récupération des données du formulaire
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? null;  // Autoriser un auteur nul
    $century = $_POST['century'] ?? null;
    $edition = $_POST['edition'] ?? null; // Optionnel
    $language = $_POST['language'] ?? null; // Optionnel
    $cote = $_POST['cote'] ?? null; // Optionnel

    // Validation simple (vérifier que le titre est bien renseigné)
    if (empty($title)) {
        echo "Le titre est requis.";
    } else {
        try {
            // Insertion dans la base de données MongoDB
            $collection = $manager->selectCollection('tp'); // Nom de la collection
            $document = [
                'titre' => $title,
                'auteur' => $author,
                'siecle' => $century,
                'edition' => $edition,
                'langue' => $language,
                'cote' => $cote,
                'objectid' => rand(1, 100), // Vous pouvez ajuster cette logique pour générer des IDs uniques
            ];

            // Insertion du manuscrit
            $result = $collection->insertOne($document);

            // Redirection ou message de succès
            header('Location: /index.php'); // Redirection vers la liste après ajout
            exit();
        } catch (Exception $e) {
            echo "Erreur lors de l'ajout du manuscrit : " . $e->getMessage();
        }
    }
} else {
    // Affichage du formulaire si la méthode POST n'est pas utilisée
    try {
        echo $twig->render('create.html.twig');
    } catch (LoaderError|RuntimeError|SyntaxError $e) {
        echo $e->getMessage();
    }
}
?>
