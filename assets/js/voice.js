/**
 * Voice Narration Manager using Web Speech API (SpeechSynthesis)
 */
class VoiceManager {
    constructor() {
        this.synth = window.speechSynthesis || null;
        this.voices = [];
        this.settings = {
            enabled: true,
            autoSpeak: true,
            voiceURI: '',
            lang: 'en-US',
            volume: 1.0
        };

        this.loadSettings();
        this.initVoices();
    }

    loadSettings() {
        const saved = localStorage.getItem('ar_voice_settings');
        if (saved) {
            try {
                this.settings = { ...this.settings, ...JSON.parse(saved) };
            } catch (e) {
                console.error('Failed to parse voice settings', e);
            }
        }
    }

    saveSettings() {
        localStorage.setItem('ar_voice_settings', JSON.stringify(this.settings));
    }

    initVoices() {
        if (!this.synth) return;

        const populate = () => {
            this.voices = this.synth.getVoices();
        };

        populate();
        if (typeof speechSynthesis !== 'undefined' && speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = populate;
        }
    }

    getAvailableVoices() {
        if (!this.synth) return [];
        if (this.voices.length === 0) {
            this.voices = this.synth.getVoices();
        }
        return this.voices;
    }

    getVoiceByURI(uri) {
        return this.getAvailableVoices().find(v => v.voiceURI === uri) || null;
    }

    stop() {
        if (this.synth && this.synth.speaking) {
            this.synth.cancel();
        }
    }

    speakText(text) {
        if (!this.synth || !this.settings.enabled || !text) return;

        // Cancel previous speech to prevent overlap
        this.stop();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.volume = parseFloat(this.settings.volume);

        const selectedVoice = this.getVoiceByURI(this.settings.voiceURI);
        if (selectedVoice) {
            utterance.voice = selectedVoice;
            utterance.lang = selectedVoice.lang;
        } else if (this.settings.lang) {
            utterance.lang = this.settings.lang;
        }

        this.synth.speak(utterance);
    }

    speakResult(data) {
        if (!this.settings.autoSpeak) return;

        // Generate natural spoken summary sentence
        let summary = `This is a ${data.productName}.`;
        if (data.manufacturer && data.manufacturer.toLowerCase() !== 'unknown') {
            summary = `This is a ${data.productName} by ${data.manufacturer}.`;
        }
        if (data.description && data.description.length > 5) {
            summary += ` ${data.description}`;
        }

        this.speakText(summary);
    }
}

// Global instance
window.voiceManager = new VoiceManager();