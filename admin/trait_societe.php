<?php
require_once('../includes/initialiser.php');

// Vérifie si l'utilisateur est connecté
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}

// Récupère les infos de l'utilisateur connecté
$current_user = Accounts::trouve_par_id($session->id_utilisateur);

if (!$current_user) {
    $session->logout();
    redirect_to('../login.php');
}

// Vérifie si l'utilisateur est bien un administrateur
if ($current_user->type !== 'administrateur') {
    $session->logout();
    redirect_to('../login.php');
}
?>
<?php

$titre = "المؤسسات ";

$active_menu = "societe";

$active_submenu = "societe";

$header = array('todo');

if ($current_user->type == "administrateur") {
	if (isset($_GET['action']) && $_GET['action'] == 'open') {
		$action = 'open';
	} else if (isset($_GET['action']) && $_GET['action'] == 'close_societe') {
		$action = 'close_societe';
	}
	if ($action == 'open') {
		$errors = array();
		// verification de données 	
		if (isset($_POST['id']) || !empty($_POST['id'])) {
			$id = intval($_POST['id']);
			$nsociete = Societe::trouve_par_id($id);
		} elseif (isset($_GET['id']) || !empty($_GET['id'])) {
			$id = intval($_GET['id']);
			$nsociete = Societe::trouve_par_id($id);
		}
            if ($nsociete){
        // perform Update
    $session->set_societe($nsociete->id_societe);
    readresser_a("index.php");
    }
		
	}
	if ($action == 'close_societe') {
		$session->delete_societe();
		readresser_a("index.php");
	} 
}

if ($current_user->type =='administrateur' or $current_user->type =='utilisateur'){

	require_once("composit/header.php");
}

