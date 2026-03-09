<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    /**
     * Main method to call Gemini API
     */
    protected static function callGemini(string $prompt, ?string $textContext = null, ?string $pdfFilePath = null, bool $expectJson = false, ?string $modelOverride = null): array|string
    {
        // 1. Get API Key
        $dbKey = \App\Models\GeminiSetting::where('key', 'api_key')->first()?->value;
        $apiKey = trim($dbKey ?: env('GEMINI_API_KEY'));
        
        if (empty($apiKey)) {
            throw new \Exception('مفتاح الـ API غير موجود. يرجى إضافته في الإعدادات.');
        }

        // 2. Build Parts
        $parts = [['text' => $prompt]];
        if (!empty($textContext)) {
            $parts[] = ['text' => "--- CONTEXT ---\n" . $textContext];
        }

        if (!empty($pdfFilePath)) {
            if (file_exists($pdfFilePath)) {
                $fileSize = filesize($pdfFilePath);
                Log::info("Gemini: Attaching PDF", ['path' => $pdfFilePath, 'size' => $fileSize]);
                
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => 'application/pdf',
                        'data' => base64_encode(file_get_contents($pdfFilePath))
                    ]
                ];
            } else {
                Log::error("Gemini: PDF File NOT FOUND", ['path' => $pdfFilePath]);
                throw new \Exception("لم يتم العثور على ملف الـ PDF في المسار: " . $pdfFilePath);
            }
        }

        // 3. Determine Model
        $dbModel = \App\Models\GeminiSetting::where('key', 'model_name')->first()?->value;
        $modelInput = trim($modelOverride ?: ($dbModel ?: 'gemini-2.0-flash'));
        $cleanModel = ltrim(str_replace('models/', '', $modelInput), '/');
        
        // Use v1beta for everything as it's the most compatible with both 1.5 and 2.x models
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$cleanModel}:generateContent?key={$apiKey}";

        // 4. Build Payload
        $payload = [
            'contents' => [['role' => 'user', 'parts' => $parts]],
            'generationConfig' => [
                'temperature' => 0.4,
            ]
        ];

        // Only add response_mime_type if we're actually expecting JSON
        if ($expectJson) {
            $payload['generationConfig']['response_mime_type'] = 'application/json';
        }

        // 5. Execute Request
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(120)
            ->post($url, $payload);

        if (!$response->successful()) {
            $status = $response->status();
            $body = $response->body();
            Log::error("Gemini API Error", ['status' => $status, 'body' => $body]);

            if ($status === 404) {
                throw new \Exception("الموديل ({$cleanModel}) غير متوفر. تأكد من صحة الاسم في الإعدادات.");
            }
            if ($status === 400 && str_contains($body, 'API_KEY_INVALID')) {
                throw new \Exception("مفتاح الـ API غير صالح.");
            }
            
            throw new \Exception("خطأ في الاتصال ({$status}): " . ($response->json()['error']['message'] ?? 'فشل الاتصال بجوجل'));
        }

        // 6. Parse Result
        $result = $response->json();
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (!$expectJson) return $text;

        // Extract JSON if it exists in markdown blocks
        if (preg_match('/\[\s*\{.*\}\s*\]/s', $text, $matches)) {
            $text = $matches[0];
        } else {
            $text = trim(preg_replace('/^```json\s*|```\s*$/', '', $text));
        }

        $decoded = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('فشل في تحليل بيانات الـ JSON المستلمة.');
        }

        return $decoded;
    }

    /**
     * Simple chat interface
     */
    public static function chat(string $prompt, ?string $model = null): string
    {
        try {
            return self::callGemini($prompt, null, null, false, $model);
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Specialized methods for content generation
     */
    public static function analyzePdfForChapters(string $pdfFilePath, ?string $extraInstructions = null): array
    {
        $dbSetting = \App\Models\GeminiSetting::where('key', 'unit_analysis_prompt')->first();
        $prompt = ($dbSetting && !empty($dbSetting->value)) ? $dbSetting->value : "I have attached a study document (PDF). Please analyze it and suggest a logical structure to divide it into multiple chapters or lessons. For each chapter, provide a clear Arabic title and a brief description of the topics covered in that chapter. Output ONLY a JSON array of objects: [{\"title\": \"...\", \"description\": \"...\", \"q_count\": 10}] in Arabic.";

        if (!empty($extraInstructions)) {
            $prompt .= "\n\nAdditional instructions for this analysis: " . $extraInstructions;
        }

        return self::callGemini($prompt, null, $pdfFilePath, true);
    }

    public static function generateQuestionsForChapter(string $pdfFilePath, string $chapterTitle, string $chapterDescription, int $count = 10): array
    {
        $prompt = self::getSystemPromptTemplate($count, 'mix');
        $context = "Focus on chapter: \"{$chapterTitle}\". Description: {$chapterDescription}";

        return self::callGemini($prompt, $context, $pdfFilePath, true);
    }

    public static function generateQuestions(?string $textContext, ?string $pdfFilePath, int $count, string $types): array
    {
        $prompt = self::getSystemPromptTemplate($count, $types);
        return self::callGemini($prompt, $textContext ?? '', $pdfFilePath, true);
    }

    /**
     * Utilities
     */
    public static function listModels(): array
    {
        return [
            'gemini-2.0-flash' => 'Gemini 2.0 Flash 🚀 (الأحدث والأسرع)',
            'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash Lite ⚡ (خفيف واقتصادي)',
            'gemini-2.5-flash' => 'Gemini 2.5 Flash ✨ (نسخة المعاينة المتطورة)',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash (إذا كان متاحاً)',
        ];
    }

    public static function getSystemPromptTemplate(int $count = 5, string $types = 'mix'): string
    {
        $dbSetting = \App\Models\GeminiSetting::where('key', 'system_prompt')->first();
        $template = ($dbSetting && !empty($dbSetting->value)) ? $dbSetting->value : self::getDefaultTemplate();

        $text = str_replace(['{count}', '{$count}'], $count, $template);
        $text = str_replace(['{types}', '{$types}'], $types, $text);
        
        return $text;
    }

    public static function getDefaultTemplate(): string
    {
        return "You are an educator. Generate {count} {types} questions in Arabic based on the content provided. Output only JSON array.";
    }
}
