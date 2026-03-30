const popup = document.getElementById("popup2");
const closePopupBtn = document.getElementById("closePopupBtn");

closePopupBtn.addEventListener("click", function() {
    popup.style.display = "none";
});

window.addEventListener("click", function(event) {
    if (event.target === popup) {
        popup.style.display = "none";
    }
});
