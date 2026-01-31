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
    const isHome = window.location.pathname.endsWith("/") ||
      window.location.pathname.includes("index.php") ||
      window.location.pathname.includes("index.html");

    let url = "api/main.php?action=get_products";
    if (isHome) {
      url += "&sort=random";
    }

    const res = await fetch(url);
    if (!res.ok) throw new Error("Failed to fetch products");

    let products;
    try {
      products = await res.json();
    } catch (parseErr) {
      const text = await res.text();
      console.error('Invalid JSON response:', text);
      throw parseErr;
    }

    const grid = document.getElementById("productGrid");
    if (!grid) return;

    if (isHome) {
      products = products.slice(0, 12);
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
        (p) => {
          // Prepare logic for multiple images
          const imageList = p.images && p.images.length > 0 ? p.images : [p.image];
          const hasMultiple = imageList.length > 1;

          let dotsHtml = '';
          if (hasMultiple) {
            dotsHtml = `<div class="image-dots" style="position: absolute; bottom: 10px; left: 0; right: 0; display: flex; justify-content: center; gap: 5px; opacity: 0; transition: 0.3s;">
                ${imageList.slice(0, 4).map((img, idx) => `
                    <span onmouseover="switchImage(this, '${img}')" 
                          style="width: 8px; height: 8px; background: ${idx === 0 ? 'var(--primary)' : '#cbd5e1'}; border-radius: 50%; cursor: pointer; display: block; box-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                    </span>
                `).join('')}
              </div>`;
          }

          return `
        <div class="card animate" onclick="window.location.href='product.php?id=${p.id}'" style="cursor: pointer;" onmouseenter="this.querySelector('.image-dots').style.opacity='1'" onmouseleave="this.querySelector('.image-dots').style.opacity='0'">
            <div class="card-img" style="position: relative;">
                <img src="assets/img/${imageList[0]}" alt="${p.name}" class="product-main-img" style="transition: opacity 0.3s; width: 100%;">
                ${dotsHtml}
            </div>
            <div style="padding: 0 0.5rem;">
                <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">${p.category}</span>
                <h3 style="margin: 0.5rem 0; font-size: 1.2rem;">${p.name}</h3>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
                    <span style="font-weight: 800; color: var(--primary); font-size: 1.1rem;">₹${parseFloat(p.price).toFixed(2)}</span>
                    <div style="flex-shrink: 0;">
                        <button onclick="event.stopPropagation(); addToCart(${p.id}, this)" class="btn-add">
                            <i class="fa-solid fa-plus"></i> Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `})
      .join("");
  } catch (err) {
    console.error("Load error:", err);
    const grid = document.getElementById("productGrid");
    if (grid) grid.innerHTML = `<div style="text-align:center; padding: 2rem; color: red;">
        <p>Error loading products.</p>
        <small>${err.message}</small>
    </div>`;
  }
}

function updateCartBadge(count) {
  const desktopBadge = document.getElementById("cartCount");
  const mobileBadge = document.getElementById("mobileCartCount");

  if (desktopBadge) desktopBadge.innerText = count;
  if (mobileBadge) {
    mobileBadge.innerText = count;
    mobileBadge.style.display = count > 0 ? "flex" : "none";
  }
}

async function addToCart(id, btn) {
  try {
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    }

    const formData = new FormData();
    formData.append("id", id);
    const res = await fetch("api/main.php?action=add_to_cart", {
      method: "POST",
      body: formData,
    });

    if (!res.ok) throw new Error("Server error");

    const result = await res.json();
    if (result.success) {
      updateCartBadge(result.count);
      if (btn) {
        btn.style.background = "#22c55e";
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
        setTimeout(() => {
          btn.style.background = "";
          btn.innerHTML = '<i class="fa-solid fa-plus"></i> Add';
          btn.disabled = false;
        }, 1500);
      }
    }
  } catch (err) {
    console.error("Add to cart error:", err);
    alert("Could not add to bag. Please try again.");
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-plus"></i> Add';
    }
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
    updateCartBadge(result.count);
    qtyEl.innerText = 1; // Reset to 1
    alert(`${qty} item(s) added to bag!`);
  }
}

async function loadCartCount() {
  const res = await fetch("api/main.php?action=get_cart");
  const data = await res.json();
  updateCartBadge(data.count || 0);
}

