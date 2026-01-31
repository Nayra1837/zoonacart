    </main>
    <footer style="background: var(--dark); color: white; margin-top: 3rem; padding: 4rem 1rem 2rem;">
        <div class="footer-inner">
            <div style="text-align: center;">
            <h3 style="font-size: 1.8rem; font-weight: 900; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 3px; color: #ec4899;"><?php echo getSetting('site_name'); ?></h3>
            <p style="color: #94a3b8; font-size: 0.9rem; max-width: 500px; margin: 0 auto 2.5rem; line-height: 1.6;">
                Redefining luxury with professional-grade cosmetics crafted for your radiant beauty.
            </p>
            
            <div style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 2.5rem; font-size: 0.85rem; font-weight: 600; flex-wrap: wrap;">
                <a href="index.php" style="color: #e2e8f0; text-decoration: none;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#e2e8f0'">Home</a>
                <a href="shop.php" style="color: #e2e8f0; text-decoration: none;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#e2e8f0'">Collection</a>
                <a href="about.php" style="color: #e2e8f0; text-decoration: none;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#e2e8f0'">About</a>
                <a href="#" onclick="event.preventDefault(); toggleChat();" style="color: #e2e8f0; text-decoration: none;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#e2e8f0'">Support</a>
                <a href="privacy.php" style="color: #e2e8f0; text-decoration: none;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#e2e8f0'">Privacy</a>
            </div>

            <div style="height: 1px; background: rgba(255,255,255,0.1); margin-bottom: 1.5rem;"></div>

            <div style="display: flex; justify-content: space-between; align-items: center; color: #64748b; font-size: 0.75rem; padding-bottom: 4rem;">
                <p>&copy; <?php echo date('Y'); ?> <?php echo getSetting('site_name'); ?> Boutique. All rights reserved.</p>
                <div style="display: flex; gap: 1.5rem; font-size: 1.1rem;">
                    <i class="fa-brands fa-instagram" style="cursor: pointer;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'"></i>
                    <i class="fa-brands fa-facebook" style="cursor: pointer;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'"></i>
                    <i class="fa-brands fa-twitter" style="cursor: pointer;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'"></i>
                </div>
            </div>
        </div>
    </footer>
    
    <?php include 'chat_widget.php'; ?>
</body>
</html>
