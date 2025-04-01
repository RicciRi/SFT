document.addEventListener('turbo:load', () => {
    if (document.body.dataset.page === 'app_home') {
        console.log('🏠 Home page JS работает!');
    }
});