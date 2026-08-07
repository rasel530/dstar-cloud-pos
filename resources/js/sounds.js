const POS_SOUNDS = {
    addItem: () => playTone(800, 0.08),
    removeItem: () => playTone(400, 0.06),
    paymentComplete: () => playTone(1000, 0.12),
    error: () => playTone(200, 0.2),
    click: () => playTone(600, 0.04),
};

function playTone(frequency, duration) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = ctx.createOscillator();
        const gainNode = ctx.createGain();
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(frequency, ctx.currentTime);
        gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);
        oscillator.start(ctx.currentTime);
        oscillator.stop(ctx.currentTime + duration);
    } catch (e) {
        // Audio not supported
    }
}

export default POS_SOUNDS;
