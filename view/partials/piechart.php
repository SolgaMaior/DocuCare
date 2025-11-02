<canvas id="citizenChart" width="400" height="400"></canvas>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const data = <?php echo $data_json; ?>;

  const labels = data.map(item => item.purokName);
  const values = data.map(item => item.citizen_count);

  new Chart(document.getElementById('citizenChart'), {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        label: 'Citizens per Purok',
        data: values,
        backgroundColor: [
          '#ff6384',
          '#36a2eb',
          '#ffcd56',
          '#4bc0c0',
          '#9966ff'
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        title: {
          display: true,
          text: 'Citizen Distribution by Purok'
        }
      }
    }
  });
</script>
