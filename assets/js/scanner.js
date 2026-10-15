document.addEventListener('DOMContentLoaded', async () => {
    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('snapshot-canvas');
    const scanBtn = document.getElementById('scan-btn');
    const statusMsg = document.getElementById('status-msg');
    
    const torchBtn = document.getElementById('torch-btn');
    const switchCamBtn = document.getElementById('switch-cam-btn');
    const focusReticle = document.getElementById('focus-reticle');

    const speakBtn = document.getElementById('speak-btn');
    const stopVoiceBtn = document.getElementById('stop-voice-btn');

    const arBox = document.getElementById('ar-bounding-box');
    const arCard = document.getElementById('ar-info-card');

    const camera = new CameraManager(video);
    const arOverlay = new AROverlayManager(document.querySelector('.camera-feed-container'), arBox, arCard);

    let currentScanData = null;

    // Camera Init
    try {
        statusMsg.textContent = 'Requesting camera access...';
        await camera.start();
        statusMsg.textContent = 'Point camera at an object';
    } catch (err) {
        statusMsg.textContent = err.message;
        scanBtn.disabled = true;
        return;
    }

    // Tap to focus trigger
    video.addEventListener('click', async (e) => {
        const rect = video.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        focusReticle.style.left = `${x}px`;
        focusReticle.style.top = `${y}px`;
        focusReticle.classList.add('active');

        window.hapticManager.triggerFocus();
        await camera.triggerFocus();

        setTimeout(() => {
            focusReticle.classList.remove('active');
        }, 1200);
    });

    // Torch Toggle
    if (torchBtn) {
        torchBtn.addEventListener('click', async () => {
            try {
                const newState = !camera.torchState;
                await camera.toggleTorch(newState);
                torchBtn.classList.toggle('active', newState);
                window.hapticManager.triggerFocus();
            } catch (e) {
                alert(e.message);
            }
        });
    }

    // Camera Switch Toggle
    if (switchCamBtn) {
        switchCamBtn.addEventListener('click', async () => {
            try {
                statusMsg.textContent = 'Switching camera...';
                await camera.switchCamera();
                statusMsg.textContent = 'Point camera at an object';
                window.hapticManager.triggerFocus();
            } catch (e) {
                alert(e.message);
                statusMsg.textContent = 'Point camera at an object';
            }
        });
    }

    // Scan Button
    let isScanning = false;

    scanBtn.addEventListener('click', async () => {
        if (isScanning) return;
        isScanning = true;
        arOverlay.hide();
        window.voiceManager.stop();

        statusMsg.textContent = 'Capturing image...';
        scanBtn.querySelector('i').className = 'fas fa-spinner fa-spin';

        try {
            const imageData = camera.captureFrame(canvas);
            statusMsg.textContent = 'AI identifying object...';

            const response = await fetch('api/recognize.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ image: imageData })
            });

            const result = await response.json();

            if (!result.ok) {
                throw new Error(result.error || 'Recognition failed.');
            }

            currentScanData = result.data;
            statusMsg.textContent = 'Object identified';
            arOverlay.show(result.data);

            window.hapticManager.triggerSuccess();
            window.voiceManager.speakResult(result.data);

            scanBtn.querySelector('i').className = 'fas fa-redo';
        } catch (error) {
            console.error(error);
            statusMsg.textContent = error.message || 'Unable to identify object.';
            scanBtn.querySelector('i').className = 'fas fa-camera';
            window.hapticManager.triggerError();
        } finally {
            isScanning = false;
        }
    });

    // Speak / Stop Voice Action Buttons
    if (speakBtn) {
        speakBtn.addEventListener('click', () => {
            if (currentScanData) {
                window.voiceManager.speakResult(currentScanData);
            }
        });
    }

    if (stopVoiceBtn) {
        stopVoiceBtn.addEventListener('click', () => {
            window.voiceManager.stop();
        });
    }
});