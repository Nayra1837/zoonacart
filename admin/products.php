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
    $hsn_code = $_POST['hsn_code'] ?? '3304';
    $tax_percent = $_POST['tax_percent'] ?? 18;
    $id = $_POST['id'] ?? null;
    $image = $_POST['current_image'] ?? '';

    // Handle Image Upload (Multiple)
    $primaryImageSet = false;
    
    // If updating, check if we already have a primary image
    if ($id && !$image) {
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetchColumn();
        if ($existing) $image = $existing;
    }

    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        $uploadedImages = [];
        
        foreach($files['name'] as $key => $name) {
            if ($files['error'][$key] === 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $imgName = time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($files['tmp_name'][$key], '../assets/img/' . $imgName)) {
                    $uploadedImages[] = $imgName;
                    
                    // Set first new image as primary if none exists or if explicitly replacing
                    if (!$image) {
                        $image = $imgName; 
                    }
                }
            }
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, stock=?, category=?, description=?, image=?, hsn_code=?, tax_percent=? WHERE id=?");
        $stmt->execute([$name, $price, $stock, $category, $description, $image, $hsn_code, $tax_percent, $id]);
        $productId = $id;
        $success = "Product updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, category, description, image, hsn_code, tax_percent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $stock, $category, $description, $image, $hsn_code, $tax_percent]);
        $productId = $pdo->lastInsertId();
        $success = "Product added successfully!";
    }

    // Insert into product_images table
    if (!empty($uploadedImages)) {
        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
        foreach($uploadedImages as $index => $img) {
            $isPrimary = ($img === $image) ? 1 : 0;
            $stmt->execute([$productId, $img, $isPrimary]);
        }
    }
}

$stmt = $pdo->query("
    SELECT p.*, GROUP_CONCAT(pi.image_path) as all_images 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id 
    GROUP BY p.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();
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
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">HSN Code</label>
                        <input type="text" name="hsn_code" id="form_hsn" required placeholder="3304" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Tax % (GST)</label>
                        <input type="number" step="0.01" name="tax_percent" id="form_tax" required placeholder="18" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none;">
                    </div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Description</label>
                    <textarea name="description" id="form_description" rows="4" style="width: 100%; padding: 1rem; border-radius: 0; border: 1px solid #e2e8f0; outline: none; resize: none;"></textarea>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Product Images</label>
                    <div id="imageInputsContainer" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <input type="file" name="images[]" class="form-control" style="border: 1px solid #e2e8f0; padding: 0.5rem; width: 100%; border-radius: 0;" onchange="previewImages()">
                    </div>
                    <button type="button" onclick="addImageInput()" class="btn" style="margin-top: 0.5rem; background: #f1f5f9; color: var(--primary); font-size: 0.8rem; font-weight: 600; width: 100%; border: 1px dashed var(--primary);">
                        <i class="fa-solid fa-plus"></i> Add Another Image
                    </button>
                    <div id="imagePreview" style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;"></div>
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
function addImageInput() {
    const container = document.getElementById('imageInputsContainer');
    const input = document.createElement('input');
    input.type = 'file';
    input.name = 'images[]';
    input.className = 'form-control';
    input.style = 'border: 1px solid #e2e8f0; padding: 0.5rem; width: 100%; border-radius: 0; margin-top: 0.5rem;';
    input.onchange = previewImages;
    container.appendChild(input);
}

function previewImages() {
    const container = document.getElementById('imagePreview');
    const inputs = document.querySelectorAll('input[name="images[]"]');
    
    // Do not clear container here if you want to keep existing previews from edit mode
    // But for adding new files, we usually want live preview of what's selected
    // Let's clear and re-render existing + new
    
    // Actually, preserving the "Existing Images" visual and appending "New Previews" is tricky if we mix them in one container.
    // For simplicity, let's keep one container. 
    // We should not clear if we want to show existing DB images.
    // Strategy: existing images are loaded by editProduct.
    // We will append newly selected previews. 
    
    const existingImgs = Array.from(container.querySelectorAll('img[data-existing="true"]'));
    container.innerHTML = '';
    
    // Restore existing
    existingImgs.forEach(img => container.appendChild(img));
    
    inputs.forEach(input => {
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '60px';
                    img.style.height = '60px';
                    img.style.borderRadius = '5px';
                    img.style.objectFit = 'cover';
                    img.style.border = '1px solid #e2e8f0';
                    container.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        }
    });
}

function editProduct(product) {
    document.getElementById('productForm').classList.remove('hidden');
    document.getElementById('form_id').value = product.id;
    document.getElementById('form_name').value = product.name;
    document.getElementById('form_price').value = product.price;
    document.getElementById('form_stock').value = product.stock;
    document.getElementById('form_category').value = product.category;
    document.getElementById('form_description').value = product.description;
    document.getElementById('form_hsn').value = product.hsn_code || '3304';
    document.getElementById('form_tax').value = product.tax_percent || '18';
    
    // Reset file inputs
    const inputContainer = document.getElementById('imageInputsContainer');
    inputContainer.innerHTML = '<input type="file" name="images[]" class="form-control" style="border: 1px solid #e2e8f0; padding: 0.5rem; width: 100%; border-radius: 0;" onchange="previewImages()">';
    
    // Load existing images
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = '';
    
    if (product.all_images) {
        const uniqueImages = [...new Set(product.all_images.split(','))];
        uniqueImages.forEach(img => {
            if (!img) return;
            const imgEl = document.createElement('img');
            imgEl.src = "../assets/img/" + img;
            imgEl.style.width = '60px';
            imgEl.style.height = '60px';
            imgEl.style.borderRadius = '5px';
            imgEl.style.objectFit = 'cover';
            imgEl.style.border = '1px solid #e2e8f0';
            imgEl.setAttribute('data-existing', 'true'); // Mark as existing
            previewContainer.appendChild(imgEl);
        });
    } else if (product.image) {
        const imgEl = document.createElement('img');
        imgEl.src = "../assets/img/" + product.image;
        imgEl.style.width = '60px';
        imgEl.style.height = '60px';
        imgEl.style.borderRadius = '5px';
        imgEl.style.objectFit = 'cover';
        imgEl.style.border = '1px solid #e2e8f0';
        imgEl.setAttribute('data-existing', 'true');
        previewContainer.appendChild(imgEl);
    }
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<style>
.hidden { display: none !important; }
@media (max-width: 768px) {
    #productForm { padding: 1.5rem !important; }
    #productForm form { grid-template-columns: 1fr !important; gap: 1.5rem !important; }
    .container { padding: 1.5rem 4% !important; }
}
</style>

<?php include '../includes/footer.php'; ?>
