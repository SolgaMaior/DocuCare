<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles/record.css">
  <link rel="stylesheet" href="styles/dashboard.css">
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="resources/dashboardassets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
  <link rel="stylesheet" href="resources/dashboardassets/css/ready.css">
  <link rel="stylesheet" href="resources/dashboardassets/css/demo.css">
  <link rel="preload" as="image" href="https://a.tile.openstreetmap.org/14/13421/7468.png">
</head>

<body>
  
  <div id="maincontainer">
    <?php require('partials/sidebar.php'); ?>
    <div class="container-fluid">
      <h4 class="page-title">Dashboard</h4>
      <div class="row">
        <div class="col-md-3">
          <div class="card card-stats card-warning">
            <div class="card-body">
              <div class="row">
                <div class="col-5">
                  <div class="icon-big text-center">
                    <i class="la la-users"></i>
                  </div>
                </div>
                <div class="col-7 d-flex align-items-center">
                  <div class="numbers">
                    <p class="card-category">Current Users</p>
                    <h4 class="card-title" id="total-users">
                      <span class="spinner-border spinner-border-sm"></span>
                    </h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="card card-stats card-success">
            <div class="card-body">
              <div class="row">
                <div class="col-5">
                  <div class="icon-big text-center">
                    <i class="la la-bar-chart"></i>
                  </div>
                </div>
                <div class="col-7 d-flex align-items-center">
                  <div class="numbers">
                    <p class="card-category">Illness Count</p>
                    <h4 class="card-title" id="total-illness">
                      <span class="spinner-border spinner-border-sm"></span>
                    </h4>
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
                    <h4 class="card-title" id="total-citizens">
                      <span class="spinner-border spinner-border-sm"></span>
                    </h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php if (CURRENT_USER_IS_ADMIN): ?>
        <div class="col-md-3">
          <div class="card card-stats card-primary">
            <div class="card-body">
              <div class="row">
                <div class="col-5">
                  <div class="icon-big text-center">
                    <i class="la la-check-circle"></i>
                  </div>
                </div>
                
                <div class="col-7 d-flex align-items-center">
                  <div class="numbers">
                    <p class="card-category">Pending Accounts</p>
                    <h4 class="card-title" id="total-pending">
                      <span class="spinner-border spinner-border-sm"></span>
                    </h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Map and Chart Row -->
      <div class="row">
        <div class="col-md-9">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Map of the Barangay</h4>
              <p class="card-category">
                See the current status in your area
                  <button onclick="refreshClusters()" class="btn btn-sm btn-primary float-right">
                    <i class="la la-refresh"></i> Refresh Clusters
                  </button>
              </p>
            </div>
            <div class="card-body">
              <!-- Date Range Filter -->
              <div class="date-filter-container mb-3">
                <div class="date-filter-row d-flex align-items-center gap-2">
                  <label><i class="la la-calendar"></i> Date Range:</label>
                  <input type="date" id="filter-start-date" class="form-control form-control-sm" style="width: 160px;">
                  <span>to</span>
                  <input type="date" id="filter-end-date" class="form-control form-control-sm" style="width: 160px;">
                  <button onclick="applyDateFilter()" class="btn btn-sm btn-primary ml-2">
                    <i class="la la-filter"></i> Apply
                  </button>
                  <button onclick="resetDateFilter()" class="btn btn-sm btn-secondary ml-1">
                    <i class="la la-undo"></i> Reset
                  </button>
                  <div class="btn-group ml-3">
                    <button class="btn btn-sm btn-outline-info" onclick="setQuickDateRange(7)">Last 7 Days</button>
                    <button class="btn btn-sm btn-outline-info" onclick="setQuickDateRange(30)">Last 30 Days</button>
                    <button class="btn btn-sm btn-outline-info" onclick="setQuickDateRange(90)">Last 3 Months</button>
                  </div>
                </div>
                <div id="date-filter-info" class="text-muted mt-1 small">Showing this month's data</div>
              </div>
              <div class="mapcontainer">
                <div id="map" style="height:50vh; width: 100%; border-radius: 10px; border: 3px solid #4fa7e6;"></div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Citizen Distribution</h4>
            </div>
            <div class="card-body">
              <canvas id="citizenChart" width="400" height="400"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Load JavaScript Libraries -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Dashboard Logic -->
  <script src="model/scripts/map_script.js"></script>

</body>
</html>