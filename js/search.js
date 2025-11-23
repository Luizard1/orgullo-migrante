document.addEventListener("DOMContentLoaded", function () {
    const wrapper = document.getElementById("searchWrapper");
    const trigger = document.getElementById("searchTrigger");
    const closeBtn = document.getElementById("searchClose");
  
    trigger.addEventListener("click", () => {
      wrapper.classList.add("search-active");
    });
  
    closeBtn.addEventListener("click", () => {
      wrapper.classList.remove("search-active");
    });
  });
  