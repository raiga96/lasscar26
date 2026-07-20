<?php
/**
 * Templat Footer Admin Panel - SukanJTS Sarawak
 * Menutup pembungkus HTML dan memuatkan Bootstrap 5 JS.
 */
?>
    </div> <!-- /admin-content -->
</div> <!-- /#page-content-wrapper -->
</div> <!-- /#wrapper -->

<!-- Bootstrap 5 JS Bundle (with Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 Library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Logik SweetAlert2 Sesi Global -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if (!empty($swal_success)): ?>
            Swal.fire({
                title: 'Berjaya!',
                text: <?php echo json_encode($swal_success); ?>,
                icon: 'success',
                confirmButtonColor: '#0a2540'
            });
        <?php endif; ?>

        <?php if (!empty($swal_error)): ?>
            Swal.fire({
                title: 'Ralat!',
                text: <?php echo json_encode($swal_error); ?>,
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        <?php endif; ?>
    });
</script>

</body>
</html>
<?php
// Tutup sambungan database jika objek wujud
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
