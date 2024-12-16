<?php

include_once __DIR__.'/../init.php';

echo "\nDébut de l'indexation des livres dans ElasticSearch...\n";

// Obtenir le client ElasticSearch
$client = getElasticSearchClient();

if ($client === null) {
    echo "Erreur : Impossible d'initialiser le client ElasticSearch.\n";
    exit(1);
}

// Chemin du fichier JSON contenant les livres
$jsonFile = __DIR__ . '/../data/catalogue-manuscrits-bibliotheque-patrimoine.json';

// Vérifier si le fichier existe
if (!file_exists($jsonFile)) {
    echo "Erreur : Le fichier JSON n'existe pas à l'emplacement $jsonFile\n";
    exit(1);
}

// Lire le contenu du fichier JSON
$jsonContent = file_get_contents($jsonFile);

// Décoder le JSON
$books = json_decode($jsonContent, true);

if ($books === null) {
    echo "Erreur : Impossible de décoder le fichier JSON.\n";
    exit(1);
}

echo "Nombre de livres à indexer : " . count($books) . "\n";

// Nom de l'index dans ElasticSearch
$indexName = 'livres';

// Vérifier si l'index existe déjà, sinon le créer
if (!$client->indices()->exists(['index' => $indexName])) {
    echo "Création de l'index \"$indexName\"...\n";
    $client->indices()->create([
        'index' => $indexName,
        'body' => [
            'mappings' => [
                'properties' => [
                    'titre' => ['type' => 'text'],
                    'auteur' => ['type' => 'text'],
                    'edition' => ['type' => 'text'],
                    'langue' => ['type' => 'keyword'],
                    'cote' => ['type' => 'text'],
                    'siecle' => ['type' => 'integer'],
                    'objectid' => ['type' => 'integer'],
                ],
            ],
        ],
    ]);
    echo "Index \"$indexName\" créé avec succès.\n";
} else {
    echo "L'index \"$indexName\" existe déjà.\n";
}

// Indexer chaque livre
foreach ($books as $book) {
    try {
        $response = $client->index([
            'index' => $indexName,
            'id'    => $book['objectid'], // Utiliser l'objectid comme identifiant unique
            'body'  => $book,
        ]);
        echo "Livre \"{$book['titre']}\" indexé avec succès (ID: {$book['objectid']}).\n";
    } catch (\Exception $e) {
        echo "Erreur lors de l'indexation du livre \"{$book['titre']}\": " . $e->getMessage() . "\n";
    }
}

echo "\nIndexation terminée avec succès !\n";
exit(0);
