document.addEventListener('DOMContentLoaded', async () => {
    const form = document.getElementById('settings-form');
    const statusAlert = document.getElementById('status-alert');
    const qrContainer = document.getElementById('qr-code-box');

    // UI elements for voice and camera
    const voiceSelect = document.getElementById('voice_select');
    const voiceVolume = document.getElementById('voice_volume');
    const volumeValue = document.getElementById('volume_val');
    const autoSpeakCheck = document.getElementById('auto_speak');
    const voiceEnableCheck = document.getElementById('voice_enable');
    const testVoiceBtn = document.getElementById('test-voice-btn');

    const hapticCheck = document.getElementById('haptic_enable');
    const cameraResSelect = document.getElementById('camera_resolution');

    // QR Code
    const currentUrl = window.location.href.replace('settings.php', 'index.php');
    qrContainer.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(currentUrl)}" alt="Scan QR Code" class="img-fluid rounded" />`;

    // Populate System API Settings
    try {
        const res = await fetch('api/settings.php');
        const json = await res.json();
        if (json.ok) {
            const d = json.data;
            document.getElementById('ai_provider').value = d.ai_provider;
            document.getElementById('gemini_model').value = d.gemini_model;
            document.getElementById('agnes_model').value = d.agnes_model;
            document.getElementById('request_timeout').value = d.request_timeout;

            if (d.gemini_configured) document.getElementById('gemini_status').textContent = 'Key saved';
            if (d.agnes_configured) document.getElementById('agnes_status').textContent = 'Key saved';
        }
    } catch (e) {
        console.error('Failed to load system settings', e);
    }

    // Populate Voice Selection List (Filtering Malaysian, Japanese, English, etc.)
    const populateVoiceList = () => {
        if (!voiceSelect) return;
        const voices = window.voiceManager.getAvailableVoices();
        voiceSelect.innerHTML = '';

        if (voices.length === 0) {
            voiceSelect.innerHTML = '<option value="">Default System Voice</option>';
            return;
        }

        voices.forEach(voice => {
            const option = document.createElement('option');
            option.value = voice.voiceURI;
            
            let flag = '🌐';
            if (voice.lang.includes('MY')) flag = '🇲🇾';
            else if (voice.lang.includes('JP') || voice.lang.includes('ja')) flag = '🇯🇵';
            else if (voice.lang.includes('US')) flag = '🇺🇸';
            else if (voice.lang.includes('GB') || voice.lang.includes('UK')) flag = '🇬🇧';

            option.textContent = `${flag} ${voice.name} (${voice.lang})`;
            if (voice.voiceURI === window.voiceManager.settings.voiceURI) {
                option.selected = true;
            }
            voiceSelect.appendChild(option);
        });
    };

    populateVoiceList();
    if (typeof speechSynthesis !== 'undefined' && speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = populateVoiceList;
    }

    // Load saved LocalStorage Voice/Camera Settings
    if (voiceVolume) {
        voiceVolume.value = window.voiceManager.settings.volume * 100;
        volumeValue.textContent = `${Math.round(window.voiceManager.settings.volume * 100)}%`;
        voiceVolume.addEventListener('input', (e) => {
            volumeValue.textContent = `${e.target.value}%`;
            window.voiceManager.settings.volume = parseFloat(e.target.value) / 100;
        });
    }

    if (autoSpeakCheck) autoSpeakCheck.checked = window.voiceManager.settings.autoSpeak;
    if (voiceEnableCheck) voiceEnableCheck.checked = window.voiceManager.settings.enabled;
    if (hapticCheck) hapticCheck.checked = window.hapticManager.enabled;
    if (cameraResSelect) cameraResSelect.value = localStorage.getItem('ar_camera_res') || '1080p';

    // Test Voice Trigger
    if (testVoiceBtn) {
        testVoiceBtn.addEventListener('click', () => {
            if (voiceSelect) window.voiceManager.settings.voiceURI = voiceSelect.value;
            window.voiceManager.speakText("This is a voice test for the AI Object Recognition System.");
        });
    }

    // Save Form Handler
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        statusAlert.className = 'alert alert-info d-block';
        statusAlert.textContent = 'Saving configuration...';

        // Save local voice/camera settings
        if (voiceSelect) window.voiceManager.settings.voiceURI = voiceSelect.value;
        if (autoSpeakCheck) window.voiceManager.settings.autoSpeak = autoSpeakCheck.checked;
        if (voiceEnableCheck) window.voiceManager.settings.enabled = voiceEnableCheck.checked;
        window.voiceManager.saveSettings();

        if (hapticCheck) window.hapticManager.setSetting(hapticCheck.checked);
        if (cameraResSelect) localStorage.setItem('ar_camera_res', cameraResSelect.value);

        // Save backend server settings
        const payload = {
            ai_provider: document.getElementById('ai_provider').value,
            gemini_model: document.getElementById('gemini_model').value,
            agnes_model: document.getElementById('agnes_model').value,
            request_timeout: parseInt(document.getElementById('request_timeout').value),
            gemini_api_key: document.getElementById('gemini_api_key').value,
            agnes_api_key: document.getElementById('agnes_api_key').value
        };

        try {
            const res = await fetch('api/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();

            if (json.ok) {
                statusAlert.className = 'alert alert-success d-block';
                statusAlert.textContent = json.message;
                setTimeout(() => window.location.reload(), 1200);
            } else {
                throw new Error(json.error);
            }
        } catch (err) {
            statusAlert.className = 'alert alert-danger d-block';
            statusAlert.textContent = err.message || 'Failed to save settings.';
        }
    });
});