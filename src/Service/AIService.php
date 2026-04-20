<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIService
{
    private $client;
    private $apiKey;
    // Utilisation de Gemini 1.5 Flash (rapide et gratuit)
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=';

    public function __construct(HttpClientInterface $client, string $geminiApiKey)
    {
        $this->client = $client;
        $this->apiKey = $geminiApiKey;
    }

    /**
     * Analyse le sentiment via Google Gemini
     */
    public function analyzeSentiment(string $text): string
    {
        if (empty($this->apiKey) || str_contains($this->apiKey, 'votre_cle')) {
            return $this->mockSentiment($text);
        }

        $prompt = "Analyse le sentiment de ce commentaire sur un covoiturage en Tunisie. Réponds UNIQUEMENT par un mot : 'positif', 'négatif', ou 'neutre'. Texte : \"$text\"";

        $response = $this->callGemini($prompt);
        return strtolower(trim($response)) ?: 'neutre';
    }

    /**
     * Suggère un prix via Google Gemini
     */
    public function suggestSmartPrice(string $depart, string $destination, string $type): float
    {
        if (empty($this->apiKey) || str_contains($this->apiKey, 'votre_cle')) {
            return $this->mockPrice($depart, $destination, $type);
        }

        $prompt = "Suggère un prix de covoiturage en Dinars Tunisiens (DT) pour un trajet de $depart à $destination en $type. Réponds UNIQUEMENT par un nombre décimal sans texte. Exemple: 25.5";

        $response = $this->callGemini($prompt);
        return (float)filter_var($response, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?: 20.0;
    }

    private function callGemini(string $prompt): string
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl . $this->apiKey, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ],
            ]);

            $data = $response->toArray();
            // Structure de réponse de Gemini : candidates[0].content.parts[0].text
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    private function mockSentiment($text) {
        if(str_contains(strtolower($text), 'bien')) return 'positif';
        return 'neutre';
    }

    private function mockPrice($d, $dst, $t) {
        return ($t === 'bus') ? 12.0 : 35.0;
    }
}
