export function initSafeDial() {

    const dial = document.querySelector('.styles_dial__GKlj9');
    const inner = dial.querySelector('.styles_inner___VZ_I');

    let isDragging = false;
    let currentRotation = 0;
    let startAngle = 0;

    function getAngle(clientX, clientY) {
        const dialRect = dial.getBoundingClientRect();

        const centerX = dialRect.left + dialRect.width / 2;
        const centerY = dialRect.top + dialRect.height / 2;

        const angle = Math.atan2(clientY - centerY, clientX - centerX) * (180 / Math.PI);

        return (angle + 90) % 360;
    }

    function getCurrentRotation() {
        const rotationStyle = inner.style.getPropertyValue('--rotation') || '0deg';
        return parseFloat(rotationStyle.replace('deg', '')) || 0;
    }

    function handleMouseDown(e) {
        isDragging = true;

        currentRotation = getCurrentRotation();

        startAngle = getAngle(e.clientX, e.clientY);

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

        const newRotation = currentRotation + angleDelta;

        inner.style.setProperty('--rotation', `${newRotation}deg`);

        currentRotation = newRotation;
        startAngle = currentAngle;
    }

    function handleMouseUp() {
        isDragging = false;

        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
    }

    function handleTouchStart(e) {
        if (e.touches.length === 1) {
            isDragging = true;

            currentRotation = getCurrentRotation();

            startAngle = getAngle(e.touches[0].clientX, e.touches[0].clientY);

            e.preventDefault();
        }
    }

    function handleTouchMove(e) {
        if (!isDragging || e.touches.length !== 1) return;

        const currentAngle = getAngle(e.touches[0].clientX, e.touches[0].clientY);

        let angleDelta = currentAngle - startAngle;

        if (angleDelta > 180) angleDelta -= 360;
        if (angleDelta < -180) angleDelta += 360;

        const newRotation = currentRotation + angleDelta;

        inner.style.setProperty('--rotation', `${newRotation}deg`);

        currentRotation = newRotation;
        startAngle = currentAngle;

        e.preventDefault();
    }

    function handleTouchEnd() {
        isDragging = false;
    }

    dial.addEventListener('mousedown', handleMouseDown);
    dial.addEventListener('touchstart', handleTouchStart, {passive: false});
    dial.addEventListener('touchmove', handleTouchMove, {passive: false});
    dial.addEventListener('touchend', handleTouchEnd);
}