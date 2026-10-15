<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AI + AR Object Recognition</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { background-color: #0b0f19; color: #f8fafc; font-family: 'Inter', sans-serif; }
        .card-custom { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.12); backdrop-filter: blur(16px); }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 680px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0 text-white"><i class="fas fa-sliders text-primary me-2"></i> System Configuration</h2>
            <a href="index.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Scanner</a>
        </div>

        <div id="status-alert" class="alert d-none" role="alert"></div>

        <form id="settings-form">
            <!-- Voice Narration Settings Section -->
            <div class="card card-custom shadow-lg rounded-4 p-4 mb-4">
                <h5 class="h6 text-primary mb-3"><i class="fas fa-volume-high me-2"></i> Voice Narration Settings</h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch pt-2">
                            <input class="form-check-input" type="checkbox" id="voice_enable" checked>
                            <label class="form-check-label text-white" for="voice_enable">Enable Voice Narration</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch pt-2">
                            <input class="form-check-input" type="checkbox" id="auto_speak" checked>
                            <label class="form-check-label text-white" for="auto_speak">Auto Speak on Scan</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="voice_select" class="form-label">Voice & Country / Accent</label>
                    <select class="form-select" id="voice_select">
                        <option value="">Loading system voices...</option>
                    </select>
                    <small class="text-muted">Includes support for Malaysian, Japanese, US, UK, and native system voices.</small>
                </div>

                <div class="mb-3">
                    <label for="voice_volume" class="form-label">Voice Volume (<span id="volume_val">100%</span>)</label>
                    <input type="range" class="form-range" id="voice_volume" min="0" max="100" value="100">
                </div>

                <button type="button" id="test-voice-btn" class="btn btn-sm btn-outline-info"><i class="fas fa-play me-1"></i> Test Voice</button>
            </div>

            <!-- Hardware Camera & Haptic Settings Section -->
            <div class="card card-custom shadow-lg rounded-4 p-4 mb-4">
                <h5 class="h6 text-primary mb-3"><i class="fas fa-camera me-2"></i> Camera & Mobile Controls</h5>
                
                <div class="mb-3">
                    <label for="camera_resolution" class="form-label">Camera Target Resolution</label>
                    <select class="form-select" id="camera_resolution">
                        <option value="1080p">Full HD (1080p) - Recommended</option>
                        <option value="720p">HD (720p)</option>
                        <option value="2k">High Res (2K / 1440p)</option>
                        <option value="480p">Standard (480p)</option>
                    </select>
                </div>

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="haptic_enable" checked>
                    <label class="form-check-label text-white" for="haptic_enable">Haptic Vibration Feedback</label>
                </div>
            </div>

            <!-- Backend AI Model Settings Section -->
            <div class="card card-custom shadow-lg rounded-4 p-4 mb-4">
                <h5 class="h6 text-primary mb-3"><i class="fas fa-brain me-2"></i> AI Provider & API Keys</h5>

                <div class="mb-3">
                    <label for="ai_provider" class="form-label">AI Provider Strategy</label>
                    <select class="form-select" id="ai_provider" name="ai_provider">
                        <option value="auto">Auto (Try Gemini, fallback to Agnes)</option>
                        <option value="gemini">Gemini Only</option>
                        <option value="agnes">Agnes Only</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="gemini_api_key" class="form-label">Gemini API Key (<span id="gemini_status" class="text-info">Not configured</span>)</label>
                    <input type="password" class="form-control" id="gemini_api_key" name="gemini_api_key" placeholder="Leave blank to keep existing key">
                </div>

                <div class="mb-3">
                    <label for="gemini_model" class="form-label">Gemini Model</label>
                    <input type="text" class="form-control" id="gemini_model" name="gemini_model" value="gemini-3.8-flash">
                </div>

                <div class="mb-3">
                    <label for="agnes_api_key" class="form-label">Agnes API Key (<span id="agnes_status" class="text-info">Not configured</span>)</label>
                    <input type="password" class="form-control" id="agnes_api_key" name="agnes_api_key" placeholder="Leave blank to keep existing key">
                </div>

                <div class="mb-3">
                    <label for="agnes_model" class="form-label">Agnes Model</label>
                    <input type="text" class="form-control" id="agnes_model" name="agnes_model" value="agnes-vision-standard">
                </div>

                <div class="mb-4">
                    <label for="request_timeout" class="form-label">Request Timeout (seconds)</label>
                    <input type="number" class="form-control" id="request_timeout" name="request_timeout" min="10" max="120" value="30">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">Save All Settings</button>
            </div>
        </form>

        <div class="card card-custom shadow-lg rounded-4 p-4 text-center">
            <h5 class="h6 mb-3 text-white">Open Scanner on Smartphone</h5>
            <div id="qr-code-box" class="d-flex justify-content-center mb-3"></div>
            <p class="text-secondary small mb-0">Scan this QR code using your phone camera to test live object recognition.</p>
        </div>
    </div>

    <script src="assets/js/voice.js"></script>
    <script src="assets/js/haptic.js"></script>
    <script src="assets/js/settings.js"></script>
</body>
</html>