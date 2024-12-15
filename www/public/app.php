<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

$twig = getTwig();
$manager = getMongoDbManager();
$redis = getRedisClient();

$list = [];

try {
    // Si Redis est activé, essayer de récupérer les données depuis le cache
    if ($redis) {
        $cacheKey = 'elements_list';
        $cachedData = $redis->get($cacheKey);

        if ($cachedData) {
            // Si les données sont dans le cache, les décoder et les utiliser
            $list = json_decode($cachedData, true);
        } else {
            // Si les données ne sont pas dans le cache, récupérer depuis MongoDB
            $collection = $manager->selectCollection('tp');
            $list = $collection->find()->toArray(); // Convertir en tableau

            // Mettre en cache les données pour 10 minutes
            $redis->setex($cacheKey, 600, json_encode($list));
        }
    } else {
        // Si Redis n'est pas activé, récupérer les données depuis MongoDB directement
        $collection = $manager->selectCollection('tp');
        $list = $collection->find()->toArray();
    }
} catch (Exception $e) {
    // En cas d'erreur avec MongoDB
    echo "Erreur lors de la récupération des données : " . $e->getMessage();
    $list = []; // Liste vide en cas d'erreur
}

// Render le template
try {
    echo $twig->render('index.html.twig', ['list' => $list]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
