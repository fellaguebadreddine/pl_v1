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
    $('.select2').select2({
        placeholder: "اختر الوظيفة",
        allowClear: true,
        dir: "rtl",
        language: {
            noResults: function() {
                return "لا توجد نتائج";
            }
        }
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
<?php if ($action == "add_tab1" || $action == "edit_tab1"){?>
<script>
    function deleteDetailSup(id) {
    if (!confirm('هل أنت متأكد من حذف هذا السطر؟')) return;
    $.ajax({
        url: 'ajax/tableau1_sup_ajax.php',
        type: 'POST',
        data: { action: 'delete_sup', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('tr[data-id="' + id + '"]').fadeOut(300, function() {
                    $(this).remove();
                    // si plus de lignes, afficher un message
                    if ($('#existing_hauts_fonctionnaires tr').length === 0) {
                        $('#existing_hauts_fonctionnaires').html('<tr><td colspan="9" class="text-center text-muted">لا توجد بيانات مسجلة</td></tr>');
                    }
                });
            } else {
                alert(response.message || 'Erreur lors de la suppression');
            }
        },
        error: function() {
            alert('Erreur serveur');
        }
    });
}
    
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

    /*================================
    AJOUTER SUP POSTE
    ================================ */
    
$(document).on('click', '.add-sup-btn', function () {

    let btn = $(this);
    let row = btn.closest('tr');

    let data = {
        action: 'add_sup',
         id_user: <?php echo $current_user->id; ?>,
            id_societe: <?php echo $current_user->id_societe; ?>,
            annee: <?php echo $annee; ?>,
        id_tableau: <?php echo $id ?? 0; ?>,
        code: $('#code').val(),
        poste_sup: $('#poste_sup').val(),
        postes_total_sup: $('#postes_total_sup').val(),
        postes_reel_sup: $('#postes_reel_sup').val(),
        poste_intirim_sup: $('#poste_intirim_sup').val(),
        poste_femme_sup: $('#poste_femme_sup').val(),
        difference_sup: $('#difference_sup').val(),
        observations_sup: $('#observations_sup').val()
    };

    $.ajax({
        url: 'ajax/tableau1_sup_ajax.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function (response) {

            if (response.status === 'success') {

                $('#noDataSup').remove();

                $('#existing_hauts_fonctionnaires').append(response.html);

                // Reset inputs
                row.find('input').val('');
            } else {
                alert(response.message);
            }
        },
        error: function () {
            alert('Erreur serveur');
        }
    });

});


function calcDifferenceSup() {

    let total = parseFloat($('#postes_total_sup').val()) || 0;
    let reel  = parseFloat($('#postes_reel_sup').val()) || 0;

    let difference = total - reel;

    $('#difference_sup').val(difference);
}

// Calcul automatique quand on tape
$(document).on('keyup change', 
    '#postes_total_sup, #postes_reel_sup', 
    function () {
        calcDifferenceSup();
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
        statut: 'en_attente',
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

<!--begin TABLEAU 1 - 1-->
<?php if ($action == "add_tab1_1" || $action == "edit_tab1_1"){?>
<script>
$(document).ready(function(){

    // ==============================
    // CALCUL AUTOMATIQUE LIGNE SAISIE
    // ==============================
    $(document).on('keyup change', '.item-row input', function(){

        let row = $(this).closest('tr');

        let titulaires  = parseFloat(row.find('[name="titulaires"]').val()) || 0;
        let stagaires   = parseFloat(row.find('[name="stagaires"]').val()) || 0;
        let effectif1   = parseFloat(row.find('[name="effectif_reel_annee_1"]').val()) || 0;

        let total = titulaires + stagaires;
        row.find('[name="tol_titu_stag"]').val(total);

        let diff = total - effectif1;
        row.find('[name="difrence"]').val(diff);

        calculateTotals();
    });


    // ==============================
    // AJOUT LIGNE AJAX
    // ==============================
    $(document).on('click','.add-tab1_1-btn',function(){

        let row = $(this).closest('tr');

        let formData = {
            action: 'add_detail',
            id_grade: row.find('[name="id_grade"]').val(),
            effectif_reel_31_dec: row.find('[name="effectif_reel_31_dec"]').val(),
            effectif_reel_annee_1: row.find('[name="effectif_reel_annee_1"]').val(),
            titulaires: row.find('[name="titulaires"]').val(),
            stagaires: row.find('[name="stagaires"]').val(),
            femmes: row.find('[name="femmes"]').val(),
            titulaie_temps_complet: row.find('[name="titulaie_temps_complet"]').val(),
            titulaie_femmes_complet: row.find('[name="titulaie_femmes_complet"]').val(),
            titulaie_temps_partiel: row.find('[name="titulaie_temps_partiel"]').val(),
            titulaie_femmes_partiel: row.find('[name="titulaie_femmes_partiel"]').val(),
            contrat_temps_complet: row.find('[name="contrat_temps_complet"]').val(),
            contrat_femme_complet: row.find('[name="contrat_femme_complet"]').val(),
            contrat_temps_pratiel: row.find('[name="contrat_temps_pratiel"]').val(),
            contrat_femmes_pratiel: row.find('[name="contrat_femmes_pratiel"]').val(),
            observations: row.find('[name="observations"]').val()
        };

        $.ajax({
            url: 'ajax/traitement_tab1_1.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response){

                if(response.success){

                    // Supprimer message noData
                    $('#noData').remove();

                    // Ajouter ligne dans tableau
                    let newRow = `
                        <tr data-id="${response.id}">
                            <td></td>
                            <td>${response.grade_name}</td>
                            <td>${formData.effectif_reel_31_dec}</td>
                            <td>${formData.effectif_reel_annee_1}</td>
                            <td>${formData.titulaires}</td>
                            <td>${formData.stagaires}</td>
                            <td>${response.tol_titu_stag}</td>
                            <td>${formData.femmes}</td>
                            <td>${response.difrence}</td>
                            <td>${formData.titulaie_temps_complet}</td>
                            <td>${formData.titulaie_femmes_complet}</td>
                            <td>${formData.titulaie_temps_partiel}</td>
                            <td>${formData.titulaie_femmes_partiel}</td>
                            <td>${formData.contrat_temps_complet}</td>
                            <td>${formData.contrat_femme_complet}</td>
                            <td>${formData.contrat_temps_pratiel}</td>
                            <td>${formData.contrat_femmes_pratiel}</td>
                            <td>${formData.observations}</td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm delete-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    $('#existing_hauts_fonctionnaires').append(newRow);

                    // Reset inputs
                    row.find('input').val('');
                    row.find('select').val('').trigger('change');

                    calculateTotals();

                } else {
                    alert(response.message);
                }
            }
        });

    });


    // ==============================
    // SUPPRESSION AJAX
    // ==============================
    $(document).on('click','.delete-btn',function(){

        if(!confirm("تأكيد الحذف ؟")) return;

        let tr = $(this).closest('tr');
        let id = tr.data('id');

        $.ajax({
            url: 'ajax/traitement_tab1_1.php',
            type: 'POST',
            data: {
                action: 'delete_detail',
                id: id
            },
            dataType: 'json',
            success: function(response){

                if(response.success){
                    tr.remove();
                    calculateTotals();
                }else{
                    alert(response.message);
                }
            }
        });

    });


    calculateTotals();
});


// ==============================
// TOTAL GENERAL
// ==============================
function calculateTotals(){

    let totals = new Array(17).fill(0);

    $('#existing_hauts_fonctionnaires tr').each(function(){

        $(this).find('td').each(function(index){

            let val = parseFloat($(this).text()) || 0;

            if(index >= 2 && index <= 16){
                totals[index] += val;
            }
        });

    });

    $('.total_effectif_reel_31_dec').text(totals[2]);
    $('.total_effectif_reel_annee_1').text(totals[3]);
    $('.total_titulaires').text(totals[4]);
    $('.total_stagaires').text(totals[5]);
    $('.total_tol_titu_stag').text(totals[6]);
    $('.total_femmes').text(totals[7]);
    $('.total_difrence').text(totals[8]);
    $('.total_titulaie_temps_complet').text(totals[9]);
    $('.total_titulaie_femmes_complet').text(totals[10]);
    $('.total_titulaie_temps_partiel').text(totals[11]);
    $('.total_titulaie_femmes_partiel').text(totals[12]);
    $('.total_contrat_temps_complet').text(totals[13]);
    $('.total_contrat_femme_complet').text(totals[14]);
    $('.total_contrat_temps_pratiel').text(totals[15]);
    $('.total_contrat_femmes_pratiel').text(totals[16]);
}

function saveTableau_1_1(){

    if(!confirm("تأكيد حفظ وتقديم الجدول ؟")) return;

    $('#loadingOverlay').show();

    $.post('ajax/traitement_tab1_1.php', {

        action: 'save_tableau',
        statut: 'en_attente',
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
    
<?php }?>

<!--begin TABLEAU 2 -->
<?php if ($action == "add_tab2" || $action == "edit_tab2"){?>
<script>
$(document).ready(function(){


    // ==============================
    // AJOUT LIGNE AJAX
    // ==============================
    $(document).on('click','.add-consiel-btn',function(){

        let row = $(this).closest('tr');

        let formData = {
            action: 'add_detail',
            id_grade: row.find('[name="id_grade"]').val(),
            loi_consiel_employer: row.find('[name="loi_consiel_employer"]').val(),
            date_fin_consiel_employer: row.find('[name="date_fin_consiel_employer"]').val(),
            reference_consiel_employer_prolong: row.find('[name="reference_consiel_employer_prolong"]').val(),
            date_fin_consiel_employer_prolong: row.find('[name="date_fin_consiel_employer_prolong"]').val(),
            reference_consiel_recours: row.find('[name="reference_consiel_recours"]').val(),
            date_fin_consiel_recours: row.find('[name="date_fin_consiel_recours"]').val(),
            reference_consiel_recours_prolong: row.find('[name="reference_consiel_recours_prolong"]').val(),
            date_fin_consiel_recours_prolong: row.find('[name="date_fin_consiel_recours_prolong"]').val(),
            observations: row.find('[name="observations"]').val()
        };

        $.ajax({
            url: 'ajax/traitement_tab2.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response){

                if(response.success){

                    // Supprimer message noData
                    $('#noData').remove();

                    // Ajouter ligne dans tableau
                    let newRow = `
                        <tr data-id="${response.id}">
                            <td>${response.grade_name}</td>
                            <td>${formData.loi_consiel_employer}</td>
                            <td>${formData.date_fin_consiel_employer}</td>
                            <td>${formData.reference_consiel_employer_prolong}</td>
                            <td>${formData.date_fin_consiel_employer_prolong}</td>
                            <td>${formData.reference_consiel_recours}</td>
                            <td>${formData.date_fin_consiel_recours}</td>
                            <td>${formData.reference_consiel_recours_prolong}</td>
                            <td>${formData.date_fin_consiel_recours_prolong}</td>
                            <td>${formData.observations}</td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm delete-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    $('#existing_consiel').append(newRow);

                    // Reset inputs
                    row.find('input').val('');
                    row.find('select').val('').trigger('change');

                } else {
                    alert(response.message);
                }
            }
        });

    });


    // ==============================
    // SUPPRESSION AJAX
    // ==============================
    $(document).on('click','.delete-btn',function(){

        if(!confirm("تأكيد الحذف ؟")) return;

        let tr = $(this).closest('tr');
        let id = tr.data('id');

        $.ajax({
            url: 'ajax/traitement_tab2.php',
            type: 'POST',
            data: {
                action: 'delete_detail',
                id: id
            },
            dataType: 'json',
            success: function(response){

                if(response.success){
                    tr.remove();
                }else{
                    alert(response.message);
                }
            }
        });

    });

});


function saveTableau2(){

    if(!confirm("تأكيد حفظ وتقديم الجدول ؟")) return;

    $('#loadingOverlay').show();

    $.post('ajax/traitement_tab2.php', {

        action: 'save_tableau',
        statut: 'en_attente',
        id_user: <?php echo $current_user->id; ?>,
        id_societe: <?php echo $current_user->id_societe; ?>,
        annee: <?php echo $annee; ?>

    }, function(response){

        $('#loadingOverlay').hide();

        if(response.success){
            alert(response.message);
            window.location.href='?action=list_tab2';
        }else{
            alert(response.message);
        }

    }, 'json');
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

 <?php if ($action == "add_tab4" || $action == "edit_tab4"): ?>
<script>
// Variables pour le tableau 4
let compteurLignes4 = <?php echo isset($index) ? $index : 0; ?>;
let tousLesGrades = <?php echo json_encode($grades_js); ?>;

$(document).ready(function() {
    // Initialiser Select2 pour tous les selects de grade
    $('.select-grade').select2({
        placeholder: "ابحث أو اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl",
        language: { noResults: () => "لا توجد نتائج", searching: () => "جاري البحث..." }
    });

    // Mettre à jour le code et l'id_grade caché lors du changement de select
    $(document).on('change', '.select-grade', function() {
        const row = $(this).closest('tr');
        const selected = $(this).find('option:selected');
        const code = selected.data('code') || '';
        const idGrade = selected.val();
        row.find('.code-grade').val(code);
        row.find('input[name*="[id_grade]"]').val(idGrade);
    });

    // Calculer les totaux initiaux
    calculerTotaux4();
});

function ajouterLigne4() {
    const tbody = $('#tbody_details4');
    const index = compteurLignes4++;
    let options = '<option value="">اختر السلك</option>';
    tousLesGrades.forEach(g => options += `<option value="${g.id}" data-code="${g.code}">${g.designation}</option>`);

    const row = `
        <tr>
            <td>
                <input type="hidden" name="details[${index}][id]" value="0">
                <input type="hidden" name="details[${index}][id_grade]" value="">
                <input type="text" class="form-control text-center code-grade" readonly>
            </td>
            <td>
                <select name="details[${index}][id_grade_select]" class="form-control select-grade" required>${options}</select>
            </td>
            <td><input type="number" name="details[${index}][postes_vacants_externe]" class="form-control numeric-field" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][produit_formation_paramedicale]" class="form-control numeric-field" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][concours_sur_titre]" class="form-control numeric-field" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][debutants_contractuels]" class="form-control numeric-field" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][ouvriers_batiment_contractuels]" class="form-control numeric-field" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][methode_sur_titre]" class="form-control numeric-field" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][examen_mini]" class="form-control numeric-field" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][test_mini_ouvriers]" class="form-control numeric-field" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][postes_financiers_exploites]" class="form-control numeric-field" value="0" min="0"></td>
            <td><input type="number" name="details[${index}][nombre_postes_financiers_exploites]" class="form-control numeric-field" value="0" min="0"></td>
            <td><textarea name="details[${index}][observations]" class="form-control" rows="1"></textarea></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne4(this)"><i class="fas fa-trash"></i></button></td>
        </tr>
    `;
    tbody.append(row);
    // Réinitialiser Select2 pour la nouvelle ligne
    tbody.find('tr:last .select-grade').select2({
        placeholder: "ابحث أو اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl"
    });
}

function supprimerLigne4(btn) {
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
    calculerTotaux4();
}

function calculerTotaux4() {
    // Liste des champs numériques à additionner
    const fields = [
        'postes_vacants_externe',
        'produit_formation_paramedicale',
        'concours_sur_titre',
        'debutants_contractuels',
        'ouvriers_batiment_contractuels',
        'methode_sur_titre',
        'examen_mini',
        'test_mini_ouvriers',
        'postes_financiers_exploites',
        'nombre_postes_financiers_exploites'
    ];
    let totals = {};
    fields.forEach(f => totals[f] = 0);

    $('#tbody_details4 tr:visible').each(function() {
        fields.forEach(f => {
            let val = parseFloat($(this).find(`input[name*="[${f}]"]`).val()) || 0;
            totals[f] += val;
        });
    });

    // Mettre à jour l'affichage des totaux
    $('#total_postes_vacants4').text(totals['postes_vacants_externe']);
    $('#total_produit_formation4').text(totals['produit_formation_paramedicale']);
    $('#total_concours4').text(totals['concours_sur_titre']);
    $('#total_debutants4').text(totals['debutants_contractuels']);
    $('#total_ouvriers4').text(totals['ouvriers_batiment_contractuels']);
    $('#total_methode4').text(totals['methode_sur_titre']);
    $('#total_examen_mini4').text(totals['examen_mini']);
    $('#total_test_mini4').text(totals['test_mini_ouvriers']);
    $('#total_postes_financiers4').text(totals['postes_financiers_exploites']);
    $('#total_nombre_postes4').text(totals['nombre_postes_financiers_exploites']);
}

function enregistrerBrouillon4() {
    const form = $('#formulaireTableau4')[0];
    const input = $('<input>').attr({ type: 'hidden', name: 'statut', value: 'brouillon' });
    $(form).append(input);
    if (confirm('هل تريد حفظ الجدول كمسودة؟')) {
        soumettreFormulaire4(form, 'brouillon');
    } else {
        input.remove();
    }
}

// Gestion de la soumission du formulaire principal tableau 4
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
        soumettreFormulaire4(this, 'validé');
    }
});

function soumettreFormulaire4(form, statut) {
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
                showMessage4(data.message, 'success');
                setTimeout(() => {
                    if (statut == 'brouillon') window.location.reload();
                    else window.location.href = '?action=list_tab4&success=1';
                }, 1500);
            } else {
                showMessage4(data.message, 'danger');
                submitBtn.html(originalText).prop('disabled', false);
            }
        })
        .catch(() => {
            showMessage4('حدث خطأ أثناء الاتصال بالخادم', 'danger');
            submitBtn.html(originalText).prop('disabled', false);
        });
}

