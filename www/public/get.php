<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use MongoDB\BSON\ObjectId;

$twig = getTwig();
$manager = getMongoDbManager();

// Récupération de l'ID de l'élément depuis la query string
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Conversion de l'ID en ObjectId MongoDB
        $objectId = new ObjectId($id);
        
        // Recherche du document dans la collection "tp" par son ID
        $collection = $manager->selectCollection('tp'); // Nom de la collection
        $entity = $collection->findOne(['_id' => $objectId]);

        // Vérifier si l'élément existe
        if (!$entity) {
            echo "L'élément demandé n'a pas été trouvé.";
            exit();
        }

    } catch (Exception $e) {
        echo "Erreur lors de la récupération des données : " . $e->getMessage();
        exit();
    }

    // Rendu du template avec les données de l'élément
    try {
        echo $twig->render('get.html.twig', ['entity' => $entity]);
    } catch (LoaderError|RuntimeError|SyntaxError $e) {
        echo $e->getMessage();
    }
} else {
    echo "Aucun ID spécifié.";
    exit();
}
?>
