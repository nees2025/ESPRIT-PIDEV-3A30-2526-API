<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Analyse le sentiment d'un commentaire (MOCK IA)
     * Retourne 'positif', 'neutre' ou 'négatif'
     */
    public function analyzeSentiment(string $text): string
    {
        // Simulation d'une analyse IA basée sur des mots clés
        $positives = ['super', 'génial', 'propre', 'ponctuel', 'merci', 'bien'];
        $negatives = ['retard', 'sale', 'cher', 'impoli', 'mauvais'];

        $score = 0;
        $words = explode(' ', strtolower($text));
        
        foreach ($words as $word) {
            if (in_array($word, $positives)) $score++;
            if (in_array($word, $negatives)) $score--;
        }

        if ($score > 0) return 'positif';
        if ($score < 0) return 'négatif';
        return 'neutre';
    }

    /**
     * Suggère un prix basé sur l'itinéraire et le type (MOCK IA)
     */
    public function suggestSmartPrice(string $depart, string $destination, string $type): float
    {
        // Simule un appel API pour calculer le prix dynamique
        $base = ($type === 'bus') ? 10.0 : 25.0;
        
        // On ajoute un facteur aléatoire "intelligent" simulé
        $distanceFactor = strlen($depart . $destination) * 0.5;
        
        return round($base + $distanceFactor + (rand(1, 10)), 2);
    }
}
