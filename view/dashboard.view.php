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
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <link rel="stylesheet" href="resources/dashboardassets/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
	<link rel="stylesheet" href="resources/dashboardassets/css/ready.css">
	<link rel="stylesheet" href="resources/dashboardassets/css/demo.css">
</head>


<body>
 <?php require('partials/sidebar.php'); ?>

 <div id="maincontainer">
  <div class="container-fluid">
    <h4 class="page-title">Dashboard</h4>
    <div class="row">
      <div class="col-md-3">
        <div class="card card-stats card-warning">
          <div class="card-body ">
            <div class="row">
              <div class="col-5">
                <div class="icon-big text-center">
                  <i class="la la-users"></i>
                </div>
              </div>
              <div class="col-7 d-flex align-items-center">
                <div class="numbers">
                  <p class="card-category">Current Users</p>
                  <h4 class="card-title">1,294</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card card-stats card-success">
          <div class="card-body ">
            <div class="row">
              <div class="col-5">
                <div class="icon-big text-center">
                  <i class="la la-bar-chart"></i>
                </div>
              </div>
              <div class="col-7 d-flex align-items-center">
                <div class="numbers">
                  <p class="card-category">Illness Count this Month</p>
                  <h4 class="card-title">345</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card card-stats card-danger">
          <div class="card-body">
            <div class="row">
              <div class="col-5">
                <div class="icon-big text-center">
                  <i class="la la-newspaper-o"></i>
                </div>
              </div>
              <div class="col-7 d-flex align-items-center">
                <div class="numbers">
                  <p class="card-category">Documented Patients</p>
                  <h4 class="card-title">1303</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 ">
        <div class="card card-stats card-primary">
          <div class="card-body ">
            <div class="row">
              <div class="col-5">
                <div class="icon-big text-center">
                  <i class="la la-check-circle"></i>
                </div>
              </div>
              <div class="col-7 d-flex align-items-center">
                <div class="numbers">
                  <p class="card-category">Pending Accounts </p>
                  <h4 class="card-title">576</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-9">
        <div class="card" style="width: 98rem; ">
          <div class="card-header">
            <h4 class="card-title">Map of the Barangay</h4>
            <p class="card-category">
            See the current status in your area</p>
          </div>
          <div class="card-body">
            <div class="mapcontainer">
              <div class="map">
                <?php require('partials/map.view.php'); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div>

</body>
</html>
