<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/record.css">
     <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
    <title>Dashboard</title>
</head>
<body>
     <div class="dashboard-layout">
    <?php require('partials/sidebar.php'); ?>
    <div class="dashboard-content">
      <?php require('map.view.php'); ?>
    </div>
  </div>
</body>
</html>