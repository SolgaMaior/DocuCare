<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= $pageTitle ?? 'Reports' ?> - DocuCare</title>
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <link rel="stylesheet" href="styles/inventory.css">
  <link rel="stylesheet" href="styles/record.css">
  <link rel="stylesheet" href="styles/reports.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="content">
    

    <div class="report-controls no-print">
      <div class="filter-bar">
        <form method="GET" action="" class="filter-form">
          <input type="hidden" name="page" value="reports">
          
          <select name="type" onchange="this.form.submit()">
            <option value="overview" <?= $reportType === 'overview' ? 'selected' : '' ?>>Overview Report</option>
            <option value="citizens" <?= $reportType === 'citizens' ? 'selected' : '' ?>>Citizens Report</option>
            <option value="health" <?= $reportType === 'health' ? 'selected' : '' ?>>Health Report</option>
            <option value="inventory" <?= $reportType === 'inventory' ? 'selected' : '' ?>>Inventory Report</option>
          </select>

          <select name="purok">
            <option value="">All Puroks</option>
            <?php foreach ($puroks as $purok): ?>
              <option value="<?= $purok['purokID'] ?>" <?= $purokID == $purok['purokID'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($purok['purokName']) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <input type="date" name="start_date" value="<?= $startDate ?>" placeholder="Start Date">
          <input type="date" name="end_date" value="<?= $endDate ?>" placeholder="End Date">
          
          <button type="submit">Generate Report</button>
        </form>
        
        <button class="btn btn-primary" style="height:3rem;" onclick="window.print()"> Print Report</button>
      </div>
    </div>

    <div class="report-header">
      <div class="report-title">
        <h1>Barangay Health Center Report</h1>
        <p class="report-subtitle">
          <?php 
            $reportTitles = [
              'overview' => 'Comprehensive Overview Report',
              'citizens' => 'Citizens Demographics Report',
              'health' => 'Health & Medical Services Report',
              'inventory' => 'Medical Inventory Report'
            ];
            echo $reportTitles[$reportType] ?? 'Report';
          ?>
        </p>
        <p class="report-date">Generated on: <?= date('F d, Y h:i A') ?></p>
        <?php if ($startDate || $endDate): ?>
          <p class="report-period">
            Period: <?= $startDate ? date('M d, Y', strtotime($startDate)) : 'Start' ?> - 
            <?= $endDate ? date('M d, Y', strtotime($endDate)) : 'Present' ?>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <div class="report-content">
      <?php if ($reportType === 'overview'): ?>
        <?php include __DIR__ . '/reports/overview_report.php'; ?>
      <?php elseif ($reportType === 'citizens'): ?>
        <?php include __DIR__ . '/reports/citizen_report.php'; ?>
      <?php elseif ($reportType === 'health'): ?>
        <?php include __DIR__ . '/reports/health_report.php'; ?>
      <?php elseif ($reportType === 'inventory'): ?>
        <?php include __DIR__ . '/reports/inventory_report.php'; ?>
      <?php endif; ?>
    </div>

    <div class="report-footer print-only">
      <div class="signature-section">
        <div class="signature-box">
          <div class="signature-line"></div>
          <p class="signature-label">Prepared by</p>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div>
          <p class="signature-label">Reviewed by</p>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div>
          <p class="signature-label">Barangay Health Officer</p>
        </div>
      </div>
    </div>
  </div>

</body>
</html>