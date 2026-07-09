
import '../css/app.css';

document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.main-sidebar');
    const content = document.querySelector('.main-content');

    toggleBtn.addEventListener('click', function () {
        sidebar.classList.toggle('active');
        content.classList.toggle('active');
    });
});
