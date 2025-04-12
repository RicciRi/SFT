export function initDownload() {
    const lines = document.querySelectorAll(".transfer-details .type-line");
    let lineIndex = 0;

    function typeLine() {
        if (lineIndex >= lines.length) return;

        const line = lines[lineIndex];
        const target = line.querySelector(".type-target");
        const fullText = line.dataset.text;
        let charIndex = 0;

        const cursor = document.createElement("span");
        cursor.classList.add("cursor");
        target.appendChild(cursor);

        const typingInterval = setInterval(() => {
            if (charIndex < fullText.length) {
                // Вставляем символ перед курсором
                const span = document.createElement("span");
                span.textContent = fullText[charIndex];
                target.insertBefore(span, cursor);
                charIndex++;
            } else {
                clearInterval(typingInterval);
                cursor.remove(); // Удаляем курсор после окончания строки
                lineIndex++;
                setTimeout(typeLine, 300);
            }
        }, 30);
    }

    typeLine();
}
