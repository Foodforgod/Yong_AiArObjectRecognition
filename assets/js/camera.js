class CameraManager {
    constructor(videoElement) {
        this.videoElement = videoElement;
        this.stream = null;
        this.videoTrack = null;
        this.devices = [];
        this.currentDeviceIndex = 0;
        this.torchState = false;

        this.loadSettings();
    }

    loadSettings() {
        this.resolution = localStorage.getItem('ar_camera_res') || '1080p';
    }

    getResolutionConstraints() {
        switch (this.resolution) {
            case '2k':
                return { width: { ideal: 2560 }, height: { ideal: 1440 } };
            case '720p':
                return { width: { ideal: 1280 }, height: { ideal: 720 } };
            case '480p':
                return { width: { ideal: 640 }, height: { ideal: 480 } };
            case '1080p':
            default:
                return { width: { ideal: 1920 }, height: { ideal: 1080 } };
        }
    }

    async enumerateVideoDevices() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
            return [];
        }
        const allDevices = await navigator.mediaDevices.enumerateDevices();
        this.devices = allDevices.filter(device => device.kind === 'videoinput');
        return this.devices;
    }

    async start(deviceId = null) {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Camera API is not supported by your browser.');
        }

        this.stop();

        await this.enumerateVideoDevices();

        const videoConstraints = {
            ...this.getResolutionConstraints(),
            focusMode: { ideal: 'continuous' }
        };

        if (deviceId) {
            videoConstraints.deviceId = { exact: deviceId };
        } else if (this.devices.length > 0) {
            // Default to environment facing or current selected index
            if (this.devices[this.currentDeviceIndex]) {
                videoConstraints.deviceId = { exact: this.devices[this.currentDeviceIndex].deviceId };
            } else {
                videoConstraints.facingMode = { ideal: 'environment' };
            }
        } else {
            videoConstraints.facingMode = { ideal: 'environment' };
        }

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: videoConstraints,
                audio: false
            });

            this.videoElement.srcObject = this.stream;
            await this.videoElement.play();

            const tracks = this.stream.getVideoTracks();
            if (tracks.length > 0) {
                this.videoTrack = tracks[0];
            }

            return true;
        } catch (error) {
            console.warn('Primary resolution failed, falling back to standard video...', error);
            // Fallback constraint
            this.stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            this.videoElement.srcObject = this.stream;
            await this.videoElement.play();
            this.videoTrack = this.stream.getVideoTracks()[0];
            return true;
        }
    }

    async switchCamera() {
        if (this.devices.length <= 1) {
            await this.enumerateVideoDevices();
        }
        if (this.devices.length <= 1) {
            throw new Error('Only one camera detected on this device.');
        }

        this.currentDeviceIndex = (this.currentDeviceIndex + 1) % this.devices.length;
        const targetDeviceId = this.devices[this.currentDeviceIndex].deviceId;
        await this.start(targetDeviceId);
    }

    isTorchSupported() {
        if (!this.videoTrack || !this.videoTrack.getCapabilities) return false;
        const capabilities = this.videoTrack.getCapabilities();
        return !!capabilities.torch;
    }

    async toggleTorch(enable) {
        if (!this.isTorchSupported()) {
            throw new Error('Flashlight is not supported on this camera.');
        }

        try {
            await this.videoTrack.applyConstraints({
                advanced: [{ torch: enable }]
            });
            this.torchState = enable;
            return this.torchState;
        } catch (err) {
            console.error('Failed to toggle torch', err);
            throw new Error('Could not enable flashlight.');
        }
    }

    async triggerFocus() {
        if (!this.videoTrack || !this.videoTrack.getCapabilities) return false;
        const capabilities = this.videoTrack.getCapabilities();

        if (capabilities.focusMode && capabilities.focusMode.includes('single-shot')) {
            try {
                await this.videoTrack.applyConstraints({
                    advanced: [{ focusMode: 'single-shot' }]
                });
                return true;
            } catch (e) {
                console.warn('Single-shot focus not applied', e);
            }
        }
        return false;
    }

    stop() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
            this.videoTrack = null;
        }
    }

    captureFrame(canvasElement) {
        const video = this.videoElement;
        
        // Scale down high-resolution frames before uploading to prevent API/Network lag
        const maxDim = 1280;
        let w = video.videoWidth || 1280;
        let h = video.videoHeight || 720;

        if (w > maxDim || h > maxDim) {
            if (w > h) {
                h = Math.round((h * maxDim) / w);
                w = maxDim;
            } else {
                w = Math.round((w * maxDim) / h);
                h = maxDim;
            }
        }

        canvasElement.width = w;
        canvasElement.height = h;
        const ctx = canvasElement.getContext('2d');
        ctx.drawImage(video, 0, 0, w, h);
        return canvasElement.toDataURL('image/jpeg', 0.75);
    }
}