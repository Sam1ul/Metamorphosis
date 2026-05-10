<?php
require_once 'header.php';
?>
<!-- Hero Section -->
<div class="bg-white/90 rounded-2xl shadow-md p-6 mb-6 flex flex-col md:flex-row items-center gap-6">
  
  <!-- Icon -->
  <div class="text-blue-600 text-6xl md:text-7xl flex-shrink-0">
    <!-- Using heroicons SVG -->
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="black" class="h-16 w-16 md:h-20 md:w-20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
</svg>

  </div>

  <!-- Text -->
  <div class="flex-1">
    <h2 class="text-2xl md:text-3xl font-bold mb-1 text-gray-900">VulnBank - Vulnerabilities Research Platform</h2>
    <p class="text-gray-600 text-sm md:text-base">This project is an alias of project 'Metamorphosis'.</p>
  </div>

  <!-- Button -->
  <div class="text-right">
    <a href="run_init.php"
       class="inline-block bg-blue-600 text-white font-semibold px-5 py-2.5 rounded-lg
              hover:bg-blue-700 active:scale-95 transition transform shadow-md">
      RESET DB
    </a>
  </div>
</div>

<!-- Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

  <!-- Teaching Goals -->
  <div class="bg-white/80 backdrop-blur rounded-2xl shadow-md p-5 hover:shadow-lg transition h-full">
    <div class="flex items-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.5 0 2.5-1 2.5-2.5S13.5 3 12 3 9.5 4 9.5 5.5 10.5 8 12 8z M12 12v8m0 0h4m-4 0H8" />
      </svg>
      <h5 class="font-semibold text-gray-900 text-lg">Goals</h5>
    </div>
    <ul class="list-disc list-inside text-gray-700 space-y-1">
      <li>Demonstrate OWASP Top 10 vulnerabilities</li>
      <li>Show attacks safely on localhost</li>
      <li>Explain mitigation and secure coding</li>
    </ul>
  </div>

  <!-- Quick Tips -->
  <div class="bg-green-100/80 backdrop-blur rounded-2xl shadow-md p-5 hover:shadow-lg transition h-full">
    <div class="flex items-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17l-.75-4.5 6-1.5-.75 4.5-4.5 1.5z" />
      </svg>
      <h5 class="font-semibold text-gray-900 text-lg">Quick Tips</h5>
    </div>
    <p class="text-gray-600 text-sm md:text-base">
      At the beginning or in the time of everything upside down, press <b>RESET DB</b> button above to initialize database or reset it.
    </p>
  </div>

</div>


<?php require_once 'footer.php'; ?>
