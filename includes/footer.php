<?php if (!empty($app_layout_open)): ?>
        </main>
    </div>
<?php endif; ?>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4><?= e(app_config('short_name')) ?></h4>
                <p><?= e(app_config('university_name')) ?></p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= url('/') ?>">Home</a></li>
                    <li><a href="<?= url('login.php') ?>">Sign In</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="mailto:support@smis.test">Contact Support</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e(app_config('university_name')) ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
