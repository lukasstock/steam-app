document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.bar').forEach(bar => {
        const percent = bar.getAttribute('data-percent');
        bar.style.width = percent + '%';
    });
});
