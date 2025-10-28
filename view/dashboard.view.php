<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles/record.css">
  <link rel="stylesheet" href="styles/dashboard.css">
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <title>Dashboard</title>
  <link rel="preload" as="style" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
  <?php require('partials/sidebar.php'); ?>

  <main class="content">
    <?php require('partials/map.view.php'); ?>
  </main>
</body>
</html>
