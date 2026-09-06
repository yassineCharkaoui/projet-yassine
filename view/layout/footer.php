<?php
require_once __DIR__ . '/../../controller/config.php';
requireRoutedView();
?>
    </div> <!-- end container -->
    
    <footer style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: #94a3b8; padding: 30px 0; margin-top: 60px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 14px;">
        <div style="max-width: 1400px; margin: 0 auto; padding: 0 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <strong style="color: white; font-size: 15px;">🏥 <?= APP_NAME ?></strong> — Version 2.0
            </div>
            <div>
                &copy; <?= date('Y') ?> Tous droits réservés • Architecture MVC PHP 8
            </div>
        </div>
    </footer>
</body>
</html>
