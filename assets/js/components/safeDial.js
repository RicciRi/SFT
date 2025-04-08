export function initSafeDial() {
    const dial = document.querySelector('.styles_dial__GKlj9');
    const inner = dial.querySelector('.styles_inner___VZ_I');
    const dialSound = document.getElementById('dialSound');

    let isDragging = false;
    let currentRotation = 0;
    let startAngle = 0;
    let rotationAccumulator = 0; // 🔧 копим с начала drag
    const DELTA_FOR_SOUND = 17;

    let lastSoundTime = 0;
    const MIN_SOUND_INTERVAL = 40; // в мс, например, 100мс между звуками

    function playDialSound() {
        const now = Date.now();
        if (now - lastSoundTime > MIN_SOUND_INTERVAL) {
            const clone = dialSound.cloneNode();
            clone.play();
            lastSoundTime = now;
        }
    }

    function getAngle(clientX, clientY) {
        const rect = dial.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        const angle = Math.atan2(clientY - centerY, clientX - centerX) * (180 / Math.PI);
        return (angle + 90) % 360;
    }

    function getCurrentRotation() {
        const rotationStyle = inner.style.getPropertyValue('--rotation') || '0deg';
        return parseFloat(rotationStyle.replace('deg', '')) || 0;
    }

    function updateRotationAndSound(angleDelta) {
        const newRotation = currentRotation + angleDelta;
        inner.style.setProperty('--rotation', `${newRotation}deg`);

        rotationAccumulator += Math.abs(angleDelta); // 🔧 аккумулируем

        // 🔧 проигрываем звук за каждые DELTA_FOR_SOUND градусов
        while (rotationAccumulator >= DELTA_FOR_SOUND) {
            playDialSound();
            rotationAccumulator -= DELTA_FOR_SOUND;
        }

        currentRotation = newRotation;
    }

    function handleMouseDown(e) {
        isDragging = true;
        currentRotation = getCurrentRotation();
        startAngle = getAngle(e.clientX, e.clientY);
        rotationAccumulator = 0; // 🔧 сброс при старте
        e.preventDefault();
        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', handleMouseUp);
    }

    function handleMouseMove(e) {
        if (!isDragging) return;
        const currentAngle = getAngle(e.clientX, e.clientY);
        let angleDelta = currentAngle - startAngle;
        if (angleDelta > 180) angleDelta -= 360;
        if (angleDelta < -180) angleDelta += 360;

        updateRotationAndSound(angleDelta);
        startAngle = currentAngle;
    }

    function handleMouseUp() {
        isDragging = false;
        // 🔧 не сбрасываем rotationAccumulator — пусть сохранится
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
    }

    function handleTouchStart(e) {
        if (e.touches.length === 1) {
            isDragging = true;
            currentRotation = getCurrentRotation();
            startAngle = getAngle(e.touches[0].clientX, e.touches[0].clientY);
            rotationAccumulator = 0; // 🔧 сброс
            e.preventDefault();
        }
    }

    function handleTouchMove(e) {
        if (!isDragging || e.touches.length !== 1) return;
        const currentAngle = getAngle(e.touches[0].clientX, e.touches[0].clientY);
        let angleDelta = currentAngle - startAngle;
        if (angleDelta > 180) angleDelta -= 360;
        if (angleDelta < -180) angleDelta += 360;

        updateRotationAndSound(angleDelta);
        startAngle = currentAngle;
        e.preventDefault();
    }

    function handleTouchEnd() {
        isDragging = false;
        // 🔧 не сбрасываем rotationAccumulator
    }

    dial.addEventListener('mousedown', handleMouseDown);
    dial.addEventListener('touchstart', handleTouchStart, { passive: false });
    dial.addEventListener('touchmove', handleTouchMove, { passive: false });
    dial.addEventListener('touchend', handleTouchEnd);
}
