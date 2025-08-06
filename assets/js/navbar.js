document.addEventListener("DOMContentLoaded", function () {
    const navbar = document.getElementById("mainNavbar");

    window.addEventListener("scroll", function () {
        if (window.scrollY > 150) { // Scroll lebih dari 150px baru nempel & blur
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }
    });
});
