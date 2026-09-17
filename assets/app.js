document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.flash').forEach((flash) => {
    window.setTimeout(() => {
      flash.classList.add('flash-hide');
      window.setTimeout(() => flash.remove(), 300);
    }, 5000);
  });
});