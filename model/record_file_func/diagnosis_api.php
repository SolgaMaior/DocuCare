<?php
// model/diagnosis_api.php

class DiagnosisAPI {
    private $apiUrl = 'https://dxgpt-apim.azure-api.net/api/diagnose';
    private $apiKey = '3b50f5dca76848b49872232b91a41156'; // Replace with your actual API key
    private $model = 'gpt4o';
    private $responseMode = 'direct';

    /**
     * Generate a UUID v4
     */
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get user's timezone
     */
    private function getUserTimezone() {
        return date_default_timezone_get();
    }

    /**
     * Make API request to get diagnosis
     */
    public function getDiagnosis($symptoms, $additionalDescription = '', $diseasesList = '') {
        // Combine symptoms and additional description
        $fullDescription = trim($symptoms);
        if (!empty($additionalDescription)) {
            $fullDescription .= '. ' . trim($additionalDescription);
        }

        // Prepare request data
        $requestData = [
            'description' => $fullDescription,
            'myuuid' => $this->generateUUID(),
            'lang' => 'en',
            'timezone' => $this->getUserTimezone(),
            'diseases_list' => $diseasesList,
            'model' => $this->model,
            'response_mode' => $this->responseMode
        ];

        // Initialize cURL
        $ch = curl_init($this->apiUrl);

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Cache-Control: no-cache',
                'Ocp-Apim-Subscription-Key: ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_TIMEOUT => 30
        ]);

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Handle errors
        if ($error) {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'API error: HTTP ' . $httpCode
            ];
        }

        // Decode response
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Invalid JSON response'
            ];
        }

        if (!isset($data['result']) || $data['result'] !== 'success') {
            return [
                'success' => false,
                'error' => 'Diagnosis request failed'
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }
}

/**
 * Helper function to render diagnosis results
 */
function renderDiagnosisResults($diagnosisData) {
    if (!isset($diagnosisData['data']) || empty($diagnosisData['data'])) {
        return '<p>No diagnosis results available.</p>';
    }

    $html = '<div class="diagnosis-results-container" style="margin-top: 2rem;">';
    $html .= '<h3 style="margin-bottom: 1rem; color: #1f2937;">Diagnosis Results</h3>';

    // Privacy notice
    if (isset($diagnosisData['anonymization']) && !$diagnosisData['anonymization']['hasPersonalInfo']) {
        $html .= '
        <div style="
            background: #d1fae5;
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        ">
            <span style="color: #059669;">✓</span>
            <span style="color: #065f46;">No personal information detected</span>
        </div>';
    }

    // Results grid
    $html .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">';

    $totalDiagnoses = count($diagnosisData['data']);
    foreach ($diagnosisData['data'] as $index => $diagnosis) {
        $html .= renderDiagnosisCard($diagnosis, $index, $totalDiagnoses);
    }

    $html .= '</div>'; // Close grid

    // Metadata footer
    $html .= '
    <div style="
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 1rem;
        margin-top: 1.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    ">
        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
            <span>Model: <strong>' . htmlspecialchars($diagnosisData['model']) . '</strong></span>
            <span>Language: <strong>' . htmlspecialchars($diagnosisData['detectedLang']) . '</strong></span>
            <span>Type: <strong>' . htmlspecialchars($diagnosisData['queryType']) . '</strong></span>
        </div>
    </div>';

    $html .= '</div>'; // Close container

    return $html;
}

/**
 * Helper function to render a single diagnosis card
 */
function renderDiagnosisCard($diagnosis, $index, $total) {
    $html = '
    <div class="diagnosis-card" style="
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    ">
        <div style="
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1rem;
            color: white;
        ">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: bold;">
                ' . htmlspecialchars($diagnosis['diagnosis']) . '
            </h3>
        </div>
        
        <div style="padding: 1.5rem;">
            <p style="color: #374151; line-height: 1.6; margin-bottom: 1rem;">
                ' . htmlspecialchars($diagnosis['description']) . '
            </p>';

    // Matching symptoms
    if (!empty($diagnosis['symptoms_in_common'])) {
        $html .= '
            <div style="margin-bottom: 1rem;">
                <h4 style="
                    color: #059669;
                    font-weight: 600;
                    margin-bottom: 0.5rem;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                ">
                    <span style="color: #10b981;">✓</span> Matching Symptoms
                </h4>
                <ul style="margin: 0; padding-left: 1.5rem; color: #4b5563;">';
        
        foreach ($diagnosis['symptoms_in_common'] as $symptom) {
            $html .= '<li style="margin-bottom: 0.25rem;">' . htmlspecialchars($symptom) . '</li>';
        }
        
        $html .= '</ul>
            </div>';
    }

    // Non-matching symptoms
    if (!empty($diagnosis['symptoms_not_in_common'])) {
        $html .= '
            <div>
                <h4 style="
                    color: #dc2626;
                    font-weight: 600;
                    margin-bottom: 0.5rem;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                ">
                    <span style="color: #ef4444;">✗</span> Non-matching Symptoms
                </h4>
                <ul style="margin: 0; padding-left: 1.5rem; color: #4b5563;">';
        
        foreach ($diagnosis['symptoms_not_in_common'] as $symptom) {
            $html .= '<li style="margin-bottom: 0.25rem;">' . htmlspecialchars($symptom) . '</li>';
        }
        
        $html .= '</ul>
            </div>';
    }

    $html .= '
        </div>
        
        <div style="
            background: #f9fafb;
            padding: 0.75rem 1.5rem;
            color: #6b7280;
            font-size: 0.875rem;
        ">
            Diagnosis ' . ($index + 1) . ' of ' . $total . '
        </div>
    </div>';

    return $html;
}
?>