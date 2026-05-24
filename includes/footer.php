<?php
$includePersonnelModals = $includePersonnelModals ?? false;
if (!function_exists('org_proses_saran_url')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
}
$prosesSaranUrl = defined('ORG_PROSES_SARAN_URL') ? ORG_PROSES_SARAN_URL : org_proses_saran_url();
$prosesSaranUrlEsc = htmlspecialchars($prosesSaranUrl, ENT_QUOTES, 'UTF-8');
$orgFooterBerandaEarly = defined('ORG_BERANDA_PAGE') && ORG_BERANDA_PAGE === true;
if (!$orgFooterBerandaEarly) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_tailwind_assets.php';
    org_tailwind_bootstrap();
}
org_component('footer', ['prosesSaranUrlEsc' => $prosesSaranUrlEsc]);
?>

    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?php
                    $loginAction = function_exists('org_home_url') ? org_home_url() : 'index.php';
                    echo htmlspecialchars($loginAction, ENT_QUOTES, 'UTF-8');
                ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">Login Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="login">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Masuk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($includePersonnelModals && $isAdmin): ?>
        <div class="modal fade" id="addPersonnelModal" tabindex="-1" aria-labelledby="addPersonnelModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPersonnelModalLabel">Tambah Personel</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add_personnel">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="return_to" value="profil.php">
                            <div class="mb-3">
                                <label for="add_person_name" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="add_person_name" name="person_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="add_person_nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="add_person_nip" name="person_nip" maxlength="20" placeholder="Maks. 20 karakter" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="add_person_position" class="form-label">Jabatan</label>
                                <input type="text" class="form-control" id="add_person_position" name="person_position" required>
                            </div>
                            <div class="mb-2">
                                <label for="add_person_photo" class="form-label">Foto (JPG/PNG)</label>
                                <input type="file" class="form-control" id="add_person_photo" name="person_photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Unggah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editPersonnelModal" tabindex="-1" aria-labelledby="editPersonnelModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editPersonnelModalLabel">Edit Personel</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="edit_personnel">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="return_to" value="profil.php">
                            <input type="hidden" name="person_slug" id="edit_person_slug">
                            <input type="hidden" name="person_id" id="edit_person_id">
                            <div class="mb-3">
                                <label for="edit_person_name" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="edit_person_name" name="person_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_person_nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="edit_person_nip" name="person_nip" maxlength="20" placeholder="Maks. 20 karakter" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="edit_person_position" class="form-label">Jabatan</label>
                                <input type="text" class="form-control" id="edit_person_position" name="person_position" required>
                            </div>
                            <div class="mb-2">
                                <label for="edit_person_photo" class="form-label">Ganti Foto (JPG/PNG)</label>
                                <input type="file" class="form-control" id="edit_person_photo" name="person_photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_vendor_assets.php';
echo org_vendor_script(org_vendor_bootstrap_js());
if (!defined('ORG_BERANDA_PAGE') || ORG_BERANDA_PAGE !== true) {
    echo org_vendor_script(org_vendor_swiper_js());
    echo org_vendor_script(org_vendor_aos_js());
}
?>
<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_motion_assets.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_theme_assets.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_navbar_assets.php';
$orgFooterBeranda = defined('ORG_BERANDA_PAGE') && ORG_BERANDA_PAGE === true;
if (!$orgFooterBeranda) {
    echo org_motion_script_tag();
}
echo org_theme_script_tag();
echo org_navbar_script_tag();
if ($orgFooterBeranda) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_beranda_assets.php';
    $orgBerandaLoadApex = defined('ORG_BERANDA_NEED_APEX') && ORG_BERANDA_NEED_APEX === true;
    echo org_beranda_footer_chart_scripts($orgBerandaLoadApex);
    if ($orgBerandaLoadApex) {
        echo org_beranda_team_target_charts_script_tag();
    }
    echo org_beranda_lite_render_script_tag();
    echo org_beranda_deferred_script_tag();
    echo org_beranda_navbar_footer_cascade_markup();
    echo org_beranda_portal_header_offset_script();
} elseif (defined('ORG_SG_PORTAL_PAGE') && ORG_SG_PORTAL_PAGE === true) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';
    echo org_portal_navbar_footer_cascade_markup();
}
?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'scripts_footer.php'; ?>
<?php
if (!empty($extraFooterMarkup) && is_string($extraFooterMarkup)) {
    echo $extraFooterMarkup;
}
?>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'ai_chat_widget.php'; ?>
</body>
</html>
