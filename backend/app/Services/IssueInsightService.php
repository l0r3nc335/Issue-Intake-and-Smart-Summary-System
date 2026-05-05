<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Throwable;

class IssueInsightService
{
    public function generate(string $title, string $description, string $priority): array
    {
        $apiKey = (string) env('OPENAI_API_KEY');

        if ($apiKey !== '') {
            try {
                return $this->generateFromLlm($apiKey, $title, $description, $priority);
            } catch (Throwable) {
                // Graceful fallback to deterministic rules.
            }
        }

        return $this->generateFromRules($title, $description, $priority);
    }

    private function generateFromLlm(string $apiKey, string $title, string $description, string $priority): array
    {
        $client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout' => 10,
        ]);

        $prompt = "You are a support triage assistant.\n".
            "Return ONLY strict JSON object with keys: summary,next_action.\n".
            "summary max 160 chars. next_action max 140 chars.\n".
            "Priority: {$priority}\nTitle: {$title}\nDescription: {$description}";

        $response = $client->post('chat/completions', [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a support triage assistant. Reply with JSON only: {"summary":"...","next_action":"..."}.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ],
        ]);

        $payload = json_decode((string) $response->getBody(), true);
        $raw = $payload['choices'][0]['message']['content'] ?? '{}';
        $result = json_decode($raw, true);

        if (!is_array($result) || !isset($result['summary'], $result['next_action'])) {
            return $this->generateFromRules($title, $description, $priority);
        }

        return [
            'summary' => Str::limit((string) $result['summary'], 160),
            'next_action' => Str::limit((string) $result['next_action'], 140),
            'source' => 'llm',
        ];
    }

    private function generateFromRules(string $title, string $description, string $priority): array
    {
        $clean = trim(preg_replace('/\s+/', ' ', $description) ?? $description);
        $summary = Str::limit("{$title}: {$clean}", 160);

        $urgentKeywords = ['down', 'payment', 'security', 'breach', 'outage', 'cannot login'];
        $hasUrgentKeyword = Str::of(strtolower($description))->contains($urgentKeywords);

        $nextAction = match (true) {
            $priority === 'critical' || $hasUrgentKeyword =>
                'Escalate to on-call responder and acknowledge requester within 15 minutes.',
            $priority === 'high' =>
                'Assign to domain owner, reproduce issue, and post an initial diagnosis update.',
            default =>
                'Triage in backlog, request missing details if needed, and set a due date.',
        };

        return [
            'summary' => $summary,
            'next_action' => $nextAction,
            'source' => 'rules',
        ];
    }
}