function showMessage4(msg, type) {
    const alert = $('<div class="alert alert-'+type+' alert-dismissible fade show" role="alert">'+msg+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    $('.app-content .container-fluid').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
}
</script>
<?php endif; ?>

<?php if ($action == "add_tab4_1" || $action == "edit_tab4_1"){ ?>
<script>
    // Variables globales pour les deux formulaires
let compteurLignes4 = <?php echo isset($index) ? $index : 0; ?>;
let compteurLignes4_1 = <?php echo isset($index) ? $index : 0; ?>;
let tousLesGrades = <?php echo json_encode($grades_js); ?>;

$(document).ready(function() {
    // Initialiser Select2 pour tous les selects
    $('.select-grade').select2({
        placeholder: "ابحث أو اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl",
        language: { noResults: () => "لا توجد نتائج", searching: () => "جاري البحث..." }
    });
    
    // Gestion des changements de select
    $(document).on('change', '.select-grade', function() {
        const row = $(this).closest('tr');
        const selected = $(this).find('option:selected');
        const code = selected.data('code') || '';
        const idGrade = selected.val();
        row.find('.code-grade').val(code);
        row.find('input[name*="[id_grade]"]').val(idGrade);
    });
    
    // Calculer les totaux si on est sur le formulaire correspondant
    if ($('#tbody_details4').length) calculerTotaux4();
    if ($('#tbody_details4_1').length) calculerTotaux4_1();
});


// Fonctions pour l'annexe 4/1

// Fonctions JavaScript spécifiques à l'annexe 4/1 (à ajouter dans la section <script>)
function ajouterLigne4_1() {
    const tbody = $('#tbody_details4_1');
    const index = compteurLignes4_1++;
    let options = '<option value="">اختر السلك</option>';
    tousLesGrades.forEach(g => options += `<option value="${g.id}" data-code="${g.code}">${g.designation}</option>`);

    // Générer les champs numériques
    let numericHtml = '';
    <?php foreach ($numeric_fields as $field): ?>
        numericHtml += `<td><input type="number" name="details[${index}][<?php echo $field; ?>]" class="form-control numeric-field" value="0" min="0"></td>`;
    <?php endforeach; ?>

    const row = `
        <tr>
          
                <input type="hidden" name="details[${index}][id]" value="0">
                <input type="hidden" name="details[${index}][id_grade]" value="">
                <input type="text" class="form-control text-center code-grade" readonly>
            
            <td>
                <select name="details[${index}][id_grade_select]" class="form-control select-grade" required>${options}</select>
            </td>
            <td><input type="text" name="details[${index}][categorie]" class="form-control"></td>
            <td><input type="number" name="details[${index}][num_categorie]" class="form-control" value="0" min="0"></td>
            ${numericHtml}
            <td><textarea name="details[${index}][observation]" class="form-control" rows="1"></textarea></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne4_1(this)"><i class="fas fa-trash"></i></button></td>
        </tr>
    `;
    tbody.append(row);
    tbody.find('tr:last .select-grade').select2({ placeholder: "ابحث أو اختر...", allowClear: true, width: '100%', dir: "rtl" });
}

function supprimerLigne4_1(btn) {
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
    calculerTotaux4_1();
}

function calculerTotaux4_1() {
    // Initialiser les totaux pour chaque champ numérique
    let totals = {};
    <?php foreach ($numeric_fields as $field): ?>
        totals['<?php echo $field; ?>'] = 0;
    <?php endforeach; ?>

    $('#tbody_details4_1 tr:visible').each(function() {
        <?php foreach ($numeric_fields as $field): ?>
            totals['<?php echo $field; ?>'] += parseFloat($(this).find('input[name*="[<?php echo $field; ?>]"]').val()) || 0;
        <?php endforeach; ?>
    });

    // Mettre à jour l'affichage
    <?php foreach ($numeric_fields as $field): ?>
        $('#total_<?php echo $field; ?>').text(totals['<?php echo $field; ?>']);
    <?php endforeach; ?>
}

function enregistrerBrouillon4_1() {
    const form = $('#formulaireTableau4_1')[0];
    const input = $('<input>').attr({ type: 'hidden', name: 'statut', value: 'brouillon' });
    $(form).append(input);
    if (confirm('هل تريد حفظ الملحق كمسودة؟')) {
        soumettreFormulaire4_1(form, 'brouillon');
    } else {
        input.remove();
    }
}
// Gestion de la soumission
document.getElementById('formulaireTableau4_1')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirm('هل أنت متأكد من رغبتك في حفظ الجدول؟')) {
        // Mettre à jour les id_grade à partir des selects
        const selects = this.querySelectorAll('.select-grade');
        selects.forEach(select => {
            const row = select.closest('tr');
            const hidden = row.querySelector('input[name*="[id_grade]"]');
            if (hidden) hidden.value = select.value;
        });
        soumettreFormulaire4_1(this, 'validé');
    }
});
function soumettreFormulaire4_1(form, statut) {
    const formData = new FormData(form);
    const submitBtn = $(form).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...').prop('disabled', true);

    fetch('ajax/traitement_tab4_1.php', {
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




// Fonctions communes
function showMessage(msg, type) {
    const alert = $('<div class="alert alert-'+type+' alert-dismissible fade show" role="alert">'+msg+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    $('.app-content .container-fluid').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
}

function supprimerTableau4(id) {
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

function supprimerTableau4_1(id) {
    if (confirm('هل أنت متأكد من حذف هذا الملحق؟')) {
        fetch('ajax/traitement_tab4_1.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=delete_tab4_1&id='+id })
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
