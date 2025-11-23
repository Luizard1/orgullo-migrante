import { products } from "./products.js";

const filename   = window.location.pathname.split("/").pop().replace(".html","").toLowerCase();
const slugToCat  = { business:"Business", casual:"Casual", ceremonia:"Ceremonia", slipon:"Slip-On", monkstrap:"Monk Strap", wingtip:"Wingtip"};
const category   = slugToCat[filename];

const container = document.getElementById("productos-grid");
Object.entries(products)
  .filter(([_, p]) => p.category === category)
  .forEach(([id,p]) => {
    const thumb = Array.isArray(p.images) && p.images.length
                ? p.images[0]
                : "ruta-por-defecto.jpg";

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
