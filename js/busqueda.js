// js/busqueda.js
import { products } from "./products.js";

// 1. Leer valor de búsqueda
const params = new URLSearchParams(window.location.search);
const q      = (params.get("q") || "").trim().toLowerCase();

// 2. Mostrar la consulta en el header
document.getElementById("query-text").textContent = q;

// 3. Filtrar productos por título o descripción
const matches = Object.entries(products).filter(([, p]) => {
  return (
    p.title.toLowerCase().includes(q) ||
    (p.description || "").toLowerCase().includes(q)
  );
});

// 4. Inyectar resultados
const container = document.getElementById("search-results");

if (matches.length === 0) {
  container.innerHTML = `<p>No se encontraron productos para “${q}”.</p>`;
} else {
  matches.forEach(([id, p]) => {
    // Primera imagen como miniatura
    const thumb = Array.isArray(p.images) && p.images.length
                ? p.images[0]
                : p.img || "";

    container.innerHTML += `
      <div class="card-producto">
        <img src="${thumb}" alt="${p.title}">
        <h2>${p.title}</h2>
        <p class="precio">${p.price}</p>
        <button>
          <a href="product.html?id=${id}">Ver detalles</a>
        </button>
      </div>
    `;
  });
}
