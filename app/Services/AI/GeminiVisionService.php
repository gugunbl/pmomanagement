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
        
        try {
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
                        'maxOutputTokens' => 2000,
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
                
                return [
                    'success' => true,
                    'data' => $parsedData,
                    'raw_response' => $result,
                    'confidence' => $this->calculateConfidence($parsedData),
                ];
            }
            
            $errorBody = $response->json();
            $errorMsg = $errorBody['error']['message'] ?? ('HTTP ' . $response->status());
            throw new \Exception("Gemini API failed: " . $errorMsg);
            
        } catch (\Exception $e) {
            Log::error('Gemini Vision Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Build structured prompt for Gemini
     */
    protected function buildExtractionPrompt(): string
    {
        return <<<PROMPT
You are an expert data extraction assistant. Analyze this Indonesian daily task report image (screenshot from WhatsApp or similar chat) and extract the following information.

Return ONLY valid JSON with this exact structure:
{
  "employee_name": "Full name of employee (extract from the report title/header)",
  "report_date": "YYYY-MM-DD (convert Indonesian date like '11 Mei 2026' to '2026-05-11')",
  "day_name": "Day of week in Indonesian (e.g. Senin, Selasa, etc.)",
  "activities": [
    {
      "start_time": "HH:MM (24-hour format)",
      "end_time": "HH:MM (24-hour format)",
      "description": "Full activity description in original language",
      "is_parallel": false
    }
  ],
  "total_productive_time": "e.g. 6 jam 18 menit (extract from the report if available)",
  "work_utilization_percentage": 78.8,
  "effective_work_hours": 8
}

IMPORTANT Rules:
1. Extract ALL activities with their exact time ranges from the image
2. Mark parallel/overlapping activities with is_parallel: true
3. Preserve Indonesian text exactly as written in the image
4. The report_date MUST be in YYYY-MM-DD format
5. start_time and end_time MUST be in HH:MM 24-hour format
6. work_utilization_percentage should be a number (e.g. 78.8 not "78.8%")
7. If a field cannot be determined, set it to null
8. Return ONLY the JSON object, no markdown fences, no explanations
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
        $requiredFields = ['employee_name', 'report_date', 'activities'];
        $optionalFields = ['day_name', 'total_productive_time', 'work_utilization_percentage'];
        
        $presentRequired = 0;
        foreach ($requiredFields as $field) {
            if (!empty($data[$field])) {
                $presentRequired++;
            }
        }
        
        $presentOptional = 0;
        foreach ($optionalFields as $field) {
            if (!empty($data[$field]) || (isset($data[$field]) && $data[$field] === 0)) {
                $presentOptional++;
            }
        }
        
        // Required fields worth 70%, optional fields worth 30%
        $requiredScore = ($presentRequired / count($requiredFields)) * 0.7;
        $optionalScore = ($presentOptional / count($optionalFields)) * 0.3;
        
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
