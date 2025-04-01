export function initRegister() {
    const lines = document.querySelectorAll('.vertical-line');

    window.addEventListener('scroll', () => {
        const viewportCenter = window.innerHeight / 2;

        lines.forEach(line => {
            const fill = line.querySelector('.vertical-line__fill');
            const rect = line.getBoundingClientRect();

            let distance = viewportCenter - rect.top;

            if (distance < 0) distance = 0;
            if (distance > rect.height) distance = rect.height;

            fill.style.height = distance + 'px';
        });
    });
}


