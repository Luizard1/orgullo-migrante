document.addEventListener("DOMContentLoaded", function () {
    const contactBtn = document.getElementById("contactButton");
    const overlay = document.getElementById("contactOverlay");
    const closeBtn = document.getElementById("closeContact");
  
    contactBtn.addEventListener("click", () => {
      overlay.classList.add("show");
    });
  
    closeBtn.addEventListener("click", () => {
      overlay.classList.remove("show");
    });
  
    window.addEventListener("click", (e) => {
      if (e.target === overlay) {
        overlay.classList.remove("show");
      }
    });
  });
  