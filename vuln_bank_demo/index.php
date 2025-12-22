<?php
require_once 'header.php';
?>
<div class="card card-hero p-4 mb-4">
  <div class="d-flex align-items-center">
    <div class="me-4">
      <i class="bi bi-bank2" style="font-size:3.2rem;color:#2b6cb0;"></i>
    </div>
    <div>
      <h2 style="margin-bottom:0;">VulnBank — Security training demo</h2>
      <p class="small-muted mb-0">Realistic banking UI with intentional vulnerabilities for local OWASP training. Use only in a controlled environment.</p>
    </div>
    <div class="ms-auto text-end">
      <a class="btn btn-outline-primary" href="run_init.php">Initialize DB</a>
    </div>
  </div>
</div>

<div class="row gy-3">
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h5><i class="bi bi-shield-lock me-2 text-primary"></i> Teaching Goals</h5>
      <ul>
        <li>Demonstrate OWASP Top 10 vulnerabilities</li>
        <li>Show attacks safely on localhost</li>
        <li>Explain mitigation and secure coding</li>
      </ul>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h5><i class="bi bi-gear me-2 text-primary"></i> Quick Tips</h5>
      <p class="mb-0 small-muted">After you run <strong>run_init.php</strong>, delete it. Do not leave legacy vulnerable pages exposed.</p>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
