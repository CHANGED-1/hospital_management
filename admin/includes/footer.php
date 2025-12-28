<?php
/**
 * Common Footer for Admin Module
 */
?>
<style>
    .footer {
        background: white;
        padding: 20px 30px;
        margin-top: 40px;
        border-top: 1px solid var(--border-color);
        text-align: center;
        color: var(--text-muted);
    }

    .footer p {
        margin: 5px 0;
        font-size: 14px;
    }

    .footer-links {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .footer-links a {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 13px;
        transition: color 0.3s;
    }

    .footer-links a:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }
</style>

<footer class="footer">
    <p>&copy; <?php echo date('Y'); ?> <?php echo HOSPITAL_NAME; ?>. All rights reserved.</p>
    <p><?php echo SITE_NAME; ?> v<?php echo APP_VERSION; ?></p>
    <div class="footer-links">
        <a href="<?php echo BASE_URL; ?>admin/help.php">Help Center</a>
        <a href="<?php echo BASE_URL; ?>admin/documentation.php">Documentation</a>
        <a href="<?php echo BASE_URL; ?>admin/privacy.php">Privacy Policy</a>
        <a href="<?php echo BASE_URL; ?>admin/terms.php">Terms of Service</a>
    </div>
</footer>

<!-- Common JavaScript -->
<script>
    // Global search functionality
    document.getElementById('globalSearch')?.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = this.value;
            // Implement global search logic
            console.log('Searching for:', searchTerm);
        }
    });

    // Confirmation for delete actions
    function confirmDelete(message = 'Are you sure you want to delete this item?') {
        return confirm(message);
    }

    // Show loading spinner
    function showLoading() {
        if (!document.getElementById('loadingOverlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                     background: rgba(0,0,0,0.5); display: flex; align-items: center; 
                     justify-content: center; z-index: 9999;">
                    <div style="background: white; padding: 30px; border-radius: 10px; 
                         text-align: center;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 40px; 
                           color: var(--primary-color);"></i>
                        <p style="margin-top: 15px; color: var(--text-color);">Loading...</p>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
    }

    // Hide loading spinner
    function hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
        }
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type}`;
        toast.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
        `;
        
        const icon = type === 'success' ? 'check-circle' : 
                     type === 'error' || type === 'danger' ? 'exclamation-circle' : 
                     'info-circle';
        
        toast.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Format currency
    function formatCurrency(amount) {
        return '<?php echo CURRENCY_SYMBOL; ?> ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // Format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }

    // Debounce function for search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Print page
    function printPage() {
        window.print();
    }

    // Export to CSV
    function exportToCSV(filename, rows) {
        const csvContent = rows.map(row => row.join(',')).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        window.URL.revokeObjectURL(url);
    }
</script>

</body>
</html>