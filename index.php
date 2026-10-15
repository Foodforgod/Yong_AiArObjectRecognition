<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>AI + AR Smart Object Recognition System</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <div class="app-container">
        <!-- Live Camera Viewfinder Stream -->
        <div class="camera-feed-container">
            <video id="camera-video" autoplay playsinline muted></video>
            <canvas id="snapshot-canvas"></canvas>
            
            <!-- Tap to focus reticle -->
            <div id="focus-reticle" class="focus-reticle"></div>

            <!-- AR Bounding Box Overlay -->
            <div id="ar-bounding-box" class="ar-bounding-box"></div>
        </div>

        <!-- UI Glassmorphism Layer -->
        <div class="ui-overlay">
            <header class="app-header">
                <div class="app-title">
                    <i class="fas fa-cube text-primary"></i> AI + AR Scanner
                </div>
                <div class="quick-controls">
                    <button id="torch-btn" class="icon-btn" aria-label="Toggle Flashlight" title="Toggle Torch">
                        <i class="fas fa-bolt"></i>
                    </button>
                    <button id="switch-cam-btn" class="icon-btn" aria-label="Switch Camera" title="Switch Camera">
                        <i class="fas fa-camera-rotate"></i>
                    </button>
                    <a href="settings.php" class="icon-btn" aria-label="Settings" title="Settings">
                        <i class="fas fa-gear"></i>
                    </a>
                </div>
            </header>

            <div class="scanner-viewfinder">
                <div class="viewfinder-box"></div>
            </div>

            <div class="scanner-controls">
                <div id="status-msg" class="status-message" aria-live="polite">Initializing camera...</div>
                <button id="scan-btn" class="scan-btn" aria-label="Scan Object">
                    <div class="scan-btn-inner">
                        <i class="fas fa-camera"></i>
                    </div>
                </button>
            </div>

            <!-- AR Product Information Expandable Card -->
            <div id="ar-info-card" class="ar-info-card">
                <div class="ar-info-header">
                    <div>
                        <div class="ar-object-label">Object</div>
                        <div class="ar-product-title">Product Name</div>
                    </div>
                    <span class="badge-pill">Tap for details</span>
                </div>
                <div class="ar-details-body">
                    <p><strong>Manufacturer:</strong> <span class="mfg-text">-</span></p>
                    <p><strong>Specification:</strong> <span class="spec-text">-</span></p>
                    <p><strong>Description:</strong> <span class="desc-text">-</span></p>
                    <p><strong>Confidence:</strong> <span class="conf-text">-</span></p>
                    
                    <div class="voice-actions">
                        <button id="speak-btn" class="btn btn-sm btn-outline-info"><i class="fas fa-volume-high me-1"></i> Speak</button>
                        <button id="stop-voice-btn" class="btn btn-sm btn-outline-secondary"><i class="fas fa-stop me-1"></i> Stop</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/voice.js"></script>
    <script src="assets/js/haptic.js"></script>
    <script src="assets/js/camera.js"></script>
    <script src="assets/js/ar-overlay.js"></script>
    <script src="assets/js/scanner.js"></script>
</body>
</html>