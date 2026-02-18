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
    $(document).ready(function(){
    $('[data-bs-toggle="tooltip"]').tooltip();
});
$(document).ready(function() {
    console.log('Document ready - Initialisation AJAX');
    
    // Initialiser Select2
    $('.select2').select2({
        placeholder: "اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl"
    });   
   
    });  
    // Afficher un message
function showAlert(message, type = 'success') {
    var alertClass = type === 'success' ? 'alert-success' : 
                     type === 'error' ? 'alert-danger' : 
                     type === 'warning' ? 'alert-warning' : 'alert-info';
    
    var alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#alertContainer').html(alertHtml);
    
    // Supprimer automatiquement après 5 secondes
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
} 

</script>
<script>
$(document).ready(function(){

    /* ===============================
       CALCUL AUTOMATIQUE DIFFERENCE HF
    ================================ */

    $(document).on('input', '.postes-total, .postes-reel', function(){
        let row = $(this).closest('tr');
        let total = parseInt(row.find('.postes-total').val()) || 0;
        let reel = parseInt(row.find('.postes-reel').val()) || 0;
        row.find('.difference').val(reel - total);
    });

    /* ===============================
       CALCUL AUTOMATIQUE DIFFERENCE HP
    ================================ */

    $(document).on('input', '.postes-total-hp, .postes-reel-hp', function(){
        let row = $(this).closest('tr');
        let total = parseInt(row.find('.postes-total-hp').val()) || 0;
        let reel = parseInt(row.find('.postes-reel-hp').val()) || 0;
        row.find('.difference-hp').val(reel - total);
    });

    /* ===============================
       AJOUT HAUTS FONCTIONNAIRES
    ================================ */

    $('.add-hf-btn').click(function(){

        let row = $(this).closest('tr');

        let data = {
            action: 'add_detail',
            type: 'hf',
            id_user: <?php echo $current_user->id; ?>,
            id_societe: <?php echo $current_user->id_societe; ?>,
            annee: <?php echo $annee; ?>,

            id_grade: row.find('.grade-select').val(),
            postes_total: row.find('.postes-total').val(),
            postes_reel: row.find('.postes-reel').val(),
            poste_intirim: row.find('.poste-intirim').val(),
            poste_femme: row.find('.poste-femme').val(),
            difference: row.find('.difference').val(),
            observations: row.find('.observations').val()
        };

        if(!data.id_grade){
            alert("اختر الوظيفة");
            return;
        }

        $('#loadingOverlay').show();

        $.post('ajax/traitement_tab1.php', data, function(response){

            $('#loadingOverlay').hide();

            if(response.success){
                showAlert(response.message, 'success');
                location.reload(); // recharge proprement
            }else{
                alert(response.message);
            }

        }, 'json');
    });

    /* ===============================
       AJOUT HAUTS POSTES
    ================================ */

    $('.add-hp-btn').click(function(){

        let row = $(this).closest('tr');

        let data = {
            action: 'add_detail',
            type: 'hp',
            id_user: <?php echo $current_user->id; ?>,
            id_societe: <?php echo $current_user->id_societe; ?>,
            annee: <?php echo $annee; ?>,

            id_grade_hp: row.find('.grade-select-hp').val(),
            postes_total_hp: row.find('.postes-total-hp').val(),
            postes_reel_hp: row.find('.postes-reel-hp').val(),
            poste_intirim_hp: row.find('.poste-intirim-hp').val(),
            poste_femme_hp: row.find('.poste-femme-hp').val(),
            difference_hp: row.find('.difference-hp').val(),
            observations_hp: row.find('.observations-hp').val()
        };

        if(!data.id_grade_hp){
            alert("اختر المنصب");
            return;
        }

        $('#loadingOverlay').show();

        $.post('ajax/traitement_tab1.php', data, function(response){

            $('#loadingOverlay').hide();

            if(response.success){
                location.reload();
            }else{
                alert(response.message);
            }

        }, 'json');
    });

});
</script>
<script>
function deleteDetail(id, type){

    if(!confirm("تأكيد الحذف ؟")) return;

    $('#loadingOverlay').show();

    $.post('ajax/traitement_tab1.php', {
        action: 'delete_detail',
        id: id,
        type: type
    }, function(response){

        $('#loadingOverlay').hide();

        if(response.success){
            location.reload();
        }else{
            alert(response.message);
        }

    }, 'json');
}
</script>
<script>
function saveTableau(){

    if(!confirm("تأكيد حفظ وتقديم الجدول ؟")) return;

    $('#loadingOverlay').show();

    $.post('ajax/traitement_tab1.php', {

        action: 'save_tableau',
        statut: 'validé',
        id_user: <?php echo $current_user->id; ?>,
        id_societe: <?php echo $current_user->id_societe; ?>,
        annee: <?php echo $annee; ?>

    }, function(response){

        $('#loadingOverlay').hide();

        if(response.success){
            alert(response.message);
            window.location.href='?action=list_tab1';
        }else{
            alert(response.message);
        }

    }, 'json');
}
</script>



