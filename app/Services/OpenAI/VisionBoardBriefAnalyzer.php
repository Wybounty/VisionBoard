<?php

namespace App\Services\OpenAI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class VisionBoardBriefAnalyzer
{
    /**
     * Analyze a vision board brief and return a structured summary.
     *
     * @return array{summary: string, data: array<string, mixed>}
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
                        'content' => 'You analyze a user vision board brief. Return only valid JSON matching the provided schema. Write in French.',
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
                        'description' => 'Structured analysis of a vision board brief.',
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
                                    'required' => [
                                        'theme',
                                        'goals',
                                        'drivers',
                                        'obstacles',
                                        'opportunities',
                                        'next_steps',
                                        'tone',
                                    ],
                                    'properties' => [
                                        'theme' => [
                                            'type' => 'string',
                                        ],
                                        'goals' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                        'drivers' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                        'obstacles' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                        'opportunities' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                        'next_steps' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                        'tone' => [
                                            'type' => 'string',
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
- the dominant life direction
- the concrete goals and desires
- the emotional drivers behind them
- the main blockers or tensions
- the most promising opportunities
- practical next steps

Keep the summary concise and write all content in French.
PROMPT;
    }
}
