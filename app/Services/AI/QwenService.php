<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QwenService
{
    protected $apiKey;
    protected $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = env('QWEN_API_KEY', 'sk-xxx'); // Replace with actual or config
        $this->baseUrl = 'https://dashscope.aliyuncs.com';
    }
    
    /**
     * Extract data from report image using Qwen-VL
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
            
            // Call Qwen-VL API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/v1/services/aigc/multimodal-generation/generation', [
                'model' => 'qwen-vl-max',
                'input' => [
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                [
                                    'type' => 'image',
                                    'image' => "data:{$mimeType};base64,{$imageBase64}"
                                ]
                            ]
                        ]
                    ]
                ],
                'parameters' => [
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.1, // Low temperature for consistency
                ]
            ]);
            
            $processingTime = now()->diffInMilliseconds($startTime);
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Log successful AI processing
                $this->logAIProcessing(null, 'qwen_vl', $prompt, $result, $response->status(), $processingTime);
                
                return [
                    'success' => true,
                    'data' => $this->parseAIResponse($result),
                    'raw_response' => $result,
                    'confidence' => $this->calculateConfidence($result),
                ];
            }
            
            throw new \Exception("Qwen API failed: " . $response->status());
            
        } catch (\Exception $e) {
            Log::error('Qwen AI Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Build structured prompt for Qwen
     */
    protected function buildExtractionPrompt(): string
    {
        return <<<PROMPT
You are an expert data extraction assistant. Analyze this Indonesian daily task report image and extract the following information in JSON format:

{
  "employee_name": "Full name of employee",
  "report_date": "YYYY-MM-DD format",
  "day_name": "Day of week in Indonesian",
  "activities": [
    {
      "start_time": "HH:MM",
      "end_time": "HH:MM",
      "description": "Full activity description",
      "is_parallel": false
    }
  ],
  "total_productive_time": "X jam Y menit",
  "work_utilization_percentage": 00.0,
  "effective_work_hours": 8
}

Rules:
1. Extract ALL activities with their time ranges
2. Mark parallel activities with is_parallel: true
3. Preserve Indonesian text exactly as written
4. Calculate duration in minutes for each activity
5. If confidence is low for any field, set it to null

Return ONLY valid JSON, no explanations.
PROMPT;
    }
    
    /**
     * Parse and validate AI response
     */
    protected function parseAIResponse(array $result): array
    {
        $output = $result['output']['choices'][0]['message']['content'] ?? '';
        
        // Extract JSON from response
        preg_match('/\{.*\}/s', $output, $matches);
        $jsonData = json_decode($matches[0] ?? '{}', true);
        
        return $jsonData;
    }
    
    /**
     * Calculate confidence score
     */
    protected function calculateConfidence(array $result): float
    {
        // Check if all required fields are present
        $data = $this->parseAIResponse($result);
        
        $requiredFields = ['employee_name', 'report_date', 'activities'];
        $presentFields = 0;
        
        foreach ($requiredFields as $field) {
            if (!empty($data[$field])) {
                $presentFields++;
            }
        }
        
        return $presentFields / count($requiredFields);
    }
    
    /**
     * Log AI processing for audit and cost tracking
     */
    protected function logAIProcessing(?int $reportId, string $provider, $request, $response, int $statusCode, int $processingTime): void
    {
        DB::table('ai_processing_logs')->insert([
            'report_id' => $reportId,
            'ai_provider' => $provider,
            'endpoint' => 'multimodal-generation',
            'request_payload' => json_encode(is_array($request) ? $request : ['prompt' => $request]),
            'response_payload' => json_encode($response),
            'status_code' => $statusCode,
            'processing_time_ms' => $processingTime,
            'token_usage_input' => $response['usage']['input_tokens'] ?? 0,
            'token_usage_output' => $response['usage']['output_tokens'] ?? 0,
            'cost_usd' => $this->calculateCost($response),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
     * Calculate API cost based on token usage
     */
    protected function calculateCost(array $response): float
    {
        // Qwen-VL pricing (example)
        $inputTokenCost = 0.0008; // per 1K tokens
        $outputTokenCost = 0.0012; // per 1K tokens
        
        $inputTokens = ($response['usage']['input_tokens'] ?? 0) / 1000;
        $outputTokens = ($response['usage']['output_tokens'] ?? 0) / 1000;
        
        return ($inputTokens * $inputTokenCost) + ($outputTokens * $outputTokenCost);
    }
}
