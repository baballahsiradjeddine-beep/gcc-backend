<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    /**
     * Generate interactive questions from a given text or PDF content using Gemini API.
     */
    public static function generateQuestions(?string $textContext, ?string $pdfFilePath, int $count, string $types, ?string $fullPromptOverride = null): array
    {
        $dbKey = \App\Models\GeminiSetting::where('key', 'api_key')->first()?->value;
        $apiKey = $dbKey ?: env('GEMINI_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception('GEMINI_API_KEY is not configured. Please add it in settings or .env file.');
        }

        $prompt = $fullPromptOverride ?: self::getSystemPromptTemplate($count, $types);
        
        $parts = [];
        $parts[] = [
            'text' => $prompt
        ];

        if (!empty($textContext)) {
            $parts[] = [
                'text' => "--- CONTENT TO ANALYZE ---\n" . $textContext
            ];
        }

        if (!empty($pdfFilePath) && file_exists($pdfFilePath)) {
            $mimeType = mime_content_type($pdfFilePath);
            $base64Data = base64_encode(file_get_contents($pdfFilePath));
            
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType ?: 'application/pdf',
                    'data' => $base64Data
                ]
            ];
        }

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => $parts
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.4
            ]
        ];

        // Get the model from DB or fallback to flash
        $dbModel = \App\Models\GeminiSetting::where('key', 'model_name')->first()?->value;
        $model = $dbModel ?: 'gemini-1.5-flash';

        // We use the selected model from settings
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])
        ->timeout(60) // it might take a while to read big PDFs
        ->post($url, $payload);

        if (!$response->successful()) {
            Log::error('Gemini API Error', ['response' => $response->body()]);
            throw new \Exception('فشل الاتصال بـ Gemini API: ' . $response->status() . ' - ' . $response->body());
        }

        $result = $response->json();
        
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Extract the JSON array from the response. 
        // Gemini sometimes includes conversational text before or after the JSON block.
        if (preg_match('/\[\s*\{.*\}\s*\]/s', $text, $matches)) {
            $text = $matches[0];
        } else {
            // Fallback: remove markdown wrappers if present
            $text = preg_replace('/```json\s*/', '', $text);
            $text = preg_replace('/```\s*/', '', $text);
            $text = trim($text);
        }
        
        $decoded = json_decode($text, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gemini Invalid JSON', [
                'error_msg' => json_last_error_msg(),
                'raw_text_length' => strlen($text),
                'raw_text_start' => substr($text, 0, 50),
                'raw_text_end' => substr($text, -50),
                'full_text' => $text
            ]);
            throw new \Exception('لم يقم Gemini بإرجاع كود JSON صالح: ' . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Simple chat interaction with Gemini
     */
    public static function chat(string $prompt, string $model = 'gemini-1.5-flash'): string
    {
        $dbKey = \App\Models\GeminiSetting::where('key', 'api_key')->first()?->value;
        $apiKey = $dbKey ?: env('GEMINI_API_KEY');

        if (!$apiKey) {
            throw new \Exception('Gemini API Key is not configured.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('Gemini API Error: ' . ($response->json()['error']['message'] ?? $response->body()));
        }

        $result = $response->json();
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response from Gemini.';
    }

    /**
     * Fetch confirmed free models for the user
     */
    public static function listModels(): array
    {
        // We use a curated list of models confirmed to work on the free tier
        // to avoid user confusion and "limit: 0" errors from experimental models.
        return [
            'gemini-1.5-flash' => 'Gemini 1.5 Flash ✅ [باقة مجانية - سريع ومستقر]',
            'gemini-1.5-pro'   => 'Gemini 1.5 Pro ✨ [باقة مجانية - ذكي وتحليلي]',
        ];
    }

    public static function getSystemPromptTemplate(int $count = 5, string $types = 'mix'): string
    {
        $dbSetting = \App\Models\GeminiSetting::where('key', 'system_prompt')->first();
        $template = ($dbSetting && !empty($dbSetting->value)) ? $dbSetting->value : self::getDefaultTemplate();

        // Standardize placeholders support
        $text = str_replace(['{count}', '{$count}'], $count, $template);
        $text = str_replace(['{types}', '{$types}'], $types, $text);
        
        return $text;
    }

    public static function getDefaultTemplate(): string
    {
        return <<<PROMPT
You are an expert educational content creator. I will provide you with a study document (PDF or text).
Your task is to generate EXACTLY {count} interactive, high-quality questions based heavily on the content provided.

The required question types should cover: {types}. (Mix them if "mix" is specified, otherwise stick to the requested type).

CRITICAL RULE: YOU MUST OUTPUT ONLY VALID MINIFIED JSON ARRAY AS THE RESULT. NO MARKDOWN, NO EXPLANATORY TEXT.

The JSON array must contain objects that EXACTLY match this structure:
[
  {
    "question": "The main question text (in Arabic)",
    "question_type": "multiple_choices" | "true_or_false" | "fill_in_the_blanks" | "pick_the_intruder" | "match_with_arrows",
    "scope": "exercice",
    "direction": "inherit",
    "question_is_latex": false,
    "explanation_text": "A detailed explanation of the correct answer (in Arabic)",
    "explanation_text_is_latex": false,
    "hint": [],
    "options": {
      // NOTE: format the options object exactly based on 'question_type' as explained below
    }
  }
]

=== Option Formats Rules based on 'question_type' ===
1. If "multiple_choices":
"options": {"choices": [ {"option": "Choice 1", "is_correct": true, "option_is_latex": false}, {"option": "Choice 2", "is_correct": false, "option_is_latex": false} ]}

2. If "true_or_false":
"options": {"correct": true}  (or false)

3. If "fill_in_the_blanks":
"options": {
  "paragraph": "Water boils at [1] degrees Celsius.",
  "blanks": [ {"id": 1, "answer": "100"} ],
  "suggestions": ["50", "100", "0"]
}

4. If "pick_the_intruder":
"options": {"words": [ {"word": "Apple", "is_intruder": false}, {"word": "Car", "is_intruder": true} ]}

5. If "match_with_arrows":
"options": {"pairs": [ {"first": "Paris", "second": "France"}, {"first": "Berlin", "second": "Germany"} ]}

Generate the questions exclusively in ARABIC language.
Make sure the JSON is 100% syntactically valid.
PROMPT;
    }
}
