<?php
require_once 'header.php';
?>

<!-- Hero Section -->
<div class="bg-white/90 rounded-2xl shadow-md p-6 mb-6 flex flex-col md:flex-row items-center gap-6">

  <div class="text-blue-600 text-6xl md:text-7xl flex-shrink-0">
    <!-- icon -->
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
         stroke-width="1.5" stroke="black"
         class="h-16 w-16 md:h-20 md:w-20">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
    </svg>
  </div>

  <div class="flex-1">
    <h2 class="text-2xl md:text-3xl font-bold mb-1 text-gray-900">
      SecureBank - Vulnerabilities Research Platform
    </h2>
    <p class="text-gray-600 text-sm md:text-base">
      This project is an alias of project 'Metamorphosis'.
    </p>
  </div>

  <!-- RESET DB (POST + CSRF) -->
  <div class="text-right">
    <form method="POST" action="run_init.php" onsubmit="return confirm('Reset database?');">
      <?php echo csrf_field(); ?>

      <button type="submit"
        class="inline-block bg-blue-600 text-white font-semibold px-5 py-2.5 rounded-lg
               hover:bg-blue-700 active:scale-95 transition transform shadow-md">
        RESET DB
      </button>
    </form>
  </div>
</div>

<!-- Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

  <div class="bg-white/80 backdrop-blur rounded-2xl shadow-md p-5 hover:shadow-lg transition h-full">
    <div class="flex items-center mb-3">
      <h5 class="font-semibold text-gray-900 text-lg">Teaching Goals</h5>
    </div>
    <ul class="list-disc list-inside text-gray-700 space-y-1">
      <li>Demonstrate OWASP Top 10 vulnerabilities</li>
      <li>Show attacks safely on localhost</li>
      <li>Explain mitigation and secure coding</li>
    </ul>
  </div>

  <div class="bg-green-100/80 backdrop-blur rounded-2xl shadow-md p-5 hover:shadow-lg transition h-full">
    <div class="flex items-center mb-3">
      <h5 class="font-semibold text-gray-900 text-lg">Quick Tips</h5>
    </div>
    <p class="text-gray-600 text-sm md:text-base">
      Use the <b>RESET DB</b> button to initialize or restore the database.
      This action is protected by CSRF and requires POST request.
    </p>
  </div>

</div>

<?php require_once 'footer.php'; ?>