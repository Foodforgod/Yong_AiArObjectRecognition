/**
 * Haptic Vibration Feedback Manager
 */
class HapticManager {
    constructor() {
        this.enabled = true;
        this.loadSettings();
    }

    loadSettings() {
        const saved = localStorage.getItem('ar_haptic_enabled');
        if (saved !== null) {
            this.enabled = saved === 'true';
        }
    }

    setSetting(enabled) {
        this.enabled = enabled;
        localStorage.setItem('ar_haptic_enabled', enabled ? 'true' : 'false');
    }

    isSupported() {
        return 'vibrate' in navigator;
    }

    triggerFocus() {
        if (this.enabled && this.isSupported()) {
            navigator.vibrate(25);
        }
    }

    triggerSuccess() {
        if (this.enabled && this.isSupported()) {
            navigator.vibrate([30, 50, 30]);
        }
    }

    triggerError() {
        if (this.enabled && this.isSupported()) {
            navigator.vibrate([80, 40, 80]);
        }
    }
}

window.hapticManager = new HapticManager();