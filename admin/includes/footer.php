<?php
/**
 * Admin Footer Template
 * The Stitch House - Common Footer for All Admin Pages
 */
?>
            </div> <!-- End admin-content -->
        </main> <!-- End admin-main -->
    </div> <!-- End admin-wrapper -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Common Admin JavaScript -->
    <script>
        $(document).ready(function() {
            // Mobile sidebar toggle functionality
            $('.sidebar-toggle').on('click', function() {
                $('.admin-sidebar').toggleClass('show');
                
                // Add overlay for mobile
                if ($('.admin-sidebar').hasClass('show')) {
                    $('body').append('<div class="sidebar-overlay show"></div>');
                } else {
                    $('.sidebar-overlay').remove();
                }
            });
            
            // Close sidebar when clicking overlay
            $(document).on('click', '.sidebar-overlay', function() {
                $('.admin-sidebar').removeClass('show');
                $(this).remove();
            });
            
            // Close sidebar when clicking outside on mobile
            $(document).on('click', function(e) {
                if ($(window).width() <= 992) {
                    if (!$(e.target).closest('.admin-sidebar, .sidebar-toggle').length) {
                        $('.admin-sidebar').removeClass('show');
                        $('.sidebar-overlay').remove();
                    }
                }
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
            
            // Layout fix for admin pages
            function fixAdminLayout() {
                // Ensure admin-wrapper exists and has proper structure
                if ($('.admin-wrapper').length) {
                    // Check if sidebar exists
                    if (!$('.admin-sidebar').length) {
                        console.warn('Admin sidebar not found - layout may be incorrect');
                    }
                    
                    // Check if main content exists
                    if (!$('.admin-main').length && !$('main.admin-content').length) {
                        console.warn('Admin main content not found - layout may be incorrect');
                    }
                    
                    // Fix any content that might be outside the proper structure
                    $('.admin-wrapper > .container, .admin-wrapper > .row, .admin-wrapper > .card').each(function() {
                        if (!$(this).closest('.admin-main, main.admin-content').length) {
                            $(this).wrap('<main class="admin-main"><div class="admin-content"></div></main>');
                        }
                    });
                    
                    // Ensure proper spacing for both layout types
                    $('.admin-main, main.admin-content').css({
                        'margin-left': '280px',
                        'width': 'calc(100% - 280px)',
                        'min-height': '100vh',
                        'background': 'rgba(255, 255, 255, 0.95)',
                        'backdrop-filter': 'blur(10px)',
                        'position': 'relative'
                    });
                    
                    $('.admin-sidebar').css({
                        'position': 'fixed',
                        'top': '0',
                        'left': '0',
                        'width': '280px',
                        'height': '100vh',
                        'z-index': '1000'
                    });
                    
                    // Fix mobile layout
                    if ($(window).width() <= 992) {
                        $('.admin-main, main.admin-content').css({
                            'margin-left': '0',
                            'width': '100%'
                        });
                    }
                }
            }
            
            // Run layout fix on page load
            fixAdminLayout();
            
            // Run layout fix on window resize
            $(window).on('resize', function() {
                if ($(window).width() > 992) {
                    fixAdminLayout();
                }
            });
        });
    </script>
    
    <?php if (isset($additionalJS)): ?>
        <?= $additionalJS ?>
    <?php endif; ?>
</body>
</html>