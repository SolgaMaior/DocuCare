<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Diagnosis Results</title>
    <link rel="stylesheet" href="styles/diagnosis.css">
    <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
</head>
<body class="diagnosis-page">



    <div class="diagnosis-container">
        <a href="index.php?page=records" class="back-link">← Back to Records</a>

        <div class="diagnosis-header">
            <h2 class="diagnosis-title">Medical Diagnosis Results</h2>
        </div>

        <!-- Display Input Information -->
        <?php if (!empty($symptoms)): ?>
            <div class="input-display-card">
                <h3>Patient Information</h3>
                
                <div class="input-item">
                    <span class="input-label">Primary Symptoms</span>
                    <div class="input-content">
                        <?php echo nl2br(htmlspecialchars($symptoms)); ?>
                    </div>
                </div>

                <?php if (!empty($additionalDescription)): ?>
                    <div class="input-item">
                        <span class="input-label">Additional Description</span>
                        <div class="input-content">
                            <?php echo nl2br(htmlspecialchars($additionalDescription)); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($citizenID): ?>
                    <div class="input-item">
                        <span class="input-label">Citizen Name</span>
                        <div class="input-content">
                            (#<?php echo htmlspecialchars($citizenID); ?>) <?php echo htmlspecialchars($firstname . ' ' . $middlename . ' ' . $lastname); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span class="alert-icon">⚠</span>
                <div>
                    <strong>Error</strong>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Diagnosis Results -->
        <?php if ($diagnosisResults): ?>
            <div class="results-section">
                <div class="results-header-card">
                    <h3>Possible Diagnoses</h3>
                    <p>
                        Found <?php echo count($diagnosisResults['data']['data']); ?> possible diagnoses based on the symptoms
                    </p>
                </div>

                <!-- Privacy Notice
                <?php if (isset($diagnosisResults['data']['anonymization']) && !$diagnosisResults['data']['anonymization']['hasPersonalInfo']): ?>
                    <div class="alert alert-success">
                        <span class="alert-icon">✓</span>
                        <span>No personal information detected in the input</span>
                    </div>
                <?php endif; ?> -->

                <!-- Results Grid -->
                <div class="results-grid">
                    <?php 
                    $totalDiagnoses = count($diagnosisResults['data']['data']);
                    foreach ($diagnosisResults['data']['data'] as $index => $diagnosis): 
                    ?>
                        <div class="diagnosis-card">
                            <div class="diagnosis-card-header">
                                <h4><?php echo htmlspecialchars($diagnosis['diagnosis']); ?></h4>
                            </div>

                            <div class="diagnosis-card-body">
                                <p class="diagnosis-description">
                                    <?php echo htmlspecialchars($diagnosis['description']); ?>
                                </p>

                                <?php if (!empty($diagnosis['symptoms_in_common'])): ?>
                                    <div class="symptoms-section">
                                        <h5 class="symptoms-title matching">
                                            <span>✓</span> Matching Symptoms
                                        </h5>
                                        <ul class="symptoms-list">
                                            <?php foreach ($diagnosis['symptoms_in_common'] as $symptom): ?>
                                                <li><?php echo htmlspecialchars($symptom); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($diagnosis['symptoms_not_in_common'])): ?>
                                    <div class="symptoms-section">
                                        <h5 class="symptoms-title non-matching">
                                            <span>✗</span> Non-matching Symptoms
                                        </h5>
                                        <ul class="symptoms-list">
                                            <?php foreach ($diagnosis['symptoms_not_in_common'] as $symptom): ?>
                                                <li><?php echo htmlspecialchars($symptom); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="diagnosis-card-footer">
                                Diagnosis <?php echo $index + 1; ?> of <?php echo $totalDiagnoses; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Metadata -->
                <div class="metadata-card">
                    <h4>Analysis Information</h4>
                    <div class="metadata-content">
                        <div class="metadata-item">
                            Model: <strong><?php echo htmlspecialchars($diagnosisResults['data']['model']); ?></strong>
                        </div>
                        <div class="metadata-item">
                            Language: <strong><?php echo htmlspecialchars($diagnosisResults['data']['detectedLang']); ?></strong>
                        </div>
                        <div class="metadata-item">
                            Query Type: <strong><?php echo htmlspecialchars($diagnosisResults['data']['queryType']); ?></strong>
                        </div>
                        <div class="metadata-item">
                            Generated: <strong><?php echo date('F j, Y g:i A'); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif (empty($error) && empty($symptoms)): ?>
            <!-- No Input Provided -->
            <div class="no-results-card">
                <div class="no-results-icon">🩺</div>
                <h3>No Symptoms Provided</h3>
                <p>Please enter symptoms from the records page to generate a diagnosis.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>