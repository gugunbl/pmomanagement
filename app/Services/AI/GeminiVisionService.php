<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GeminiVisionService
{
    protected $apiKey;
    protected $baseUrl;
    protected $model;
    
    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
        $this->model = 'gemini-2.5-flash';
    }
    
     /**
     * Extract data from report image using Gemini Vision
     */
    public function extractFromImage(string $imagePath): array
    {
        $startTime = now();
        $modelsToTry = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-flash-latest'];
        $lastException = null;

        foreach ($modelsToTry as $modelToUse) {
            try {
                $this->model = $modelToUse;
                
                // Convert image to base64
                $imageBase64 = base64_encode(file_get_contents($imagePath));
                $mimeType = mime_content_type($imagePath);
                
                // Prepare prompt for daily report extraction
                $prompt = $this->buildExtractionPrompt();
                
                // Call Gemini API with inline image data
                $response = Http::timeout(60)->post(
                    $this->baseUrl . '/models/' . $this->model . ':generateContent?key=' . $this->apiKey,
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $prompt
                                    ],
                                    [
                                        'inline_data' => [
                                            'mime_type' => $mimeType,
                                            'data' => $imageBase64
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'maxOutputTokens' => 8000,
                            'responseMimeType' => 'application/json',
                        ]
                    ]
                );
                
                $processingTime = now()->diffInMilliseconds($startTime);
                
                if ($response->successful()) {
                    $result = $response->json();
                    
                    // Log successful AI processing
                    $this->logAIProcessing(null, $result, $response->status(), $processingTime);
                    
                    $parsedData = $this->parseAIResponse($result);
                    
                    // Enforce Template Validation
                    if (isset($parsedData['is_valid_template']) && $parsedData['is_valid_template'] === false) {
                        throw new \Exception("template tidak sesuai, dan tidak bisa di proses");
                    }
                    if (isset($parsedData['format']) && $parsedData['format'] === 'invalid') {
                        throw new \Exception("template tidak sesuai, dan tidak bisa di proses");
                    }
                    
                    // Validate structure: must have employee_name and either entries or report_date
                    if (empty($parsedData['employee_name']) || (empty($parsedData['entries']) && empty($parsedData['report_date']))) {
                        throw new \Exception("template tidak sesuai, dan tidak bisa di proses");
                    }
                    
                    return [
                        'success' => true,
                        'data' => $parsedData,
                        'raw_response' => $result,
                        'confidence' => $this->calculateConfidence($parsedData),
                    ];
                }
                
                $errorBody = $response->json();
                $errorMsg = $errorBody['error']['message'] ?? ('HTTP ' . $response->status());
                throw new \Exception("Gemini API failed with model {$this->model}: " . $errorMsg);
                
            } catch (\Exception $e) {
                $lastException = $e;
                // If it is a template validation failure, abort immediately to avoid wasting API quota/retrying
                if ($e->getMessage() === "template tidak sesuai, dan tidak bisa di proses") {
                    break;
                }
                Log::warning("Gemini Vision model {$modelToUse} failed: " . $e->getMessage() . ". Trying next model fallback...");
            }
        }

        // If all models failed or template is invalid, log error and return
        Log::error('Gemini Vision Error: ' . $lastException->getMessage());
        
        return [
            'success' => false,
            'error' => $lastException->getMessage(),
        ];
    }
    
    /**
     * Build structured prompt for Gemini
     */
    protected function buildExtractionPrompt(): string
    {
        return <<<PROMPT
You are an expert daily task report verifier and data extractor.

First, you must validate if the uploaded image is a daily task report, daily activity log, or daily work log (either a table format or a text/chat format).

**APPROVED REPORT TEMPLATES:**

1. **FORMAT A - Grid/Table Daily Report:**
   - A table, sheet, or grid containing columns for dates, activities, tasks, detail, time/minutes, or duration.
   - Typically lists an employee's name at the top or in a header.

2. **FORMAT B - Text/Chat Daily Report:**
   - A chat screenshot (e.g. WhatsApp, Telegram, Slack) or a text document showing daily tasks.
   - Contains a date header (e.g. "18 Mei 2026"), time slots (e.g. "10:05", "10.15 - 15.36"), activities, and optionally a work utilization percentage section at the end.

---

**VALIDATION INSTRUCTION:**
- If the image is a valid daily task report (either Format A or Format B), set "is_valid_template": true.
- If the image is NOT a daily task report at all (for example, if it is a random screenshot of a dashboard, a chart/graph, a photograph of an object, a selfie, a shopping receipt, an invoice, a general diagram, or a meme), set "is_valid_template": false, "format": "invalid", "error_message": "template tidak sesuai, dan tidak bisa di proses".

---

Return ONLY valid JSON with this exact structure:

{
  "is_valid_template": true or false,
  "format": "table" or "chat" or "invalid",
  "error_message": "template tidak sesuai, dan tidak bisa di proses" or null,
  "employee_name": "Full name of employee from the header/text",
  "entries": [
    {
      "report_date": "YYYY-MM-DD (convert Indonesian date like 'Senin, 04 Mei 2026' or '18 Mei 2026' to '2026-05-04' or '2026-05-18')",
      "day_name": "Day name in Indonesian (Senin, Selasa, etc.)",
      "activities": [
        {
          "start_time": "HH:MM or null if not available",
          "end_time": "HH:MM or null if not available",
          "description": "Full activity description exactly as written",
          "duration_minutes": 80,
          "is_parallel": false
        }
      ]
    }
  ]
}

IMPORTANT Rules:
1. Be inclusive and permissive: if the image contains any form of daily work report or daily task log, it IS a valid template.
2. For Format A (table): Each row or date is one entry. Parse all columns. Convert duration to integer minutes.
3. For Format B (chat): Calculate duration_minutes as the difference between start_time and end_time, or map durations if stated.
4. Output ONLY the JSON block.
PROMPT;
    }
    
    /**
     * Parse and validate AI response from Gemini
     */
    protected function parseAIResponse(array $result): array
    {
        // Gemini response structure: candidates[0].content.parts[0].text
        $output = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Clean possible markdown fences
        $output = trim($output);
        $output = preg_replace('/^```json\s*/i', '', $output);
        $output = preg_replace('/\s*```$/i', '', $output);
        
        // Extract JSON from response
        $jsonData = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback: try to extract JSON object from text
            preg_match('/\{.*\}/s', $output, $matches);
            $jsonData = json_decode($matches[0] ?? '{}', true);
        }
        
        return $jsonData ?: [];
    }
    
    /**
     * Calculate confidence score based on extracted data completeness
     */
    protected function calculateConfidence(array $data): float
    {
        // Support both old structure and new multi-entry structure
        $employee_name = $data['employee_name'] ?? null;
        
        $report_date = null;
        $activities = [];
        $day_name = null;
        
        if (!empty($data['entries']) && is_array($data['entries'])) {
            $firstEntry = $data['entries'][0] ?? [];
            $report_date = $firstEntry['report_date'] ?? null;
            $activities = $firstEntry['activities'] ?? [];
            $day_name = $firstEntry['day_name'] ?? null;
        } else {
            $report_date = $data['report_date'] ?? null;
            $activities = $data['activities'] ?? [];
            $day_name = $data['day_name'] ?? null;
        }
        
        $presentRequired = 0;
        if (!empty($employee_name)) $presentRequired++;
        if (!empty($report_date)) $presentRequired++;
        if (!empty($activities)) $presentRequired++;
        
        $presentOptional = 0;
        if (!empty($day_name)) {
            $presentOptional++;
        }
        if (isset($data['work_utilization_percentage']) || isset($data['total_productive_time'])) {
            $presentOptional++;
        }
        
        // Required fields worth 70%, optional fields worth 30%
        $requiredScore = ($presentRequired / 3) * 0.7;
        $optionalScore = ($presentOptional / 2) * 0.3;
        
        return round($requiredScore + $optionalScore, 4);
    }
    
    /**
     * Log AI processing for audit and cost tracking
     */
    protected function logAIProcessing(?int $reportId, array $response, int $statusCode, int $processingTime): void
    {
        try {
            DB::table('ai_processing_logs')->insert([
                'report_id' => $reportId,
                'ai_provider' => 'gemini',
                'endpoint' => '/v1beta/models/' . $this->model . ':generateContent',
                'request_payload' => json_encode(['model' => $this->model, 'note' => 'Gemini 2.0 Flash Vision']),
                'response_payload' => json_encode($response),
                'status_code' => $statusCode,
                'processing_time_ms' => $processingTime,
                'token_usage_input' => $response['usageMetadata']['promptTokenCount'] ?? 0,
                'token_usage_output' => $response['usageMetadata']['candidatesTokenCount'] ?? 0,
                'cost_usd' => $this->calculateCost($response),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log AI processing: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate API cost based on token usage (Gemini 2.0 Flash pricing)
     */
    protected function calculateCost(array $response): float
    {
        // Gemini 2.0 Flash pricing (free tier has generous limits, paid tier below)
        $inputTokenCost = 0.0001;  // per 1K tokens
        $outputTokenCost = 0.0004; // per 1K tokens
        
        $inputTokens = ($response['usageMetadata']['promptTokenCount'] ?? 0) / 1000;
        $outputTokens = ($response['usageMetadata']['candidatesTokenCount'] ?? 0) / 1000;
        
        return round(($inputTokens * $inputTokenCost) + ($outputTokens * $outputTokenCost), 6);
    }
}
