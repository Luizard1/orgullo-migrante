document.addEventListener("DOMContentLoaded", function () {
  const menuButton = document.getElementById("menuButton");
  const dropdownOverlay = document.getElementById("dropdownOverlay");
  const closeMenuBtn = document.getElementById("closeMenu");

  menuButton.addEventListener("click", function () {
    dropdownOverlay.classList.add("show");
  });

  closeMenuBtn.addEventListener("click", function () {
    dropdownOverlay.classList.remove("show");
  });

  window.addEventListener("click", function (e) {
    if (
      !dropdownOverlay.contains(e.target) &&
      !menuButton.contains(e.target)
    ) {
      dropdownOverlay.classList.remove("show");
    }
  });
});
