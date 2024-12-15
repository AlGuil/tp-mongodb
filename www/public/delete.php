<?php

include_once '../init.php';

use MongoDB\BSON\ObjectId;

$manager = getMongoDbManager();
$redis = getRedisClient(); // Récupère l'instance Redis

// Vérification de la présence de l'ID dans la query string
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Conversion de l'ID en ObjectId MongoDB
        $objectId = new ObjectId($id);

        // Clé de cache pour l'élément spécifique
        $cacheKey = "tp_item_" . $id;

        // Récupérer la collection et supprimer le document
        $collection = $manager->selectCollection('tp'); // Nom de la collection
        $result = $collection->deleteOne(['_id' => $objectId]);

        if ($result->getDeletedCount() > 0) {
            // Supprimer le cache Redis de l'élément
            if ($redis) {
                $redis->del($cacheKey);
                echo "Cache Redis invalidé.<br>";
            }

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
