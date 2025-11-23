document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;
  
    window.addEventListener("scroll", () => {
      if (window.scrollY > 10) {
        body.classList.add("hero-scrolled");
      } else {
        body.classList.remove("hero-scrolled");
      }
    });
  });
  