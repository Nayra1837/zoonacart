<?php
require_once '../includes/functions.php';
if (!isAdmin()) redirect('login.php');

$error = '';
$success = '';

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Product deleted successfully!";
}

// Handle Add/Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $id = $_POST['id'] ?? null;
    $image = $_POST['current_image'] ?? '';

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imgName = time() . '_' . $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], '../assets/img/' . $imgName)) {
            $image = $imgName;
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, stock=?, category=?, description=?, image=? WHERE id=?");
        $stmt->execute([$name, $price, $stock, $category, $description, $image, $id]);
        $success = "Product updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, category, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $stock, $category, $description, $image]);
        $success = "Product added successfully!";
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
include '../includes/header.php';
?>

<div class="container" style="padding: 2.5rem 5%;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Product Management</h1>
        <p style="color: #64748b;">Effortlessly manage your luxury boutique catalog.</p>
    </div>

    <?php include 'admin_nav.php'; ?>

    <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
        <button onclick="document.getElementById('productForm').classList.toggle('hidden')" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-plus"></i> New Product
        </button>
    </div>

    <?php if($success): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 1rem 2rem; border-radius: 0; margin-bottom: 2rem; font-weight: 600; display: flex; align-items: center; gap: 1rem; border: 1px solid #a7f3d0;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Product Form -->
    <div id="productForm" class="hidden glass animate" style="padding: 3rem; border-radius: 0; margin-bottom: 4rem; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(244, 63, 94, 0.05); border-radius: 50%; blur: 50px;"></div>
        <h2 style="margin-bottom: 2rem; position: relative;">Product Details</h2>
        <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; position: relative;">
            <input type="hidden" name="id" id="form_id">
            <input type="hidden" name="current_image" id="form_current_image">
            
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Product Name</label>
                    <input type="text" name="name" id="form_name" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Price (₹)</label>
                        <input type="number" step="0.01" name="price" id="form_price" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Stock Units</label>
                        <input type="number" name="stock" id="form_stock" required style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Category</label>
                    <select name="category" id="form_category" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; background: white;">
                        <option>Makeup</option>
                        <option>Skincare</option>
                        <option>Fragrance</option>
                        <option>Haircare</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Description</label>
                    <textarea name="description" id="form_description" rows="4" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; resize: none;"></textarea>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Feature Image</label>
                    <div style="position: relative; border: 2px dashed #e2e8f0; border-radius: 0; padding: 1rem; text-align: center; cursor: pointer; transition: 0.3s;" onmouseenter="this.style.borderColor='var(--primary)'" onmouseleave="this.style.borderColor='#e2e8f0'">
                        <input type="file" name="image" id="imageInput" style="position: absolute; inset: 0; opacity: 0; cursor: pointer;" onchange="previewImage(this)">
                        <div id="imagePreview" style="margin-bottom: 0.5rem;">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 1.5rem; color: #94a3b8;"></i>
                        </div>
                        <p style="font-size: 0.75rem; color: #64748b;">Drop image or click to browse</p>
                    </div>
                </div>
            </div>

            <div style="grid-column: span 2; display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-dark" style="padding: 1rem 3rem;">Save Changes</button>
                <button type="button" onclick="document.getElementById('productForm').classList.add('hidden')" class="btn" style="background: #f1f5f9; color: #64748b;">Dismiss</button>
            </div>
        </form>
    </div>

    <!-- Product Grid -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
        <?php foreach($products as $p): ?>
            <div class="glass" style="padding: 1.5rem; border-radius: 0; display: flex; align-items: center; gap: 2rem; transition: 0.3s;" onmouseenter="this.style.transform='translateX(10px)'" onmouseleave="this.style.transform='translateX(0)'">
                <img src="../assets/img/<?php echo $p['image']; ?>" style="width: 80px; height: 80px; border-radius: 20px; object-fit: cover; background: #f8fafc;">
                <div style="flex-grow: 1;">
                    <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;"><?php echo $p['category']; ?></span>
                    <h3 style="font-size: 1.2rem; margin: 0.2rem 0;"><?php echo $p['name']; ?></h3>
                    <div style="display: flex; gap: 2rem; align-items: center; margin-top: 0.5rem;">
                        <span style="font-weight: 800; color: var(--primary);"><?php echo formatPrice($p['price']); ?></span>
                        <span style="font-size: 0.9rem; color: <?php echo $p['stock'] < 10 ? '#ef4444' : '#10b981'; ?>; font-weight: 600;">
                            <i class="fa-solid fa-box"></i> <?php echo $p['stock']; ?> in stock
                        </span>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="btn" style="padding: 0.8rem; background: #f1f5f9; color: #64748b; border-radius: 0;" title="Edit Product">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <a href="?delete=<?php echo $p['id']; ?>" onclick="return confirm('Permanently remove this product?')" class="btn" style="padding: 0.8rem; background: #fff1f2; color: #e11d48; border-radius: 0;" title="Delete Product">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = `<img src="${e.target.result}" style="max-height: 100px; max-width: 100%; border-radius: 0;">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function editProduct(product) {
    document.getElementById('productForm').classList.remove('hidden');
    document.getElementById('form_id').value = product.id;
    document.getElementById('form_name').value = product.name;
    document.getElementById('form_price').value = product.price;
    document.getElementById('form_stock').value = product.stock;
    document.getElementById('form_category').value = product.category;
    document.getElementById('form_description').value = product.description;
    document.getElementById('form_current_image').value = product.image;
    
    if (product.image) {
        document.getElementById('imagePreview').innerHTML = `<img src="../assets/img/${product.image}" style="max-height: 100px; max-width: 100%; border-radius: 0;">`;
    } else {
        document.getElementById('imagePreview').innerHTML = `<i class="fa-solid fa-cloud-arrow-up" style="font-size: 1.5rem; color: #94a3b8;"></i>`;
    }
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<style>
.hidden { display: none !important; }
</style>

<?php include '../includes/footer.php'; ?>
