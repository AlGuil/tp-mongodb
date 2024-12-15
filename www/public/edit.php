<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

$twig = getTwig();
$manager = getMongoDbManager();

// Vérification de l'ID du document à modifier
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Récupération du document depuis la base de données avec l'ID
        $collection = $manager->selectCollection('tp');
        $document = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
        
        if ($document) {
            // Affichage du formulaire avec les données du document
            echo $twig->render('update.html.twig', ['entity' => $document]);
        } else {
            echo "Document non trouvé.";
        }
    } catch (Exception $e) {
        echo "Erreur lors de la récupération du document : " . $e->getMessage();
    }
} else {
    echo "Aucun ID spécifié.";
}
?>
