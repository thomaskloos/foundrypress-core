document.addEventListener('click', async function (event) {
    const button = event.target.closest('.js-copy-prompt');
    if (!button) return;

    const targetId = button.getAttribute('data-copy-target');
    if (!targetId) return;

    const target = document.getElementById(targetId);
    if (!target) return;

    try {
        await navigator.clipboard.writeText(target.value);
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        setTimeout(() => {
            button.textContent = originalText;
        }, 1500);
    } catch (error) {
        button.textContent = 'Copy Failed';
        setTimeout(() => {
            button.textContent = 'Copy Prompt';
        }, 1500);
    }
});