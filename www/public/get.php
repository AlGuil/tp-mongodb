<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use MongoDB\BSON\ObjectId;

$twig = getTwig();
$manager = getMongoDbManager();
$redis = getRedisClient();

// Récupération de l'ID de l'élément
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Vérification si Redis est activé et si les données sont en cache
        if ($redis) {
            $cacheKey = 'element_' . $id;
            $cachedData = $redis->get($cacheKey);

            if ($cachedData) {
                $entity = json_decode($cachedData, true);
            } else {
                // Si non trouvé dans le cache, récupérer depuis MongoDB
                $objectId = new ObjectId($id);
                $collection = $manager->selectCollection('tp');
                $entity = $collection->findOne(['_id' => $objectId]);

                if ($entity) {
                    // Stocker dans le cache pour 10 minutes
                    $redis->setex($cacheKey, 600, json_encode($entity));
                }
            }
        } else {
            // Si Redis n'est pas activé, récupérer les données depuis MongoDB
            $objectId = new ObjectId($id);
            $collection = $manager->selectCollection('tp');
            $entity = $collection->findOne(['_id' => $objectId]);
        }

        // Vérification si l'élément existe
        if (!$entity) {
            echo "L'élément demandé n'a pas été trouvé.";
            exit();
        }

    } catch (Exception $e) {
        echo "Erreur lors de la récupération des données : " . $e->getMessage();
        exit();
    }

    // Render du template
    try {
        echo $twig->render('get.html.twig', ['entity' => $entity]);
    } catch (LoaderError|RuntimeError|SyntaxError $e) {
        echo $e->getMessage();
    }
} else {
    echo "Aucun ID spécifié.";
    exit();
}
