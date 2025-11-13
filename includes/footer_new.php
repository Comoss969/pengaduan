</div> <!-- End container -->

    <!-- Footer Toggle Button -->
    <button id="footerToggle" class="footer-toggle-btn" title="Toggle Footer">
        <svg class="footer-toggle-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6,9 12,15 18,9"></polyline>
        </svg>
    </button>

    <!-- Footer -->
    <footer id="mainFooter" class="modern-footer">
        <div class="footer-container">
            <div class="footer-content">
                <!-- Left Column: Patch Info -->
                <div class="footer-column footer-patch">
                    <div class="footer-item">
                        <span class="footer-label">Patch:</span>
                        <span class="footer-badge">1.2.5</span>
                    </div>
                    <div class="footer-item">
                        <span class="footer-desc">Update perbaikan bug & performa</span>
                    </div>
                </div>

                <!-- Right Column: Contact Info -->
                <div class="footer-column footer-contact">
                    <div class="footer-item">
                        <span class="footer-label">Admin:</span>
                        <span class="footer-value">Akira</span>
                    </div>
                    <div class="footer-item">
                        <a href="mailto:akiraexample@gmail.com" class="footer-link">akiraexample@gmail.com</a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="footer-copyright">
                <span class="footer-copyright-text">© 2025 Sistem Pengaduan Masyarakat</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Footer Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const footerToggle = document.getElementById('footerToggle');
            const mainFooter = document.getElementById('mainFooter');
            const toggleIcon = footerToggle.querySelector('.footer-toggle-icon');

            // Check localStorage for footer state
            const footerVisible = localStorage.getItem('footerVisible') !== 'false';

            if (!footerVisible) {
                mainFooter.classList.add('footer-hidden');
                toggleIcon.style.transform = 'rotate(180deg)';
            }

            // Toggle footer on button click
            footerToggle.addEventListener('click', function() {
                const isHidden = mainFooter.classList.contains('footer-hidden');

                if (isHidden) {
                    mainFooter.classList.remove('footer-hidden');
                    toggleIcon.style.transform = 'rotate(0deg)';
                    localStorage.setItem('footerVisible', 'true');
                } else {
                    mainFooter.classList.add('footer-hidden');
                    toggleIcon.style.transform = 'rotate(180deg)';
                    localStorage.setItem('footerVisible', 'false');
                }
            });
        });
    </script>
</body>
</html>