?>
<!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <h2 class="fw-bold text-primary mb-2">
                    <i class="fas fa-building me-3"></i>قائمة المؤسسات
                </h2>
                <p class="text-muted">إدارة وعرض جميع المؤسسات المسجلة في النظام</p>
              </div>
              <div class="col-sm-6">
              <button class="btn btn-primary btn-lg float-sm-end" data-bs-toggle="modal" data-bs-target="#ajouterSocieteModal">
                                        <i class="fas fa-plus me-2"></i>إضافة مؤسسة جديدة
                                    </button>
                
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-12">
                <!-- Default box -->
                  <!-- Section des Sociétés -->
                  <section class=" bg-light">   
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-white py-3 border-0">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="ابحث عن شركة...">
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary active" data-filter="all">الكل</button>
                                            <button type="button" class="btn btn-outline-primary" data-filter="active">نشطة</button>
                                            <button type="button" class="btn btn-outline-primary" data-filter="inactive">غير نشطة</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="societesTable" class="table table-hover mb-0" dir="rtl">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="50">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                                    </div>
                                                </th>
                                                <th class="ps-4">اسم الشركة</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>الهاتف</th>
                                                <th>المدينة</th>
                                                <th>الحالة</th>
                                                <th class="text-center">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            // Exemple de données - Remplacez par vos données réelles
                                            $societes = Societe::trouve_tous();
                                            
                                            foreach ($societes as $societe):
                                                $statutClass = $societe->etat == 'active' ? 'success' : 'danger';
                                                $statutText = $societe->etat == 'active' ? 'نشطة' : 'غير نشطة';
                                            ?>
                                            <tr class="align-middle" data-status="<?php echo $societe->etat; ?>">
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input row-checkbox" type="checkbox" value="<?php echo $societe->id_societe; ?>">
                                                    </div>
                                                </td>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-3">
                                                            <div class="avatar-initial bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <i class="fas fa-building fs-5"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-semibold"><?php echo $societe->raison_ar; ?></h6>
                                                            <small class="text-muted">ID: #<?php echo str_pad($societe->id_societe, 4, '0', STR_PAD_LEFT); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <i class="fas fa-envelope me-2 text-muted"></i>
                                                    <?php echo $societe->email; ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-phone me-2 text-muted"></i>
                                                    <?php echo $societe->tel1; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?php if (isset($societe->wilayas)){$ville =Wilayas::trouve_par_id($societe->wilayas); echo $ville->nom ;}; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $statutClass; ?> bg-opacity-10 text-<?php echo $statutClass; ?>">
                                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                                        <?php echo $statutText; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary" 
                                                            onclick="afficherDetailsSociete(<?php echo $societe->id_societe; ?>)"
                                                            data-bs-toggle="tooltip" 
                                                            title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <!-- Bouton Modifier -->
                                                    <button type="button" 
                                                            class="btn btn-outline-success" 
                                                            onclick="modifierSociete(<?php echo $societe->id_societe; ?>)"
                                                            data-bs-toggle="tooltip" 
                                                            title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <!-- Bouton Supprimer -->
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="supprimerSociete(<?php echo $societe->id_societe; ?>)"
                                                            data-bs-toggle="tooltip" 
                                                            title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer bg-white py-3 border-0">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <span class="me-3 text-muted">عدد النتائج:</span>
                                            <select id="rowsPerPage" class="form-select form-select-sm w-auto">
                                                <option value="5">5</option>
                                                <option value="10" selected>10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination justify-content-end mb-0">
                                                <li class="page-item disabled">
                                                    <a class="page-link" href="#" tabindex="-1">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions en masse -->
                        <div class="mt-3 d-none" id="bulkActions">
                            <div class="card border-primary">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-primary fw-semibold me-3" id="selectedCount">0</span>
                                            <span class="text-muted">شركة محددة</span>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-success me-2">
                                                <i class="fas fa-check me-1"></i>تفعيل
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning me-2">
                                                <i class="fas fa-ban me-1"></i>تعطيل
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash me-1"></i>حذف
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                </section>

                <!-- Style CSS additionnel -->


                <!-- JavaScript pour la fonctionnalité -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Sélectionner tous
                    const selectAll = document.getElementById('selectAll');
                    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
                    const bulkActions = document.getElementById('bulkActions');
                    const selectedCount = document.getElementById('selectedCount');
                    const searchInput = document.getElementById('searchInput');
                    const filterButtons = document.querySelectorAll('[data-filter]');
                    
                    // Gérer la sélection de toutes les cases
                    selectAll.addEventListener('change', function() {
                        rowCheckboxes.forEach(checkbox => {
                            checkbox.checked = selectAll.checked;
                        });
                        updateBulkActions();
                    });
                    
                    // Gérer la sélection individuelle
                    rowCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', updateBulkActions);
                    });
                    
                    function updateBulkActions() {
                        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
                        const count = checkedBoxes.length;
                        
                        selectedCount.textContent = count;
                        
                        if (count > 0) {
                            bulkActions.classList.remove('d-none');
                        } else {
                            bulkActions.classList.add('d-none');
                        }
                        
                        // Décocher "selectAll" si pas toutes sélectionnées
                        selectAll.checked = (count === rowCheckboxes.length) && (rowCheckboxes.length > 0);
                    }
                    
                    // Filtrer par statut
                    filterButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const filter = this.getAttribute('data-filter');
                            
                            // Mettre à jour les boutons actifs
                            filterButtons.forEach(btn => btn.classList.remove('active'));
                            this.classList.add('active');
                            
                            // Filtrer les lignes
                            const rows = document.querySelectorAll('#societesTable tbody tr');
                            
                            rows.forEach(row => {
                                if (filter === 'all') {
                                    row.style.display = '';
                                } else {
                                    const status = row.getAttribute('data-status');
                                    row.style.display = (status === filter) ? '' : 'none';
                                }
                            });
                        });
                    });
                    
                    // Recherche
                    searchInput.addEventListener('keyup', function() {
                        const searchTerm = this.value.toLowerCase();
                        const rows = document.querySelectorAll('#societesTable tbody tr');
                        
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            row.style.display = text.includes(searchTerm) ? '' : 'none';
                        });
                    });
                    
                    // Initialiser les tooltips Bootstrap
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                });
                </script>
                
                <!-- /.card -->
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      
<script>
// Fonction pour afficher les détails d'une société
function afficherDetailsSociete(id) {
    // Créer un modal pour afficher les détails
    const modalHTML = `
        <div class="modal fade" id="detailsSocieteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" dir="rtl">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-info-circle me-2"></i>تفاصيل المؤسسة
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="detailsSocieteContent">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <p class="mt-2">جاري تحميل التفاصيل...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="button" class="btn btn-primary" onclick="modifierSociete(${id})">
                            <i class="fas fa-edit me-1"></i>تعديل
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter le modal au DOM
    if (!document.getElementById('detailsSocieteModal')) {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    // Charger les détails via AJAX
    fetch(`ajax/traitement_societe.php?action=details&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const content = document.getElementById('detailsSocieteContent');
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            ${data.logo ? 
                                `<img src="../uploads/logos/${data.logo}" 
                                      class="img-fluid rounded-circle border" 
                                      style="width: 150px; height: 150px; object-fit: cover;">` : 
                                `<div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                      style="width: 150px; height: 150px;">
                                    <i class="fas fa-building fa-3x text-secondary"></i>
                                </div>`
                            }
                        </div>
                        <div class="col-md-8">
                            <h4 class="text-primary">${data.raison_ar}</h4>
                            <h6 class="text-muted">${data.raison_fr}</h6>
                            <hr>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-map-marker-alt me-2"></i>العنوان:</strong>
                                    <p class="mt-1">${data.adresse_ar}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-city me-2"></i>المدينة:</strong>
                                    <p class="mt-1">${data.ville_ar}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-flag me-2"></i>الولاية:</strong>
                                    <p class="mt-1">${data.wilaya_nom}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-barcode me-2"></i>الرمز البريدي:</strong>
                                    <p class="mt-1">${data.postal || '---'}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-phone me-2"></i>الهاتف 1:</strong>
                                    <p class="mt-1">${data.tel1}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-phone-alt me-2"></i>الهاتف 2:</strong>
                                    <p class="mt-1">${data.tel2 || '---'}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-fax me-2"></i>الفاكس:</strong>
                                    <p class="mt-1">${data.fax || '---'}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-mobile-alt me-2"></i>الجوال 1:</strong>
                                    <p class="mt-1">${data.mob1 || '---'}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-envelope me-2"></i>البريد الإلكتروني:</strong>
                                    <p class="mt-1">${data.email || '---'}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-globe me-2"></i>الموقع الإلكتروني:</strong>
                                    <p class="mt-1">${data.web ? `<a href="${data.web}" target="_blank">${data.web}</a>` : '---'}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-calendar-alt me-2"></i>تاريخ التسجيل:</strong>
                                    <p class="mt-1">${data.date_creation_formatted}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-chart-line me-2"></i>السنة المالية:</strong>
                                    <p class="mt-1">${data.exercice_debut} - ${data.exercice_fin}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <strong><i class="fas fa-tag me-2"></i>الحالة:</strong>
                                    <span class="badge bg-${data.etat_class} mt-1">${data.etat_text}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('detailsSocieteContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.message || 'حدث خطأ في تحميل التفاصيل'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailsSocieteContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    حدث خطأ في الاتصال بالخادم
                </div>
            `;
        });
    
    // Afficher le modal
    const modal = new bootstrap.Modal(document.getElementById('detailsSocieteModal'));
    modal.show();
}

// Fonction pour modifier une société
function modifierSociete(id) {
    window.location.href = `modifier_societe.php?id=${id}`;
}

// Fonction pour supprimer une société
function supprimerSociete(id) {
    if (confirm('هل أنت متأكد من حذف هذه الشركة؟ لا يمكن التراجع عن هذه العملية.')) {
        const formData = new FormData();
        formData.append('id_societe', id);
        
        fetch('ajax/traitement_societe.php?action=supprimer', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء الاتصال بالخادم');
        });
    }
}

// Gestion de la table avec les boutons de filtrage par état
document.addEventListener('DOMContentLoaded', function() {
    // ... (votre code existant pour la sélection multiple et la recherche)
    
    // Mettre à jour les boutons de filtrage pour inclure tous les états
    const filterButtons = document.querySelectorAll('[data-filter]');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Mettre à jour les boutons actifs
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrer les lignes
            const rows = document.querySelectorAll('#societesTable tbody tr');
            
            rows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    const status = row.getAttribute('data-status');
                    row.style.display = (status === filter) ? '' : 'none';
                }
            });
        });
    });
    
    // Initialiser les tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
    <!-- Modal Ajouter Société -->
<div class="modal fade" id="ajouterSocieteModal" tabindex="-1" aria-labelledby="ajouterSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" dir="rtl">
            <form id="formAjouterSociete" action="ajax/traitement_societe.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="ajouterSocieteModalLabel">
                        <i class="fas fa-building me-2"></i>إضافة مؤسسة جديدة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Messages d'erreur/succès -->
                    <div id="messageAlert" class="alert d-none"></div>
                    
                    <!-- Onglets pour organiser le formulaire -->
                    <ul class="nav nav-tabs mb-4" id="societeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-generales-tab" data-bs-toggle="tab" data-bs-target="#info-generales" type="button" role="tab">
                                <i class="fas fa-info-circle me-1"></i> المعلومات الأساسية
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                <i class="fas fa-address-book me-1"></i> معلومات الاتصال
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="logo-tab" data-bs-toggle="tab" data-bs-target="#logo" type="button" role="tab">
                                <i class="fas fa-image me-1"></i> الشعار والإعدادات
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="societeTabContent">
                        <!-- Informations Générales -->
                        <div class="tab-pane fade show active" id="info-generales" role="tabpanel">
                            <div class="row g-3">                                
                                <div class="col-6">
                                    <label for="raison_ar" class="form-label">إسم المؤسسة بالعربية <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="raison_ar" name="raison_ar" required placeholder="أدخل إسم المؤسسة ">
                                </div>
                                <div class="col-6">
                                    <label for="raison_fr" class="form-label">إسم المؤسسة الفرنسية <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="raison_fr" name="raison_fr" required placeholder="أدخل إسم المؤسسة ">
                                </div>
                                
                                <div class="col-12">
                                    <label for="adresse_ar" class="form-label">العنوان بالعربية <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="adresse_ar" name="adresse_ar" rows="2" required placeholder="العنوان الكامل"></textarea>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="wilayas" class="form-label">الولاية <span class="text-danger">*</span></label>
                                    <select class="form-select" id="wilayas" name="wilayas" required>
                                        <option value="">اختر الولاية</option>
                                       <?php $villes = Wilayas::trouve_tous(); foreach ($villes as $ville){?>
                                        <option value="<?php echo $ville->id;?>"><?php echo $ville->nom; ?></option>
                                        <?php }?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="postal" class="form-label">الرمز البريدي</label>
                                    <input type="text" class="form-control" id="postal" name="postal" placeholder="مثال: 16000">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations de Contact -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="tel1" class="form-label">الهاتف 1 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">+213</span>
                                        <input type="tel" class="form-control" id="tel1" name="tel1" required placeholder="مثال: 0550123456">
                                        <div class="invalid-feedback">
                                            رقم الهاتف غير صالح (0550XXXXXX)
                                        </div>
                                        <div class="valid-feedback">
                                            رقم صحيح
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="tel2" class="form-label">الهاتف 2</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+213</span>
                                        <input type="tel" class="form-control" id="tel2" name="tel2" placeholder="رقم إضافي (اختياري)">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="fax" class="form-label">الفاكس</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+213</span>
                                        <input type="tel" class="form-control" id="fax" name="fax" placeholder="رقم الفاكس">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="mob1" class="form-label">الجوال 1</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+213</span>
                                        <input type="tel" class="form-control" id="mob1" name="mob1" placeholder="مثال: 0770123456">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="mob2" class="form-label">الجوال 2</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+213</span>
                                        <input type="tel" class="form-control" id="mob2" name="mob2" placeholder="رقم جوال إضافي">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">البريد الإلكتروني</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="example@domain.com">
                                </div>
                            </div>
                        </div> 
                        
                        <!-- Logo et Paramètres -->
                        <div class="tab-pane fade" id="logo" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="logo" class="form-label">شعار الشركة</label>
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <img id="logoPreview" src="../assets/images/default-company.png" alt="معاينة الشعار" class="img-fluid rounded" style="max-height: 150px;">
                                            </div>
                                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*" onchange="previewLogo(event)">
                                            <div class="form-text">الصور المسموح بها: JPG, PNG, GIF. الحجم الأقصى: 2MB</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="etat" class="form-label">حالة الشركة</label>
                                    <select class="form-select" id="etat" name="etat">
                                        <option value="active" selected>نشطة</option>
                                        <option value="inactive">غير نشطة</option>
                                        <option value="pending">قيد المراجعة</option>
                                        <option value="suspended">موقوفة مؤقتاً</option>
                                    </select>
                                    
                                    <div class="mt-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" checked>
                                            <label class="form-check-label" for="newsletter">
                                                إرسال إشعارات عبر البريد الإلكتروني
                                            </label>
                                        </div>
                                        
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="auto_generate" name="auto_generate" checked>
                                            <label class="form-check-label" for="auto_generate">
                                                إنشاء حسابات تلقائية للمدراء
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>ملاحظات هامة:</h6>
                                        <ul class="mb-0">
                                            <li>الحقول التي تحمل علامة (<span class="text-danger">*</span>) إلزامية</li>
                                            <li>يجب أن تكون صورة الشعار بخلفية شفافة إن أمكن</li>
                                            <li>سيتم إنشاء رقم فريد للشركة تلقائياً بعد الحفظ</li>
                                            <li>يمكن تعديل جميع المعلومات لاحقاً من لوحة التحكم</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnResetForm">
                        <i class="fas fa-redo me-1"></i> مسح النموذج
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="fas fa-save me-1"></i> حفظ الشركة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



      <!--begin::Footer-->
    <?php require_once("composit/footer.php");?>
