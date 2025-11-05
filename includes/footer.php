</main>

<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>About RentHub</h3>
            <p>Your trusted platform for affordable student housing solutions.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="<?php echo $BASE_URL ?? ''; ?>/public/index.php">Home</a></li>
                <li><a href="<?php echo $BASE_URL ?? ''; ?>/houses/browse.php">Browse Houses</a></li>
                <li><a href="<?php echo $BASE_URL ?? ''; ?>/public/register.php">Register</a></li>
                <li><a href="<?php echo $BASE_URL ?? ''; ?>/public/login.php">Login</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Connect With Us</h3>
            <div class="social-links">
                <a href="#" target="_blank" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> RentHub Portal. All rights reserved.</p>
    </div>
</footer>

<style>
.site-footer {
    background: #1560c1ff;
    color: white;
    padding: 40px 20px 20px;
    margin-top: 40px;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    padding-bottom: 20px;
}

.footer-section h3 {
    margin-bottom: 15px;
    font-size: 1.2rem;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 10px;
}

.footer-section a {
    color: white;
    text-decoration: none;
    transition: opacity 0.3s;
}

.footer-section a:hover {
    opacity: 0.8;
}

.social-links {
    display: flex;
    gap: 15px;
    font-size: 1.5rem;
}

.social-links a {
    color: white;
    transition: transform 0.3s;
}

.social-links a:hover {
    transform: translateY(-3px);
}

.footer-bottom {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .social-links {
        justify-content: center;
    }
}
</style>

</body>
</html>
