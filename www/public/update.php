<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use MongoDB\BSON\ObjectId;

$twig = getTwig();
$manager = getMongoDbManager();
$redis = getRedisClient(); // Récupère l'instance Redis

if (!empty($_POST)) {
    try {
        // Récupérer les données envoyées par le formulaire
        $id = $_POST['id'];
        $title = $_POST['title'];
        $author = $_POST['author'];
        $century = $_POST['century'];
        $edition = $_POST['edition'];
        $language = $_POST['language'];
        $cote = $_POST['cote'];

        // Clé de cache pour l'élément spécifique
        $cacheKey = "tp_item_" . $id;

        // Mise à jour du document dans la base de données
        $collection = $manager->selectCollection('tp');
        $updateData = [
            'titre' => $title,
            'auteur' => $author,
            'siecle' => $century,
            'edition' => $edition,
            'langue' => $language,
            'cote' => $cote,
        ];

        $result = $collection->updateOne(
            ['_id' => new ObjectId($id)], 
            ['$set' => $updateData]
        );

        if ($result->getModifiedCount() > 0) {
            // Mise à jour des données en cache Redis
            if ($redis) {
                // Mettre à jour l'élément dans Redis
                $updatedEntity = array_merge($updateData, ['_id' => $id]);
                $redis->setex($cacheKey, 600, json_encode($updatedEntity)); // Durée du cache : 10 minutes
                echo "Cache Redis mis à jour.<br>";
            }

            // Redirection vers la page index après la mise à jour
            header('Location: index.php');
            exit;
        } else {
            echo "Aucune modification effectuée.";
        }
    } catch (Exception $e) {
        echo "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}
?>
