<?php

namespace App\Services\OpenAI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class VisionBoardBriefAnalyzer
{
    /**
     * Analyze a vision board brief and return a structured summary of themes.
     *
     * @return array{summary: string, data: array{themes: array<int, array{title: string, description: string, motivational_phrase: string}>}}
     */
    public function analyze(string $brief): array
    {
        $apiKey = (string) config('services.openai.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $response = Http::acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->timeout(60)
            ->post($this->endpoint(), [
                'model' => config('services.openai.brief_model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu analyses un brief de Vision Board. Extrait uniquement les grands themes importants evoques par l utilisateur, pour servir ensuite a rechercher des images et construire automatiquement un Vision Board. Cree un titre court pour chaque theme, ecris une description concise de ce que la personne souhaite reellement, ajoute pour chaque theme une phrase motivante breve et positive, fusionne les informations similaires, n invente aucune information, reponds uniquement en francais, et retourne exclusivement un JSON valide respectant le schema fourni. Quand le brief contient des noms precis, des lieux, des villes, des pays ou des repères identifiables comme London ou Guadeloupe, conserve-les dans les titres et/ou descriptions quand c est pertinent.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->buildPrompt($brief),
                    ],
                ],
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'vision_board_brief_analysis',
                        'description' => 'Structured extraction of themes from a vision board brief.',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'additionalProperties' => false,
                            'required' => ['summary', 'data'],
                            'properties' => [
                                'summary' => [
                                    'type' => 'string',
                                    'description' => 'Short summary of the brief.',
                                ],
                                'data' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'required' => ['themes'],
                                    'properties' => [
                                        'themes' => [
                                            'type' => 'array',
                                            'minItems' => 1,
                                            'items' => [
                                                'type' => 'object',
                                                'additionalProperties' => false,
                                                'required' => ['title', 'description', 'motivational_phrase'],
                                                'properties' => [
                                                    'title' => [
                                                        'type' => 'string',
                                                    ],
                                                    'description' => [
                                                        'type' => 'string',
                                                    ],
                                                    'motivational_phrase' => [
                                                        'type' => 'string',
                                                        'description' => 'Short motivational phrase for the theme.',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI brief analysis request failed.');
        }

        $payload = $response->json();
        $content = data_get($payload, 'choices.0.message.content') ?? data_get($payload, 'output_text');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('OpenAI brief analysis returned an empty response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! isset($decoded['summary'], $decoded['data']) || ! is_array($decoded['data'])) {
            throw new RuntimeException('OpenAI brief analysis returned invalid JSON.');
        }

        return [
            'summary' => (string) $decoded['summary'],
            'data' => $decoded['data'],
        ];
    }

    protected function endpoint(): string
    {
        return rtrim((string) config('services.openai.base_url'), '/').'/chat/completions';
    }

    protected function buildPrompt(string $brief): string
    {
        return <<<PROMPT
Analyze this vision board brief and return the structure requested by the schema.

Brief:
{$brief}

Focus on:
- the most important themes only
- short theme titles
- concise descriptions of what the person truly wants
- a short motivational phrase for each theme
- merging similar ideas into one theme
- not inventing information
- preserving precise names, places, cities, countries, and notable entities when they appear in the brief and are relevant to the theme
- outputting valid French only

Keep the summary concise and write all content in French.
PROMPT;
    }
}
