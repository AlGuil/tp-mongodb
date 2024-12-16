<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

$twig = getTwig();
$manager = getMongoDbManager();
$redis = getRedisClient();
$client = getElasticSearchClient(); // Client Elasticsearch

$list = [];
$searchTerm = ''; // Initialiser le terme de recherche

// Vérifier si un terme de recherche a été fourni
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = trim($_GET['search']);

    try {
        // Construire la requête Elasticsearch pour chercher dans 'titre' et 'auteur'
        $params = [
            'index' => 'livres', // Nom de l'index
            'body'  => [
                'query' => [
                    'bool' => [
                        'should' => [
                            // Recherche approximative (tolérer les fautes de frappe)
                            ['match' => ['titre' => ['query' => $searchTerm, 'fuzziness' => 'AUTO']]],
                            ['match' => ['auteur' => ['query' => $searchTerm, 'fuzziness' => 'AUTO']]]
                        ]
                    ]
                ]
            ]
        ];

        // Exécuter la recherche
        $response = $client->search($params);
        
        // Récupérer les IDs des livres trouvés
        $bookIds = array_map(function($hit) {
            return $hit['_id'];
        }, $response['hits']['hits']);

        // Récupérer les livres depuis MongoDB qui correspondent aux IDs trouvés
        if (!empty($bookIds)) {
            $collection = $manager->selectCollection('tp');
            $list = $collection->find(['objectid' => ['$in' => $bookIds]])->toArray();
        }
    } catch (Exception $e) {
        echo "Erreur lors de la recherche dans Elasticsearch : " . $e->getMessage();
    }
} else {
    // Si aucun terme de recherche, récupérer tous les livres depuis MongoDB ou Redis comme avant
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
        echo "Erreur lors de la récupération des données : " . $e->getMessage();
        $list = []; // Liste vide en cas d'erreur
    }
}

// Render le template
try {
    echo $twig->render('index.html.twig', ['list' => $list, 'searchTerm' => $searchTerm]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
