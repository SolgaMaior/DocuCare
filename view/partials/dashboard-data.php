<script defer>
document.addEventListener("DOMContentLoaded", () => {
  // Fetch all dashboard data at once
  fetch('controllers/get_dashboard_data.php')
    .then(res => res.json())
    .then(data => {
      // Update stats cards
      document.getElementById('total-users').textContent = data.stats.total_users;
      document.getElementById('total-illness').textContent = data.stats.total_illness_records;
      document.getElementById('total-citizens').textContent = data.stats.total_citizens;
      document.getElementById('total-pending').textContent = data.stats.total_pending_accounts;

      initPieChart(data.pie);
      

      initMap(data.map);
    })
    .catch(err => {
      console.error('Failed to load dashboard data:', err);
      // Show error message to user
      document.getElementById('total-users').textContent = 'Error';
      document.getElementById('total-illness').textContent = 'Error';
      document.getElementById('total-citizens').textContent = 'Error';
      document.getElementById('total-pending').textContent = 'Error';
    });
});


</script>