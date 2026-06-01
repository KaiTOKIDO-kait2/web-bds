        <!-- jQuery -->
        <script src="<?= BASEURL ?>/admin/assets/js/jquery-3.2.1.min.js"></script>
        
        <!-- Bootstrap Core JS -->
        <script src="<?= BASEURL ?>/admin/assets/js/popper.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/js/bootstrap.min.js"></script>
        
        <!-- Slimscroll JS -->
        <script src="<?= BASEURL ?>/admin/assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
        
        <!-- Datatables JS -->
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/dataTables.responsive.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/responsive.bootstrap4.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/dataTables.select.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/dataTables.buttons.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/buttons.bootstrap4.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/buttons.html5.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/buttons.flash.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/datatables/buttons.print.min.js"></script>

        <!-- Custom JS -->
        <script src="<?= BASEURL ?>/admin/assets/js/script.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/tinymce/tinymce.min.js"></script>
        <script src="<?= BASEURL ?>/admin/assets/plugins/tinymce/init-tinymce.min.js"></script>

        <!-- Modern Admin JS -->
        <script>
        (function($){
            /* ---- Slimscroll sidebar ---- */
            if($.fn.slimscroll) {
                $('.sidebar .sidebar-inner').slimscroll({ height: 'calc(100vh - 64px)', width: '100%', size: '4px', color: '#475569' });
            }

            /* ---- Remove conflicting legacy delegated handlers ---- */
            $(document).off('click', '#mobile_btn');
            $(document).off('click', '#toggle_btn');
            $('#mobile_btn').off('click');
            $('#toggle_btn').off('click');

            /* ---- Force stable sidebar state (disable collapse flicker) ---- */
            $('body').removeClass('mini-sidebar expand-menu slide-nav');
            $('html').removeClass('menu-opened');
            try { localStorage.removeItem('mini-sidebar'); } catch (e) {}

            /* ---- Override old script.js sidebar submenu (re-bind with new logic) ---- */
            $('#sidebar-menu a').off('click');
            $('#sidebar-menu .submenu > a').on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                var $li = $(this).parent();
                var $ul = $(this).next('ul');
                if($li.hasClass('open')) {
                    $ul.slideUp(220);
                    $li.removeClass('open');
                } else {
                    $('#sidebar-menu .submenu.open > ul').slideUp(220);
                    $('#sidebar-menu .submenu.open').removeClass('open');
                    $ul.slideDown(220);
                    $li.addClass('open');
                }
            });

            /* ---- Highlight active sidebar link (exact path or child route) ---- */
            var path = window.location.pathname.replace(/\/+$/, '') || '/';
            $('#sidebar-menu a[href]').each(function(){
                var href = $(this).attr('href');
                if(!href || href === '#' || href.indexOf('javascript:') === 0) {
                    return;
                }
                var hrefPath = href.replace(/^https?:\/\/[^\/]+/, '').replace(/\/+$/, '') || '/';
                var isActive = (path === hrefPath) || (hrefPath !== '/' && path.indexOf(hrefPath + '/') === 0);
                if(!isActive) {
                    return;
                }
                $(this).addClass('active');
                $(this).parent('li').addClass('active');
                var $sub = $(this).closest('.submenu');
                if($sub.length) {
                    $sub.addClass('open');
                    $sub.children('ul').show();
                }
            });

            /* ---- Sidebar collapse/mobile toggle intentionally disabled ---- */

            /* ---- Admin user dropdown: custom instant toggle ---- */
            var $adminMenuToggle = $('#admin-user-menu-toggle');
            var $adminMenu = $('#admin-user-menu');

            function closeAdminMenu() {
                $adminMenu.removeClass('show');
                $adminMenuToggle.attr('aria-expanded', 'false');
            }

            function toggleAdminMenu(e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = $adminMenu.hasClass('show');
                $('.dropdown-menu.show').not($adminMenu).removeClass('show');
                $adminMenu.toggleClass('show', !isOpen);
                $adminMenuToggle.attr('aria-expanded', isOpen ? 'false' : 'true');
            }

            $adminMenuToggle.on('click', toggleAdminMenu);
            $adminMenu.on('click', '.dropdown-item', closeAdminMenu);
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.nav.user-menu').length) {
                    closeAdminMenu();
                }
            });
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAdminMenu();
                }
            });

            /* ---- Remove old overlay element added by legacy script.js ---- */
            if ($('.sidebar-overlay').length) {
                $('.sidebar-overlay').off('click').remove();
            }
            if ($('#navOverlay').length) {
                $('#navOverlay').off('click').remove();
            }

            /* ---- DataTables are initialized by admin/assets/js/script.js ---- */
            /* Avoid re-init here to prevent: Cannot reinitialise DataTable */
        })(jQuery);
        </script>
    </body>
</html>
