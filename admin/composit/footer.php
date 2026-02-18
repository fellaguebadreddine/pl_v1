      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">تطوير و انجاز</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2026&nbsp;
          <a href="#" class="text-decoration-none">منصة تسيير </a>.
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="../js/adminlte.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- MODAL FERME SOCIETE  -->


  <div class="modal fade" id="close_societe" tabindex="-1" aria-labelledby="ajouterGradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" dir="rtl">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="ajouterGradeModalLabel">
                        <i class="fas fa-building me-2"></i>  إغلاق المؤسسة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">

        <p>هل انت متأكد من إغلاق المؤسسة<?php if (isset($nav_societe->Dossier)) {

                                                        echo $nav_societe->Dossier;

                                                    } ?></p>

    </div>

    <div class="modal-footer">
        <a href="close_file.php?action=close_societe" class="btn btn-success">تسجيل الخروج </a>
    </div>

</div>

<!-- END MODAL FERME SOCIETE  -->

    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
        const isMobile = window.innerWidth <= 992;

        if (
          sidebarWrapper &&
          OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <script>
function sauvegarderCommentaire() {
    var id_tableau = <?php echo $id; ?>;
    var commentaire = $('#commentaire').val();

    $.ajax({
        url: 'ajax/save_commentaire_tableau.php',
        type: 'POST',
        data: {
            id: id_tableau,
            commentaire: commentaire
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم حفظ الملاحظة بنجاح');
            } else {
                alert('حدث خطأ: ' + response.message);
            }
        },
        error: function() {
            alert('حدث خطأ في الاتصال');
        }
    });
}
</script>

<script>
    function commentTableau1(id) {
    // Option 1 : ouvrir une boîte de dialogue simple
    var commentaire = prompt("أدخل ملاحظتك على هذا الجدول:");
    if (commentaire !== null) {
        $.ajax({
            url: 'ajax/save_commentaire_tableau.php',
            type: 'POST',
            data: {
                id: id,
                commentaire: commentaire
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('تم حفظ الملاحظة');
                    location.reload(); // recharge proprement
                } else {
                    alert('خطأ: ' + response.message);
                }
            },
            error: function() {
                alert('خطأ في الاتصال');
            }
        });
    }
}
</script>

<script>
function validerTableau(id) {
    Swal.fire({
        title: 'تأكيد المصادقة',
        text: 'هل أنت متأكد من المصادقة على هذا الجدول؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'نعم، مصادقة',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax/valider_tableau.php',
                type: 'POST',
                data: {
                    id: id,
                    action: 'valider'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تمت المصادقة',
                            text: 'تمت المصادقة على الجدول بنجاح',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: response.message || 'حدث خطأ أثناء المصادقة'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'حدث خطأ في الاتصال'
                    });
                }
            });
        }
    });
}

function demanderModification(id) {
    Swal.fire({
        title: 'طلب تعديل',
        input: 'textarea',
        inputLabel: 'سبب طلب التعديل',
        inputPlaceholder: 'اكتب سبب طلب التعديل هنا...',
        inputAttributes: {
            'aria-label': 'اكتب سبب طلب التعديل'
        },
        showCancelButton: true,
        confirmButtonText: 'إرسال',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#ffc107',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return 'الرجاء إدخال سبب طلب التعديل';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax/valider_tableau.php',
                type: 'POST',
                data: {
                    id: id,
                    action: 'demander_modification',
                    commentaire: result.value
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم الإرسال',
                            text: 'تم إرسال طلب التعديل إلى المؤسسة',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: response.message || 'حدث خطأ أثناء الإرسال'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'حدث خطأ في الاتصال'
                    });
                }
            });
        }
    });
}
</script>
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
