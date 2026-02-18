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

$titre = "الرئيسية ";

$active_menu = "index";

$active_submenu = "index";

$header = array('todo');

if ($current_user->type =='administrateur' or $current_user->type =='utilisateur'){

	require_once("composit/header.php");
}


// Exercices
$exercice_actif = Exercice::get_exercice_actif();
$annee_courante = $exercice_actif ? $exercice_actif->annee : date('Y');



?>
<!-- Style CSS additionnel -->
<style>
    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .card.bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-xxl {
        width: 100px;
        height: 100px;
    }
    
    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.8em;
    }
    
    .btn-outline-primary, .btn-outline-success, .btn-outline-info, 
    .btn-outline-warning, .btn-outline-secondary, .btn-outline-danger {
        border-width: 2px;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.03);
    }
    
    .border-start {
        border-left-width: 4px !important;
    }
</style>
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
                    <i class="fas fa-building me-3"></i>إدارة  <?php if (isset($nav_societe) ){echo $nav_societe->raison_ar ;}else{ echo 'المؤسسات';} ?>
                </h2>
                <p class="text-muted">إدارة وعرض جميع المؤسسات المسجلة في النظام</p>
              </div>
              <div class="col-sm-6">                
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">الرئيسية</a></li>
                  <li class="breadcrumb-item active" aria-current="page"><?php if (isset($nav_societe) ){echo $nav_societe->raison_ar ;} ?></li>
                </ol>
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
                  <?php if (isset($nav_societe) ){ ?>
                  <!-- Message de bienvenue -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h4>مرحباً بك، <?php echo $current_user->prenom . ' ' . $current_user->nom; ?></h4>
                                <p class="mb-0"><i class="fas fa-calendar-alt me-2"></i> السنة المالية الحالية: <?php echo $annee_courante; ?></p>
                                <?php if ($exercice_actif): ?>
                                    <small><?php echo "من " . date('d/m/Y', strtotime($exercice_actif->date_debut)) . " إلى " . date('d/m/Y', strtotime($exercice_actif->date_fin)); ?></small>
                                <?php endif; ?>
                            </div>
                            <div>
                                <i class="fas fa-chart-pie fa-4x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                             <!-- Liens d'administration rapides -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h3 class="card-title"><i class="fas fa-cogs me-2"></i> الإدارة</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-6 mb-3">
                                    <a href="societes.php" class="btn btn-outline-primary w-100 py-3">
                                        <i class="fas fa-building fa-2x mb-2"></i>
                                        <br>إدارة المؤسسات
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <a href="utilisateurs.php" class="btn btn-outline-success w-100 py-3">
                                        <i class="fas fa-user-cog fa-2x mb-2"></i>
                                        <br>إدارة المستخدمين
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <a href="grades.php" class="btn btn-outline-info w-100 py-3">
                                        <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                        <br>الدرجات والوظائف
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <a href="exercices.php" class="btn btn-outline-warning w-100 py-3">
                                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                        <br>السنوات المالية
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <a href="parametres.php" class="btn btn-outline-secondary w-100 py-3">
                                        <i class="fas fa-sliders-h fa-2x mb-2"></i>
                                        <br>الإعدادات العامة
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <a href="logs.php" class="btn btn-outline-danger w-100 py-3">
                                        <i class="fas fa-history fa-2x mb-2"></i>
                                        <br>سجل النشاطات
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <a href="sauvegarde.php" class="btn btn-outline-dark w-100 py-3">
                                        <i class="fas fa-database fa-2x mb-2"></i>
                                        <br>النسخ الاحتياطي
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <a href="profile.php" class="btn btn-outline-primary w-100 py-3">
                                        <i class="fas fa-id-card fa-2x mb-2"></i>
                                        <br>ملفي الشخصي
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                   
                    <?php 	} else{ ?>   
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-white py-3 border-0">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="ابحث عن مؤسسة...">
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
                                                <th class="ps-4"> المؤسسة</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>الهاتف</th>
                                                <th>المدينة</th>
                                                <th>الحالة</th>
                                                <th class="text-center">فتح المؤسسة</th>
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
                                                    <a type="button"  href="trait_societe.php?id=<?php if(isset($societe->id_societe)) {echo $societe->id_societe; }?>&action=open"
                                                            class="btn btn-outline-primary"                                                           
                                                            data-bs-toggle="tooltip" 
                                                            title=" فتح المؤسسة">
                                                        <i class="fas fa-folder-open"></i>
                                            </a>                                                    
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
                        <?php }?>
                    
                </section>
                
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
    <!--begin::Footer-->
    <?php require_once("composit/footer.php");?>
