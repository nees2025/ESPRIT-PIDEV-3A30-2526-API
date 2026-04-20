<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AIService
{
    private $client;
    private $apiKey;
    private $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(HttpClientInterface $client, string $groqApiKey)
    {
        $this->client = $client;
        $this->apiKey = $groqApiKey;
    }

    /**
     * Analyse le sentiment d'un commentaire via Groq (Llama 3)
     */
    public function analyzeSentiment(string $text): string
    {
        if (empty($this->apiKey) || str_contains($this->apiKey, 'votre_cle')) {
            return $this->mockSentiment($text); // Fallback si pas de clé
        }

        $prompt = "Analyse le sentiment du commentaire suivant sur un trajet de covoiturage en Tunisie. Réponds UNIQUEMENT par un seul mot parmi : 'positif', 'négatif', ou 'neutre'. Texte : \"$text\"";

        $response = $this->callGroq($prompt);
        return strtolower(trim($response)) ?: 'neutre';
    }

    /**
     * Suggère un prix intelligent via Groq (IA)
     */
    public function suggestSmartPrice(string $depart, string $destination, string $type): float
    {
        if (empty($this->apiKey) || str_contains($this->apiKey, 'votre_cle')) {
            return $this->mockPrice($depart, $destination, $type); // Fallback
        }

        $prompt = "Suggère un prix de covoiturage en Dinars Tunisiens (DT) pour un trajet de $depart à $destination en $type. Réponds UNIQUEMENT par un nombre décimal sans texte. Exemple: 25.5";

        $response = $this->callGroq($prompt);
        return (float)filter_var($response, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?: 20.0;
    }

    private function callGroq(string $prompt): string
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama3-8b-8192',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.5,
                ],
            ]);

            $data = $response->toArray();
            return $data['choices'][0]['message']['content'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    // --- FALLBACKS (Si la clé API n'est pas encore configurée) ---
    private function mockSentiment($text) {
        $pos = ['super', 'bien', 'merci'];
        foreach($pos as $p) if(str_contains(strtolower($text), $p)) return 'positif';
        return 'neutre';
    }

    private function mockPrice($d, $dst, $t) {
        return ($t === 'bus') ? 12.0 : 35.0;
    }
}
