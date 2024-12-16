<?php

include_once '../init.php';

use MongoDB\BSON\ObjectId;

$manager = getMongoDbManager();

// Vérification de la présence de l'ID dans la query string
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Conversion de l'ID en ObjectId MongoDB
        $objectId = new ObjectId($id);

        // Récupérer la collection et supprimer le document
        $collection = $manager->selectCollection('tp'); // Nom de la collection
        $result = $collection->deleteOne(['_id' => $objectId]);

        if ($result->getDeletedCount() > 0) {
            // Redirection vers la liste si la suppression a réussi
            header('Location: /index.php');
            exit();
        } else {
            echo "Aucun document trouvé à supprimer.";
        }

    } catch (Exception $e) {
        echo "Erreur lors de la suppression du document : " . $e->getMessage();
    }
} else {
    echo "Aucun ID spécifié pour la suppression.";
}
?>
