-- Add New Cosmetic Products
USE zoonacosmetics;

INSERT INTO products (name, price, description, image, stock, category) VALUES 
('Classic Red Matte Lipstick', 25.00, 'A rich, velvety matte lipstick that provides long-lasting color and hydration. Perfect for a bold look.', 'matte_lipstick.png', 50, 'Lips'),
('Luxe Liquid Eyeliner', 18.50, 'Precision liquid eyeliner with a fine tip for sharp, dramatic lines. Water-resistant and smudge-proof.', 'liquid_eyeliner.png', 40, 'Eyes'),
('Volumax Mascara', 22.00, 'Intense volumizing mascara that lifts and separates lashes for a dramatic, falsie effect.', 'volumizing_mascara.png', 60, 'Eyes'),
('Nude & Shimmer Eyeshadow Palette', 45.00, 'A versatile 12-shade palette featuring a mix of buttery mattes and blinding shimmers for day-to-night looks.', 'eyeshadow_palette.png', 30, 'Eyes'),
('Radiant Blush Compact', 28.00, 'Silky powder blush in a universally flattering pink shade. Buildable coverage for a natural flush.', 'blush_compact.png', 35, 'Face');
