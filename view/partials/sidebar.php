<?php
require_once('authCheck.php'); // ensures CURRENT_USER_* are defined
?>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+SC:wght@400;700&display=swap" rel="stylesheet">

<style>
  #Title {
    font-family: 'Cormorant SC', serif;
  }
  </style>

<div class="sidebar">

  <div class="relative flex items-center justify-center mb-8">
    <img src="resources/images/Logo.svg" alt="DocuCare Logo" class="sidelogo"/>
    <h2 id="Title" class="relative text-3xl font-bold text-white "><br><br> DocuCare</h2>
  </div>

  <ul class="space-y-2 font-medium">

    <!-- Dashboard (Visible to everyone) -->
    <li>
      <a href="/DocuCare/index.php?page=dashboard" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-400 group">
        <svg class="w-5 h-5 ml-3" fill="white" viewBox="0 0 22 21">
          <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
          <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
        </svg>
        <span class="ms-3">Dashboard</span>
      </a>
    </li>




    <li>
      <a href="/DocuCare/index.php?page=records" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-400 group">
        <svg class="w-5 h-5 ml-3" fill="white" viewBox="0 0 20 18">
          <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
        </svg>
        <span class="ms-3">
          <?= CURRENT_USER_IS_ADMIN ? 'Citizen Records' : 'My Records' ?>
        </span>
      </a>
    </li>
  

    <?php if (!CURRENT_USER_IS_ADMIN): ?>
    <!-- Set Appointment (Visible to everyone) -->
    <li>
      <a href="/DocuCare/index.php?page=appointments" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-400 group">
        <svg class="w-5 h-5 ml-3" fill="white" viewBox="0 0 20 20">
          <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z"/>
          <path d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1.969 1.969 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z"/>
        </svg>
        <span class="ms-3">Set Appointment</span>
      </a>
    </li>
    <?php endif; ?>
    <!-- Admin Only -->
    <?php if (CURRENT_USER_IS_ADMIN): ?>
    <li>
      <a href="/DocuCare/index.php?page=schedules" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-400 group">
        <svg class="w-5 h-5 ml-3" fill="white" viewBox="0 0 20 20">
          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
        </svg>
        <span class="ms-3">Schedules</span>
      </a>
    </li>

    <li>
      <a href="/DocuCare/index.php?page=inventory" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-400 group">
        <svg class="w-5 h-5 ml-3" fill="white" viewBox="0 0 18 20">
          <path d="M17 5.923A1 1 0 0 0 16 5h-3V4a4 4 0 1 0-8 0v1H2a1 1 0 0 0-1 .923L.086 17.846A2 2 0 0 0 2.08 20h13.84a2 2 0 0 0 1.994-2.153L17 5.923ZM7 9a1 1 0 0 1-2 0V7h2v2Zm0-5a2 2 0 1 1 4 0v1H7V4Zm6 5a1 1 0 1 1-2 0V7h2v2Z"/>
        </svg>
        <span class="ms-3">Inventory</span>
      </a>
    </li>
    
    <li>
      <a href="/DocuCare/index.php?page=approve_accounts"
        class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-400 group"
      >
        <svg class="w-5 h-5 ml-2" fill="white" viewBox="0 0 18 20">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
        <span class="ms-3">Approve Accounts</span>
      </a>
    </li>
    <li>
      <a href="/DocuCare/index.php?page=reports"
        class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-400 group"
      >
        <svg class="w-5 h-5 ml-2" fill="white" viewBox="0 0 18 20">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
        <span class="ms-3">Reports</span>
      </a>
    </li>
   <?php endif; ?>
    
    <!-- Logout -->
    <li>
      <a href="/DocuCare/index.php?page=logout"
        class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-400 group"
        onclick="return confirm('Are you sure you want to log out?');">
        <svg class="w-5 h-5 ml-3" fill="white" viewBox="0 0 18 20">
          <path d="M17 16l4-4m0 0l-4-4m4 4h-14m5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h3a3 3 0 013 3v1"></path>
        </svg>
        <span class="ms-3">Logout</span>
      </a>
    </li>

  </ul>
</div>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
