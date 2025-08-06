document.addEventListener("DOMContentLoaded", function () {
    const banner = document.querySelector("#banner");
    if (banner) {
        console.log("Carousel initialized successfully");
    } else {
        console.error("Carousel element not found!");
    }
});
