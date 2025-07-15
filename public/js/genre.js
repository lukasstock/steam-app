document.addEventListener('DOMContentLoaded', () => {
    const tags = document.querySelectorAll('.tag');

    tags.forEach(tag => {
        tag.addEventListener('click', () => {
            // Simple "pulse" animation on click
            tag.classList.add('pulse');
            setTimeout(() => tag.classList.remove('pulse'), 300);
        });
    });
});
