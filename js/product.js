// js/product.js
import { products } from "./products.js";

// 1. Leer el id de la URL: ?id=producto1
const params  = new URLSearchParams(window.location.search);
const id      = params.get("id");
const product = products[id] || { title: "No encontrado", price: "", description: "", images: [] };

// 2. Rellenar los campos de texto
document.getElementById("product-title").textContent       = product.title;
document.getElementById("product-price").textContent       = product.price;
document.getElementById("product-description").textContent = product.description;

// 3. Inyectar cada imagen como un <div class="swiper-slide">
const wrapper = document.querySelector(".swiper-wrapper");
product.images.forEach(src => {
  const slide = document.createElement("div");
  slide.className = "swiper-slide";
  slide.innerHTML = `<img src="${src}" alt="${product.title}">`;
  wrapper.append(slide);
});

// 4. Inicializar Swiper (igual que antes)
new Swiper('.mySwiper', {
  slidesPerView:   2.5,
  spaceBetween:    20,
  centeredSlides:  true,
  loop:            true,
  navigation: {
    prevEl: '.swiper-button-prev',
    nextEl: '.swiper-button-next',
  },
});

// 5. Añadir al carrito
const addBtn = document.getElementById("add-to-cart");
// Asignar el id del producto al botón
addBtn.dataset.id = id;
addBtn.addEventListener("click", () => {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  cart.push(id);
  localStorage.setItem("cart", JSON.stringify(cart));
  alert("✅ Producto añadido al carrito");
});
