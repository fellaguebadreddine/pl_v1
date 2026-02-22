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
<?php if ($action == "add_tab1" || $action == "edit_tab1"){?>
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
<?php }?>

<?php if ($action == "add_tab3" || $action == "edit_tab3" ){?>
<!--begin::Script-->
   
<script>
// Variables globales
let compteurLignes = <?php echo $index; ?>;
let tousLesGrades = <?php echo json_encode($grades_js); ?>;

// Initialiser Select2
document.addEventListener('DOMContentLoaded', function() {
    $('.select-grade').select2({
        placeholder: "ابحث أو اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl",
        language: {
            noResults: function() { return "لا توجد نتائج"; },
            searching: function() { return "جاري البحث..."; }
        },
        templateResult: function(grade) {
            if (!grade.id) return grade.text;
            const gradeObj = tousLesGrades.find(g => g.id == grade.id);
            if (gradeObj) {
                let $result = $('<span>' + gradeObj.designation + '</span>');
                if (gradeObj.loi) $result.append('<br><small class="text-muted">' + gradeObj.loi + '</small>');
                return $result;
            }
            return grade.text;
        },
        templateSelection: function(grade) {
            if (!grade.id) return grade.text;
            const gradeObj = tousLesGrades.find(g => g.id == grade.id);
            return gradeObj ? gradeObj.designation : grade.text;
        }
    });
});

function ajouterLigne() {
    const tbody = document.getElementById('tbody_details');
    const nouvelleLigne = document.createElement('tr');
    let options = '<option value="">اختر السلك</option>';
    tousLesGrades.forEach(grade => {
        options += `<option value="${grade.id}" data-code="${grade.code}">${grade.designation}</option>`;
    });

    nouvelleLigne.innerHTML = `
        <td>
            <input type="hidden" name="details[${compteurLignes}][id]" value="0">
            <input type="hidden" name="details[${compteurLignes}][id_grade]" value="">
            <input type="text" class="form-control text-center code-grade" readonly>
        </td>
        <td>
            <select name="details[${compteurLignes}][id_grade_select]" class="form-control select-grade" required>
                ${options}
            </select>
        </td>
        <td><input type="checkbox" name="details[${compteurLignes}][interne]" value="1"></td>
        <td><input type="checkbox" name="details[${compteurLignes}][externe]" value="1"></td>
        <td><input type="checkbox" name="details[${compteurLignes}][diplome]" value="1"></td>
        <td><input type="checkbox" name="details[${compteurLignes}][concour]" value="1"></td>
        <td><input type="checkbox" name="details[${compteurLignes}][examen_pro]" value="1"></td>
        <td><input type="checkbox" name="details[${compteurLignes}][test_pro]" value="1"></td>
        <td><input type="text" name="details[${compteurLignes}][loi]" class="form-control"></td>
        <td><input type="number" name="details[${compteurLignes}][nomination]" class="form-control" value="0"></td>
        <td><textarea name="details[${compteurLignes}][observation]" class="form-control" rows="1"></textarea></td>
        <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(nouvelleLigne);
    $(nouvelleLigne.querySelector('.select-grade')).select2({
        placeholder: "ابحث أو اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl"
    });
    compteurLignes++;
}

function actualiserCode(select) {
    const row = select.closest('tr');
    const selectedOption = select.options[select.selectedIndex];
    const code = selectedOption.getAttribute('data-code') || '';
    const idGrade = selectedOption.value;
    row.querySelector('.code-grade').value = code;
    const hidden = row.querySelector('input[name*="[id_grade]"]');
    if (hidden) hidden.value = idGrade;
}

function supprimerLigne(button) {
    const row = button.closest('tr');
    const idDetail = row.getAttribute('data-id-detail');
    if (idDetail && idDetail > 0) {
        if (confirm('هل أنت متأكد من حذف هذا السطر؟')) {
            const inputSuppression = document.createElement('input');
            inputSuppression.type = 'hidden';
            inputSuppression.name = 'supprimer_details[]';
            inputSuppression.value = idDetail;
            row.parentNode.appendChild(inputSuppression);
            row.style.display = 'none';
        }
    } else {
        row.remove();
    }
}

// Gestion de la soumission
document.getElementById('formulaireTableau3')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirm('هل أنت متأكد من رغبتك في حفظ الجدول؟')) {
        // Mettre à jour les id_grade à partir des selects
        const selects = this.querySelectorAll('.select-grade');
        selects.forEach(select => {
            const row = select.closest('tr');
            const hidden = row.querySelector('input[name*="[id_grade]"]');
            if (hidden) hidden.value = select.value;
        });
        soumettreFormulaire(this, 'validé');
    }
});

function enregistrerBrouillon() {
    const form = document.getElementById('formulaireTableau3');
    const inputStatut = document.createElement('input');
    inputStatut.type = 'hidden';
    inputStatut.name = 'statut';
    inputStatut.value = 'brouillon';
    form.appendChild(inputStatut);
    if (confirm('هل تريد حفظ الجدول كمسودة؟')) {
        const selects = form.querySelectorAll('.select-grade');
        selects.forEach(select => {
            const row = select.closest('tr');
            const hidden = row.querySelector('input[name*="[id_grade]"]');
            if (hidden) hidden.value = select.value;
        });
        soumettreFormulaire(form, 'brouillon');
    } else {
        form.removeChild(inputStatut);
    }
}

function soumettreFormulaire(form, statut) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...';
    submitBtn.disabled = true;

    fetch('ajax/traitement_tab3.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => {
                if (statut == 'brouillon') {
                    window.location.reload();
                } else {
                    window.location.href = '?action=list_tab3&success=1';
                }
            }, 1500);
        } else {
            showMessage(data.message, 'danger');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error(error);
        showMessage('حدث خطأ أثناء الاتصال بالخادم', 'danger');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showMessage(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.app-content .container-fluid').prepend(alertDiv);
    setTimeout(() => alertDiv.remove(), 5000);
}

function supprimerTableau(id) {
    if (confirm('هل أنت متأكد من حذف هذا الجدول؟')) {
        fetch('ajax/traitement_tab3.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=delete_tab3&id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showMessage(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error(error);
            showMessage('حدث خطأ أثناء الاتصال بالخادم', 'danger');
        });
    }
}
</script>
<?php }?>

 <?php if ($action == "add_tab4" || $action == "edit_tab4"){ ?>
<script>
let compteurLignes = <?php echo $index; ?>;
let tousLesGrades = <?php echo json_encode($grades_js); ?>;

$(document).ready(function() {
    $('.select-grade').select2({
        placeholder: "ابحث أو اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl",
        language: { noResults: () => "لا توجد نتائج", searching: () => "جاري البحث..." }
    });
    $(document).on('change', '.select-grade', function() {
        const row = $(this).closest('tr');
        const selected = $(this).find('option:selected');
        const code = selected.data('code') || '';
        const idGrade = selected.val();
        row.find('.code-grade').val(code);
        row.find('input[name*="[id_grade]"]').val(idGrade);
    });
    calculerTotaux();
});

function ajouterLigne() {
    const tbody = $('#tbody_details');
    const index = compteurLignes++;
    let options = '<option value="">اختر السلك</option>';
    tousLesGrades.forEach(g => options += `<option value="${g.id}" data-code="${g.code}">${g.designation}</option>`);
    const row = `
        <tr>
            <td><input type="hidden" name="details[${index}][id]" value="0"><input type="hidden" name="details[${index}][id_grade]" value=""><input type="text" class="form-control text-center code-grade" readonly></td>
            <td><select name="details[${index}][id_grade_select]" class="form-control select-grade" required>${options}</select></td>
            <td><input type="number" name="details[${index}][postes_vacants_externe]" class="form-control" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][produit_formation_paramedicale]" class="form-control" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][concours_sur_titre]" class="form-control" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][debutants_contractuels]" class="form-control" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][ouvriers_batiment_contractuels]" class="form-control" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][methode_sur_titre]" class="form-control" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][examen_mini]" class="form-control" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][test_mini_ouvriers]" class="form-control" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][postes_financiers_exploites]" class="form-control" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][nombre_postes_financiers_exploites]" class="form-control" value="0" min="0"></td>
            <td><textarea name="details[${index}][observations]" class="form-control" rows="1"></textarea></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)"><i class="fas fa-trash"></i></button></td>
        </tr>
    `;
    tbody.append(row);
    tbody.find('tr:last .select-grade').select2({ placeholder: "ابحث أو اختر...", allowClear: true, width: '100%', dir: "rtl" });
}

function supprimerLigne(btn) {
    const row = $(btn).closest('tr');
    const idDetail = row.data('id-detail');
    if (idDetail && idDetail > 0) {
        if (confirm('هل أنت متأكد من حذف هذا السطر؟')) {
            $('<input>').attr({ type: 'hidden', name: 'supprimer_details[]', value: idDetail }).appendTo(row.parent());
            row.hide();
        }
    } else {
        row.remove();
    }
    calculerTotaux();
}

function calculerTotaux() {
    let totals = { postes_vacants:0, produit_formation:0, concours:0, debutants:0, ouvriers:0, methode:0, examen_mini:0, test_mini:0, postes_financiers:0, nombre_postes:0 };
    $('#tbody_details tr:visible').each(function() {
        totals.postes_vacants += parseFloat($(this).find('input[name*="[postes_vacants_externe]"]').val()) || 0;
        totals.produit_formation += parseFloat($(this).find('input[name*="[produit_formation_paramedicale]"]').val()) || 0;
        totals.concours += parseFloat($(this).find('input[name*="[concours_sur_titre]"]').val()) || 0;
        totals.debutants += parseFloat($(this).find('input[name*="[debutants_contractuels]"]').val()) || 0;
        totals.ouvriers += parseFloat($(this).find('input[name*="[ouvriers_batiment_contractuels]"]').val()) || 0;
        totals.methode += parseFloat($(this).find('input[name*="[methode_sur_titre]"]').val()) || 0;
        totals.examen_mini += parseFloat($(this).find('input[name*="[examen_mini]"]').val()) || 0;
        totals.test_mini += parseFloat($(this).find('input[name*="[test_mini_ouvriers]"]').val()) || 0;
        totals.postes_financiers += parseFloat($(this).find('input[name*="[postes_financiers_exploites]"]').val()) || 0;
        totals.nombre_postes += parseFloat($(this).find('input[name*="[nombre_postes_financiers_exploites]"]').val()) || 0;
    });
    $('#total_postes_vacants').text(totals.postes_vacants);
    $('#total_produit_formation').text(totals.produit_formation);
    $('#total_concours').text(totals.concours);
    $('#total_debutants').text(totals.debutants);
    $('#total_ouvriers').text(totals.ouvriers);
    $('#total_methode').text(totals.methode);
    $('#total_examen_mini').text(totals.examen_mini);
    $('#total_test_mini').text(totals.test_mini);
    $('#total_postes_financiers').text(totals.postes_financiers);
    $('#total_nombre_postes').text(totals.nombre_postes);
}

function enregistrerBrouillon() {
    const form = $('#formulaireTableau4')[0];
    const input = $('<input>').attr({ type: 'hidden', name: 'statut', value: 'brouillon' });
    $(form).append(input);
    if (confirm('هل تريد حفظ الجدول كمسودة؟')) {
        soumettreFormulaire(form, 'brouillon');
    } else {
        input.remove();
    }
}
// Gestion de la soumission
document.getElementById('formulaireTableau4')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirm('هل أنت متأكد من رغبتك في حفظ الجدول؟')) {
        // Mettre à jour les id_grade à partir des selects
        const selects = this.querySelectorAll('.select-grade');
        selects.forEach(select => {
            const row = select.closest('tr');
            const hidden = row.querySelector('input[name*="[id_grade]"]');
            if (hidden) hidden.value = select.value;
        });
        soumettreFormulaire(this, 'validé');
    }
});
function soumettreFormulaire(form, statut) {
    const formData = new FormData(form);
    const submitBtn = $(form).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...').prop('disabled', true);

    fetch('ajax/traitement_tab4.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => {
                    if (statut == 'brouillon') window.location.reload();
                    else window.location.href = '?action=list_tab4&success=1';
                }, 1500);
            } else {
                showMessage(data.message, 'danger');
                submitBtn.html(originalText).prop('disabled', false);
            }
        })
        .catch(() => {
            showMessage('حدث خطأ أثناء الاتصال بالخادم', 'danger');
            submitBtn.html(originalText).prop('disabled', false);
        });
}

function showMessage(msg, type) {
    const alert = $('<div class="alert alert-'+type+' alert-dismissible fade show" role="alert">'+msg+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    $('.app-content .container-fluid').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
}

function supprimerTableau(id) {
    if (confirm('هل أنت متأكد من حذف هذا الجدول؟')) {
        fetch('ajax/traitement_tab4.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=delete_tab4&id='+id })
            .then(res => res.json())
            .then(data => {
                if (data.success) { showMessage(data.message, 'success'); setTimeout(() => window.location.reload(), 1500); }
                else showMessage(data.message, 'danger');
            })
            .catch(() => showMessage('حدث خطأ أثناء الاتصال بالخادم', 'danger'));
    }
}
</script>

<?php }?> 


    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