<script>
// Variables globales
let compteurLignes = <?php echo $hf_index + count($details_hp); ?>;
let tousLesGrades = <?php echo json_encode($grades_js); ?>;
let isSubmitting = false;

$(document).ready(function() {
    // Initialiser Select2
    $('.select-grade').select2({
        placeholder: "ابحث أو اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl",
        language: {
            noResults: function() { return "لا توجد نتائج"; },
            searching: function() { return "جاري البحث..."; }
        }
    });

    // Événements pour les calculs
    $(document).on('input', '.postes-total, .postes-reel', function() {
        calculerDifference(this);
        calculerTotaux();
    });
    $(document).on('input', '.poste-intirim, .poste-femme', function() {
        calculerTotaux();
    });

    // Événement changement de select
    $(document).on('change', '.select-grade', function() {
        actualiserCode(this);
    });

    // Suppression de ligne
    $(document).on('click', '.btn-delete-row', function() {
        supprimerLigne(this);
    });

    // Ajout de ligne
    $('#btnAddHf').click(function() { ajouterLigne('hauts_fonctionnaires'); });
    $('#btnAddHp').click(function() { ajouterLigne('hauts_postes'); });

    // Soumission
    $('#btnSubmit').click(function() { soumettreFormulaire('validé'); });

    // Calculer les totaux initiaux
    calculerTotaux();
});

function ajouterLigne(section) {
    const tbody = $(`#tbody_${section}`);
    const index = compteurLignes++;
    const isHf = section === 'hauts_fonctionnaires';
    const placeholder = isHf ? 'اختر الوظيفة' : 'اختر المنصب';
    let options = `<option value="">${placeholder}</option>`;
    tousLesGrades.forEach(g => {
        options += `<option value="${g.id}" data-code="${g.code}">${g.designation}</option>`;
    });

    const row = `
        <tr>
            <td>
                <input type="hidden" name="details[${index}][id]" value="0">
                <input type="hidden" name="details[${index}][section]" value="${section}">
                <input type="hidden" name="details[${index}][id_grade]" value="">
                <input type="text" class="form-control text-center code-grade" readonly>
            </td>
            <td>
                <select name="details[${index}][id_grade_select]" class="form-control select-grade" data-section="${section}" required>
                    ${options}
                </select>
            </td>
            <td><input type="number" name="details[${index}][postes_total]" class="form-control text-center postes-total" value="0" min="0" required></td>
            <td><input type="number" name="details[${index}][postes_reel]" class="form-control text-center postes-reel" value="0" min="0" required></td>
            <td><input type="number" name="details[${index}][poste_intirim]" class="form-control text-center poste-intirim" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][poste_femme]" class="form-control text-center poste-femme" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][difference]" class="form-control text-center difference" value="0" readonly></td>
            <td><textarea name="details[${index}][observations]" class="form-control" rows="1"></textarea></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-delete-row"><i class="fas fa-trash"></i></button></td>
        </tr>
    `;
    tbody.append(row);
    tbody.find('tr:last .select-grade').select2({
        placeholder: "ابحث أو اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl"
    });
}

function actualiserCode(select) {
    const $select = $(select);
    const row = $select.closest('tr');
    const selected = $select.find('option:selected');
    const code = selected.data('code') || '';
    const idGrade = selected.val();
    row.find('.code-grade').val(code);
    row.find('input[name*="[id_grade]"]').val(idGrade);
}

