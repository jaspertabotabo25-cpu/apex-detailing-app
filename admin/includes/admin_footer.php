<?php
// admin/includes/admin_footer.php
?>
        </div> <!-- End of .content -->
    </div> <!-- End of .dashboard -->
    
    <div id="toast" class="toast"></div>

    <script>
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.textContent = message;
                toast.className = 'toast show ' + type;
                
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
        }
    </script>
    <?= $extraScripts ?? '' ?>
</body>
</html>
