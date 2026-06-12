/**
 * Custom Heart Cursor
 * Adds a cute heart cursor with trailing effect to non-admin pages
 */

// Only enable cursor on non-admin pages
if (!window.location.pathname.includes('admin')) {
    let cursorX = 0;
    let cursorY = 0;
    let trailX = 0;
    let trailY = 0;
    let isMouseDown = false;
    let cursorScale = 1;

    // Create cursor elements
    const cursor = document.createElement('div');
    cursor.id = 'custom-cursor';
    cursor.textContent = '🩷';
    document.body.appendChild(cursor);

    const trail = document.createElement('div');
    trail.id = 'cursor-trail';
    document.body.appendChild(trail);

    // Update cursor position on mouse move
    document.addEventListener('mousemove', (e) => {
        cursorX = e.clientX;
        cursorY = e.clientY;

        // Update cursor position instantly
        cursor.style.left = cursorX + 'px';
        cursor.style.top = cursorY + 'px';
    });

    // Update trail position with lerp on animation frame
    function updateTrail() {
        // Linear interpolation (lerp) toward cursor position
        const lerpFactor = 0.15;
        trailX += (cursorX - trailX) * lerpFactor;
        trailY += (cursorY - trailY) * lerpFactor;

        trail.style.left = trailX + 'px';
        trail.style.top = trailY + 'px';

        requestAnimationFrame(updateTrail);
    }
    updateTrail();

    // Click pulse: scale to 0.7 on mousedown
    document.addEventListener('mousedown', () => {
        isMouseDown = true;
        cursorScale = 0.7;
        cursor.style.transform = `translate(-50%, -50%) scale(${cursorScale})`;
    });

    // Scale back to 1 on mouseup
    document.addEventListener('mouseup', () => {
        isMouseDown = false;
        cursorScale = 1;
        cursor.style.transform = `translate(-50%, -50%) scale(${cursorScale})`;
    });

    // Hide cursor when leaving window
    document.addEventListener('mouseleave', () => {
        cursor.style.opacity = '0';
        trail.style.opacity = '0';
    });

    // Show cursor when re-entering window
    document.addEventListener('mouseenter', () => {
        cursor.style.opacity = '1';
        trail.style.opacity = '1';
    });

    // Scale up on hover over buttons and links
    const interactiveElements = document.querySelectorAll('.btn, a[href]');
    interactiveElements.forEach((el) => {
        el.addEventListener('mouseover', () => {
            cursorScale = 1.4;
            cursor.style.transform = `translate(-50%, -50%) scale(${cursorScale})`;
        });

        el.addEventListener('mouseout', () => {
            if (!isMouseDown) {
                cursorScale = 1;
                cursor.style.transform = `translate(-50%, -50%) scale(${cursorScale})`;
            }
        });
    });
}
