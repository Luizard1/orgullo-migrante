// js/cart.js
import { products } from "./products.js";

document.addEventListener("DOMContentLoaded", () => {
  const itemsDiv      = document.getElementById("cart-items");
  const totalSpan     = document.getElementById("cart-total");
  const checkoutBtn   = document.getElementById("checkout");
  const methodCode    = document.getElementById("method-code");
  const methodCard    = document.getElementById("method-card");
  const paySection    = document.getElementById("payment-method");
  const codeSection   = document.getElementById("payment-code");
  const cardSection   = document.getElementById("payment-card");
  const generatedCode = document.getElementById("generated-code");
  const confirmCode   = document.getElementById("confirm-code");
  const cardForm      = document.getElementById("card-form");

  // helper para ocultar/mostrar
  const show = el => el.classList.remove("hidden");
  const hide = el => el.classList.add("hidden");

  // 1. Cargar carrito
  const cart = JSON.parse(localStorage.getItem("cart")) || [];

  // 2. Mostrar productos y calcular total
  let total = 0;
  cart.forEach((prodId, idx) => {
    const p = products[prodId];
    if (!p) return;
    const priceNum = parseFloat(p.price.replace(/[^0-9.]/g, ""));
    total += priceNum;

    const div = document.createElement("div");
    div.className = "cart-item";
    div.innerHTML = `
      <img src="${p.images[0]}" alt="${p.title}" class="cart-thumb">
      <div class="cart-info">
        <h3>${p.title}</h3>
        <p>${p.price}</p>
      </div>
      <button class="remove-btn" data-idx="${idx}">Eliminar</button>
    `;
    itemsDiv.append(div);
  });
  totalSpan.textContent = `MXN$ ${total.toFixed(2)}`;

  // 3. Eliminar producto
  itemsDiv.addEventListener("click", e => {
    if (!e.target.matches(".remove-btn")) return;
    const idx = Number(e.target.dataset.idx);
    cart.splice(idx, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    location.reload();
  });

  // al hacer clic en Proceder a Pagar
  checkoutBtn.addEventListener("click", () => {
    if (cart.length === 0) {
      return alert("Tu carrito está vacío.");
    }
    show(paySection);     // muestra radios
    hide(codeSection);    // oculta código
    hide(cardSection);    // oculta tarjeta
    checkoutBtn.disabled = true;
  });

  // 5. Pagar con código
  methodCode.addEventListener("change", () => {
    if (!methodCode.checked) return;
    const code = "COD-" + Math.random().toString(36).substr(2, 8).toUpperCase();
    generatedCode.textContent = code;
    show(codeSection);
    hide(cardSection);
  });

  // 6. Pagar con tarjeta
  methodCard.addEventListener("change", () => {
    if (!methodCard.checked) return;
    show(cardSection);
    hide(codeSection);
  });

  // 7. Confirmar código
  confirmCode.addEventListener("click", () => {
    alert(`✅ Pago con código procesado por MXN$ ${total.toFixed(2)}. ¡Gracias!`);
    localStorage.removeItem("cart");
    location.reload();
  });

  // 8. Procesar tarjeta
  cardForm.addEventListener("submit", e => {
    e.preventDefault();
    alert(`✅ Pago con tarjeta procesado por MXN$ ${total.toFixed(2)}. ¡Gracias!`);
    localStorage.removeItem("cart");
    location.reload();
  });
});
