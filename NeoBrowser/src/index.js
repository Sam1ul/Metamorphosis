// renderer side logic

const minimizeBtn = document.getElementById('minimize');
const maximizeBtn = document.getElementById('maximize');
const closeBtn = document.getElementById('close');

minimizeBtn?.addEventListener('click', () => {
    window.windowControls.minimize();
});

maximizeBtn?.addEventListener('click', () => {
    window.windowControls.maximize();
});

closeBtn?.addEventListener('click', () => {
    window.windowControls.close();
});

console.log("renderer loaded");