// Cart Page Logic
async function loadCartDetails() {
  const container = document.getElementById("cartItems");
  const loading = document.getElementById("cartLoading");
  const summary = document.getElementById("summary");
  const content = document.getElementById("cartContent");

  try {
    const res = await fetch("api/main.php?action=get_cart");
    const data = await res.json();

    if (loading) loading.style.display = 'none';

    if (data.items.length === 0) {
      if (content) content.style.display = 'none';

      const cartContainer = document.getElementById("cartContainer");
      if (cartContainer) {
        cartContainer.innerHTML = `
          <div class="text-center" style="padding: 100px 0; background: white; border-radius: 20px;">
              <i class="fa-solid fa-bag-shopping" style="font-size: 5rem; color: #e2e8f0; margin-bottom: 2rem;"></i>
              <h2 style="font-size: 2rem; color: #1e293b;">Your bag is empty</h2>
              <p style="color: #64748b; margin-top: 1rem; margin-bottom: 2rem;">Looks like you haven't added anything to your cart yet.</p>
              <a href="shop.php" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 700; border-radius: 8px;">Start Shopping</a>
          </div>`;
      }
      return;
    }

    // Items exist
    if (content) {
      // We use a CSS class for layout, but JS ensures it's visible if it was hidden
      content.style.display = '';
    }
    if (summary) summary.style.display = 'block';

    container.innerHTML = data.items
      .map(
        (item) => `
          <div id="cart-item-${item.id}" class="glass cart-item-animate" style="padding: 1.5rem; border-radius: 0; display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem; background: white; border-bottom: 1px solid #f1f5f9; transition: all 0.4s ease;">
              <img src="assets/img/${item.image}" style="width: 80px; height: 80px; border-radius: 8px; object-fit: cover; border: 1px solid #f1f5f9;">
              <div style="flex-grow: 1;">
                  <h3 style="margin-bottom: 0.3rem; font-size: 1.1rem; color: #1e293b;">${item.name}</h3>
                  <p style="color: #64748b; font-size: 0.9rem;">₹${parseFloat(item.price).toFixed(2)}</p>
                  <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.8rem;">
                      <button onclick="updateQty(${item.id}, ${item.qty - 1})" class="btn" style="padding: 2px 10px; background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b;">-</button>
                      <span style="font-weight: 800; min-width: 25px; text-align: center;">${item.qty}</span>
                      <button onclick="updateQty(${item.id}, ${item.qty + 1})" class="btn" style="padding: 2px 10px; background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b;">+</button>
                  </div>
              </div>
              <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                  <button onclick="removeCartItem(${item.id})" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1.2rem; transition: 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                      <i class="fa-solid fa-xmark"></i>
                  </button>
                  <p style="font-weight: 800; font-size: 1.1rem; color: var(--dark);">₹${parseFloat(item.subtotal).toFixed(2)}</p>
              </div>
          </div>
      `,
      )
      .join("");

    container.innerHTML += `
        <div style="margin-top: 2rem;">
            <a href="shop.php" style="text-decoration: none; color: #64748b; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrow-left-long" style="margin-right: 5px;"></i> Back to Shopping
            </a>
        </div>
    `;

    const cartTotalEl = document.getElementById("cartTotal");
    if (cartTotalEl) {
      cartTotalEl.innerText = "₹" + parseFloat(data.total).toFixed(2);
    }

  } catch (err) {
    if (loading) loading.innerHTML = `<p style="color: red;">Failed to load cart. Please refresh.</p>`;
    console.error(err);
  }
}

async function removeCartItem(id) {
  const itemEl = document.getElementById(`cart-item-${id}`);
  if (!itemEl) return;

  // Optimistic UI Update: Instant feedback
  itemEl.style.opacity = "0.5";
  itemEl.style.transform = "translateX(20px)";
  itemEl.style.pointerEvents = "none";

  const formData = new FormData();
  formData.append("id", id);
  formData.append("qty", 0);

  try {
    await fetch("api/main.php?action=update_cart", {
      method: "POST",
      body: formData,
    });

    // Slighly delay for smooth animation before full reload
    itemEl.style.height = "0";
    itemEl.style.margin = "0";
    itemEl.style.padding = "0";
    itemEl.style.overflow = "hidden";
    itemEl.style.opacity = "0";

    setTimeout(() => {
      loadCartDetails();
      loadCartCount();
    }, 300);

  } catch (err) {
    console.error("Remove error:", err);
    itemEl.style.opacity = "1";
    itemEl.style.transform = "none";
    itemEl.style.pointerEvents = "auto";
    alert("Could not remove item. Please try again.");
  }
}

async function updateQty(id, qty) {
  if (qty <= 0) return removeCartItem(id);

  const itemEl = document.getElementById(`cart-item-${id}`);
  if (itemEl) itemEl.style.opacity = "0.7";

  const formData = new FormData();
  formData.append("id", id);
  formData.append("qty", qty);

  try {
    await fetch("api/main.php?action=update_cart", {
      method: "POST",
      body: formData,
    });
    loadCartDetails();
    loadCartCount();
  } catch (err) {
    if (itemEl) itemEl.style.opacity = "1";
    console.error(err);
  }
}

function switchImage(dot, imgPath) {
  const card = dot.closest('.card');
  const imgInfo = card.querySelector('.product-main-img');

  // Fade out
  imgInfo.style.opacity = '0.7';

  // Switch source
  setTimeout(() => {
    imgInfo.src = 'assets/img/' + imgPath;
    imgInfo.style.opacity = '1';
  }, 100);

  // Update dots
  const dots = card.querySelectorAll('.image-dots span');
  dots.forEach(d => d.style.background = '#cbd5e1');
  dot.style.background = 'var(--primary)';
}