function calculerDifference(input) {
    const row = $(input).closest('tr');
    const postesTotal = parseFloat(row.find('.postes-total').val()) || 0;
    const postesReel = parseFloat(row.find('.postes-reel').val()) || 0;
    row.find('.difference').val(postesReel - postesTotal);
}

function calculerTotaux() {
    let hfTotal = 0, hfReel = 0, hfInt = 0, hfFem = 0;
    let hpTotal = 0, hpReel = 0, hpInt = 0, hpFem = 0;

    $('#tbody_hauts_fonctionnaires tr').each(function() {
        hfTotal += parseFloat($(this).find('.postes-total').val()) || 0;
        hfReel += parseFloat($(this).find('.postes-reel').val()) || 0;
        hfInt += parseFloat($(this).find('.poste-intirim').val()) || 0;
        hfFem += parseFloat($(this).find('.poste-femme').val()) || 0;
    });

    $('#tbody_hauts_postes tr').each(function() {
        hpTotal += parseFloat($(this).find('.postes-total').val()) || 0;
        hpReel += parseFloat($(this).find('.postes-reel').val()) || 0;
        hpInt += parseFloat($(this).find('.poste-intirim').val()) || 0;
        hpFem += parseFloat($(this).find('.poste-femme').val()) || 0;
    });

    $('#total_hf_postes_total').text(hfTotal);
    $('#total_hf_postes_reel').text(hfReel);
    $('#total_hf_poste_intirim').text(hfInt);
    $('#total_hf_poste_femme').text(hfFem);
    $('#total_hf_difference').text(hfReel - hfTotal);

    $('#total_hp_postes_total').text(hpTotal);
    $('#total_hp_postes_reel').text(hpReel);
    $('#total_hp_poste_intirim').text(hpInt);
    $('#total_hp_poste_femme').text(hpFem);
    $('#total_hp_difference').text(hpReel - hpTotal);

    $('#general_hf_postes_total').text(hfTotal);
    $('#general_hf_postes_reel').text(hfReel);
    $('#general_hf_intirim').text(hfInt);
    $('#general_hf_femme').text(hfFem);
    $('#general_hf_diff').text(hfReel - hfTotal);

    $('#general_hp_postes_total').text(hpTotal);
    $('#general_hp_postes_reel').text(hpReel);
    $('#general_hp_intirim').text(hpInt);
    $('#general_hp_femme').text(hpFem);
    $('#general_hp_diff').text(hpReel - hpTotal);

    $('#general_total_postes').text(hfTotal + hpTotal);
    $('#general_total_reel').text(hfReel + hpReel);
    $('#general_total_intirim').text(hfInt + hpInt);
    $('#general_total_femme').text(hfFem + hpFem);
    $('#general_total_diff').text((hfReel + hpReel) - (hfTotal + hpTotal));
}

function supprimerLigne(btn) {
    if (!confirm('هل أنت متأكد من حذف هذا السطر؟')) return;
    const row = $(btn).closest('tr');
    const idDetail = row.data('id-detail');
    if (idDetail && idDetail > 0) {
        $('<input>').attr({ type: 'hidden', name: 'supprimer_details[]', value: idDetail }).appendTo(row.parent());
        row.hide();
    } else {
        row.remove();
    }
    calculerTotaux();
}

function enregistrerBrouillon() {
    soumettreFormulaire('brouillon');
}

function soumettreFormulaire(statut) {
    if (isSubmitting) return;
    isSubmitting = true;

    const form = $('#formulaireTableau1')[0];
    const formData = new FormData(form);
    formData.append('statut', statut);

    showLoading(true);

    fetch('ajax/traitement_tab1.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'تم بنجاح',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                if (statut === 'validé')
                    window.location.href = 'tab1.php?action=list_tab1&success=update';
                else
                    window.location.reload();
            });
        } else {
            Swal.fire({ icon: 'error', title: 'خطأ', text: data.message });
            isSubmitting = false;
        }
    })
    .catch(error => {
        showLoading(false);
        Swal.fire({ icon: 'error', title: 'خطأ', text: 'حدث خطأ في الاتصال' });
        isSubmitting = false;
    });
}

function showLoading(show) {
    $('#loadingOverlay').css('display', show ? 'flex' : 'none');
    $('#btnSubmit, .btn-warning').prop('disabled', show);
}
</script>


    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
