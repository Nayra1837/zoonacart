document.addEventListener("DOMContentLoaded", () => {
  checkAuth();
  loadCartCount();

  if (document.getElementById("productGrid")) loadProducts();
  if (document.getElementById("cartItems")) loadCartDetails();
});

async function checkAuth() {
  const res = await fetch("api/main.php?action=get_auth");
  const user = await res.json();
  const authBox = document.getElementById("authActions");
  const adminLink = document.getElementById("adminLink");

  // Show/Hide Admin Panel Link
  if (adminLink) {
    adminLink.style.display = (user.isLoggedIn && user.role === "admin") ? "block" : "none";
  }

  // Update auth actions only if the placeholder exists (some pages render server-side links)
  if (authBox) {
    if (user.isLoggedIn) {
      authBox.innerHTML = `
            <a href="profile.php" class="btn btn-dark" style="padding: 0.5rem 1.5rem;">Profile</a>
            <a href="logout.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Logout</a>
        `;
    } else {
      authBox.innerHTML = `
            <a href="login.php" style="font-weight: 500; text-decoration: none; color: inherit;">Login</a>
            <a href="register.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Join</a>
        `;
    }
  }

  // Active Link Styling for static pages
  const currentPage = window.location.pathname.split("/").pop();
  document.querySelectorAll(".nav-links a").forEach(link => {
    const href = link.getAttribute("href");
    if (href === currentPage || (currentPage === "" && href === "index.php")) {
      link.classList.add("active");
      link.style.color = "var(--primary)";
    }
  });
}

async function loadProducts() {
  try {
    const res = await fetch("api/main.php?action=get_products");
    if (!res.ok) throw new Error("Failed to fetch products");
    let products;
    try {
      products = await res.json();
    } catch (parseErr) {
      const text = await res.text();
      console.error('Invalid JSON response from products API:', text);
      throw parseErr;
    }
    const grid = document.getElementById("productGrid");
    if (!grid) return;

    // Show only 8 products if on home page
    const isHome = window.location.pathname.endsWith("/") || 
                   window.location.pathname.includes("index.php") || 
                   window.location.pathname.includes("index.html");
    
    if (isHome) {
      products = products.slice(0, 8);
    }

    if (products.length === 0) {
      grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 5rem; color: #64748b;">
        <i class="fa-solid fa-sparkles" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
        <p>Our luxury collection is arriving soon.</p>
      </div>`;
      return;
    }

  grid.innerHTML = products
    .map(
      (p) => `
        <div class="card animate">
            <div class="card-img">
                <img src="assets/img/${p.image}" alt="${p.name}">
            </div>
            <div style="padding: 0 0.5rem;">
                <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">${p.category}</span>
                <h3 style="margin: 0.5rem 0; font-size: 1.2rem;">${p.name}</h3>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                    <span style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">₹${parseFloat(p.price).toFixed(2)}</span>
                    <div style="display: flex; align-items: center; gap: 0;">
                        <button onclick="changeQty(${p.id}, -1)" style="width: 32px; height: 32px; background: #f1f5f9; border: 1px solid #e2e8f0; cursor: pointer; font-weight: 800; font-size: 1rem;">−</button>
                        <span id="qty-${p.id}" style="font-weight: 700; min-width: 32px; height: 32px; text-align: center; line-height: 32px; background: white; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">1</span>
                        <button onclick="changeQty(${p.id}, 1)" style="width: 32px; height: 32px; background: #f1f5f9; border: 1px solid #e2e8f0; cursor: pointer; font-weight: 800; font-size: 1rem;">+</button>
                        <button onclick="addToCartWithQty(${p.id})" class="btn btn-dark" style="padding: 0 1rem; height: 32px; border-radius: 0; margin-left: 0.5rem; font-size: 0.75rem;">
                            <i class="fa-solid fa-bag-shopping"></i> Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `,
    )
    .join("");
  } catch (err) {
    console.error("Load error:", err);
    const grid = document.getElementById("productGrid");
    if (grid) grid.innerHTML = `<p style="text-align:center; padding: 2rem;">Error loading products.</p>`;
  }
}

async function addToCart(id) {
  const formData = new FormData();
  formData.append("id", id);
  const res = await fetch("api/main.php?action=add_to_cart", {
    method: "POST",
    body: formData,
  });
  const result = await res.json();
  if (result.success) {
    document.getElementById("cartCount").innerText = result.count;
    alert("Product added to bag!");
  }
}

function changeQty(id, delta) {
  const qtyEl = document.getElementById(`qty-${id}`);
  let qty = parseInt(qtyEl.innerText) + delta;
  if (qty < 1) qty = 1;
  if (qty > 99) qty = 99;
  qtyEl.innerText = qty;
}

async function addToCartWithQty(id) {
  const qtyEl = document.getElementById(`qty-${id}`);
  const qty = parseInt(qtyEl.innerText);
  const formData = new FormData();
  formData.append("id", id);
  formData.append("qty", qty);
  const res = await fetch("api/main.php?action=add_to_cart", {
    method: "POST",
    body: formData,
  });
  const result = await res.json();
  if (result.success) {
    document.getElementById("cartCount").innerText = result.count;
    qtyEl.innerText = 1; // Reset to 1
    alert(`${qty} item(s) added to bag!`);
  }
}

async function loadCartCount() {
  const res = await fetch("api/main.php?action=get_cart");
  const data = await res.json();
  document.getElementById("cartCount").innerText = data.count || 0;
}

// Cart Page Logic
async function loadCartDetails() {
  const res = await fetch("api/main.php?action=get_cart");
  const data = await res.json();
  const container = document.getElementById("cartItems");

  if (data.items.length === 0) {
    container.innerHTML = `<div class="text-center" style="padding: 100px 0;">
            <i class="fa-solid fa-bag-shopping" style="font-size: 5rem; color: #e2e8f0; margin-bottom: 2rem;"></i>
            <h2>Your bag is empty</h2>
            <br><a href="index.php" class="btn btn-primary">Go Shopping</a>
        </div>`;
    return;
  }

  container.innerHTML = data.items
    .map(
      (item) => `
        <div class="glass" style="padding: 2rem; border-radius: 0; display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem;">
            <img src="assets/img/${item.image}" style="width: 100px; height: 100px; border-radius: 0; object-fit: cover;">
            <div style="flex-grow: 1;">
                <h3 style="margin-bottom: 0.5rem;">${item.name}</h3>
                <p style="color: #64748b;">₹${item.price} each</p>
                <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                    <button onclick="updateQty(${item.id}, ${item.qty - 1})" class="btn" style="padding: 5px 12px; background: #eee;">-</button>
                    <span style="font-weight: 800;">${item.qty}</span>
                    <button onclick="updateQty(${item.id}, ${item.qty + 1})" class="btn" style="padding: 5px 12px; background: #eee;">+</button>
                </div>
            </div>
            <div style="text-align: right;">
                <p style="font-weight: 800; font-size: 1.2rem; color: var(--primary);">₹${parseFloat(item.subtotal).toFixed(2)}</p>
            </div>
        </div>
    `,
    )
    .join("");

  document.getElementById("cartTotal").innerText =
    "₹" + parseFloat(data.total).toFixed(2);
}

async function updateQty(id, qty) {
  const formData = new FormData();
  formData.append("id", id);
  formData.append("qty", qty);
  await fetch("api/main.php?action=update_cart", {
    method: "POST",
    body: formData,
  });
  loadCartDetails();
  loadCartCount();
}
