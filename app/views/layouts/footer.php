<?php if (empty($skipLayoutFooterContent)): ?>
    <?php require '../app/views/layouts/footer_content.php'; ?>
<?php endif; ?>

        <!-- Scroll to top -->
        <a href="#" class="bg-secondary text-white hover-text-secondary" id="scroll"><i class="fas fa-angle-up"></i></a>
    </div> <!-- end row wrapping header/content -->
</div> <!-- end page-wrapper -->

<?php require_once __DIR__ . '/../shared/popup.php'; ?>

<!-- Js Link -->
<script src="<?= BASEURL ?>/js/app-popup.js"></script>
<script src="<?= BASEURL ?>/js/jquery.min.js"></script>
<?php
$__re_chat_uri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($__re_chat_uri, '/admin') === false) {
    require_once __DIR__ . '/../partials/chat_widget.php';
}
unset($__re_chat_uri);
?>
<script src="<?= BASEURL ?>/js/tinymce/tinymce.min.js"></script>
<script src="<?= BASEURL ?>/js/tinymce/init-tinymce.min.js"></script>
<!-- jQuery Layer Slider -->
<script src="<?= BASEURL ?>/js/greensock.js"></script>
<script src="<?= BASEURL ?>/js/layerslider.transitions.js"></script>
<script src="<?= BASEURL ?>/js/layerslider.kreaturamedia.jquery.js"></script>
<script src="<?= BASEURL ?>/js/popper.min.js"></script>
<script src="<?= BASEURL ?>/js/bootstrap.min.js"></script>
<script src="<?= BASEURL ?>/js/owl.carousel.min.js"></script>
<script src="<?= BASEURL ?>/js/tmpl.js"></script>
<script src="<?= BASEURL ?>/js/jquery.dependClass-0.1.js"></script>
<script src="<?= BASEURL ?>/js/draggable-0.1.js"></script>
<script src="<?= BASEURL ?>/js/jquery.slider.js"></script>
<script src="<?= BASEURL ?>/js/wow.js"></script>
<script src="<?= BASEURL ?>/js/custom.js"></script>
<script src="<?= BASEURL ?>/js/lx-select.js"></script>
</body>
</html>
