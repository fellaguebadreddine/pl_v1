<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'softdz38_pl';
$username = 'softdz38_pl_user';
$password = '4sN*{7#ld9Gy0phs';

try {
   $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_query = "";
if (!empty($search)) {
    $search_query = "WHERE (id LIKE :search OR id_societe LIKE :search OR annee LIKE :search 
                    OR statut LIKE :search OR id_user LIKE :search OR total LIKE :search 
                    OR `total-reel` LIKE :search OR total_intrim LIKE :search OR total_femmes LIKE :search)";
}

// Statut filter
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';
if (!empty($statut_filter)) {
    $search_query .= (!empty($search_query) ? " AND" : " WHERE") . " statut = :statut";
}

// Tri
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';

// Compter le total des enregistrements
$count_sql = "SELECT COUNT(*) FROM votre_table $search_query";
$stmt_count = $pdo->prepare($count_sql);

if (!empty($search)) {
    $search_param = "%$search%";
    $stmt_count->bindValue(':search', $search_param);
}
if (!empty($statut_filter)) {
    $stmt_count->bindValue(':statut', $statut_filter);
}

$stmt_count->execute();
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Récupérer les données
$sql = "SELECT * FROM votre_table $search_query ORDER BY $sort $order LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
if (!empty($statut_filter)) {
    $stmt->bindValue(':statut', $statut_filter);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll();

// Statistiques
$total_stats = $pdo->query("SELECT SUM(total) as total, SUM(`total-reel`) as total_reel, SUM(total_intrim) as total_intrim, SUM(total_femmes) as total_femmes FROM votre_table")->fetch();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - البيانات</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
            margin-bottom: 20px;
        }
        
        .card-header {
            font-weight: 600;
            color: #fff;
        }
        
        .bg-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .bg-success-custom {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
        }
        
        .bg-info-custom {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card {
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .badge {
            font-size: 0.85em;
            padding: 0.5em 0.8em;
        }
        
        .badge-brouillon { background-color: #6c757d; }
        .badge-en-attente { background-color: #ffc107; color: #000; }
        .badge-valide { background-color: #28a745; }
        .badge-rejete { background-color: #dc3545; }
        
        .action-btn {
            margin: 2px;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: #667eea;
            color: white;
            font-weight: 600;
            border: none;
        }
        
        .table tbody tr:hover {
            background-color: #f0f4ff;
        }
        
        .pagination .page-link {
            color: #667eea;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .search-box input {
            padding-right: 40px;
        }
        
        .filter-select {
            min-width: 150px;
        }
        
        .btn-export {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            border: none;
        }
        
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(238, 90, 36, 0.4);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary-custom border-0 text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0"><i class="fas fa-database me-2"></i>لوحة البيانات</h2>
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-sm" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>طباعة
                                </button>
                                <button class="btn btn-light btn-sm" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                                <button class="btn btn-light btn-sm" onclick="exportToPDF()">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-0">
                    <div class="card-body text-center p-3">
                        <div class="stat-number"><?= number_format($total_stats['total'] ?? 0, 0, ',', ' ') ?></div>
                        <div class="stat-label">المجموع الكلي</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-0">
                    <div class="card-body text-center p-3">
                        <div class="stat-number"><?= number_format($total_stats['total_reel'] ?? 0, 0, ',', ' ') ?></div>
                        <div class="stat-label">المجموع الفعلي</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-0">
                    <div class="card-body text-center p-3">
                        <div class="stat-number"><?= number_format($total_stats['total_intrim'] ?? 0, 0, ',', ' ') ?></div>
                        <div class="stat-label">المجموع المؤقت</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-0">
                    <div class="card-body text-center p-3">
                        <div class="stat-number"><?= number_format($total_stats['total_femmes'] ?? 0, 0, ',', ' ') ?></div>
                        <div class="stat-label">مجموع النساء</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et Recherche -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="search-box">
                    <input type="text" class="form-control" id="searchInput" placeholder="البحث..." value="<?= htmlspecialchars($search) ?>">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <div class="col-md-3">
                <select class="form-select filter-select" id="statutFilter">
                    <option value="">جميع الحالات</option>
                    <option value="brouillon" <?= $statut_filter === 'brouillon' ? 'selected' : '' ?>>مسودة</option>
                    <option value="en_attente" <?= $statut_filter === 'en_attente' ? 'selected' : '' ?>>قيد الانتظار</option>
                    <option value="valide" <?= $statut_filter === 'valide' ? 'selected' : '' ?>>معتمد</option>
                    <option value="rejete" <?= $statut_filter === 'rejete' ? 'selected' : '' ?>>مرفوض</option>
                </select>
            </div>
            
            <div class="col-md-5 text-end">
                <small class="text-muted">إجمالي السجلات: <strong><?= $total_records ?></strong></small>
            </div>
        </div>

        <!-- Tableau de données -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary-custom">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i>بيانات الجدول</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="dataTable">
                                <thead>
                                    <tr>
                                        <th style="cursor: pointer;" onclick="sortTable('id')">
                                            # <i class="fas fa-sort"></i>
                                        </th>
                                        <th style="cursor: pointer;" onclick="sortTable('id_societe')">
                                            معرف الشركة <i class="fas fa-sort"></i>
                                        </th>
                                        <th style="cursor: pointer;" onclick="sortTable('annee')">
                                            السنة <i class="fas fa-sort"></i>
                                        </th>
                                        <th style="cursor: pointer;" onclick="sortTable('statut')">
                                            الحالة <i class="fas fa-sort"></i>
                                        </th>
                                        <th style="cursor: pointer;" onclick="sortTable('date_valide')">
                                            تاريخ التحقق <i class="fas fa-sort"></i>
                                        </th>
                                        <th style="cursor: pointer;" onclick="sortTable('id_user')">
                                            معرف المستخدم <i class="fas fa-sort"></i>
                                        </th>
                                        <th style="cursor: pointer;" onclick="sortTable('total')">
                                            المجموع <i class="fas fa-sort"></i>
                                        </th>
                                        <th style="cursor: pointer;" onclick="sortTable('total-reel')">
                                            المجموع الفعلي <i class="fas fa-sort"></i>
                                        </th>
                                        <th style="cursor: pointer;" onclick="sortTable('total_intrim')">
                                            المجموع المؤقت <i class="fas fa-sort"></i>
                                        </th>
                                        <th style="cursor: pointer;" onclick="sortTable('total_femmes')">
                                            مجموع النساء <i class="fas fa-sort"></i>
                                        </th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($data) > 0): ?>
                                        <?php foreach ($data as $row): ?>
                                            <tr>
                                                <td><strong><?= $row['id'] ?></strong></td>
                                                <td><?= $row['id_societe'] ?></td>
                                                <td><span class="badge bg-info"><?= $row['annee'] ?></span></td>
                                                <td>
                                                    <?php
                                                    $statut_classes = [
                                                        'brouillon' => 'badge-brouillon',
                                                        'en_attente' => 'badge-en-attente',
                                                        'valide' => 'badge-valide',
                                                        'rejete' => 'badge-rejete'
                                                    ];
                                                    $statut_text = [
                                                        'brouillon' => 'مسودة',
                                                        'en_attente' => 'قيد الانتظار',
                                                        'valide' => 'معتمد',
                                                        'rejete' => 'مرفوض'
                                                    ];
                                                    $statut_class = $statut_classes[$row['statut']] ?? 'badge-secondary';
                                                    $statut_label = $statut_text[$row['statut']] ?? $row['statut'];
                                                    ?>
                                                    <span class="badge <?= $statut_class ?>"><?= $statut_label ?></span>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($row['date_valide'])) ?></td>
                                                <td><?= $row['id_user'] ?></td>
                                                <td><?= number_format($row['total'], 0, ',', ' ') ?></td>
                                                <td><?= number_format($row['total-reel'], 0, ',', ' ') ?></td>
                                                <td><?= number_format($row['total_intrim'], 0, ',', ' ') ?></td>
                                                <td><?= number_format($row['total_femmes'], 0, ',', ' ') ?></td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <button class="btn btn-sm btn-primary action-btn" title="عرض التفاصيل" onclick="viewDetails(<?= $row['id'] ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-warning action-btn" title="تعديل" onclick="editRecord(<?= $row['id'] ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger action-btn" title="حذف" onclick="deleteRecord(<?= $row['id'] ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-5">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                <p class="mb-0">لا توجد بيانات لعرضها</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="row mt-4">
            <div class="col-12">
                <nav aria-label="Pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&statut=<?= urlencode($statut_filter) ?>">
                                    <i class="fas fa-chevron-right"></i> السابق
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?= $i ?></span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&statut=<?= urlencode($statut_filter) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&statut=<?= urlencode($statut_filter) ?>">
                                    التالي <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Recherche en temps réel
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const search = this.value;
            const statut = document.getElementById('statutFilter').value;
            window.location.href = `?search=${encodeURIComponent(search)}&statut=${encodeURIComponent(statut)}`;
        });

        // Filtre par statut
        document.getElementById('statutFilter').addEventListener('change', function() {
            const search = document.getElementById('searchInput').value;
            const statut = this.value;
            window.location.href = `?search=${encodeURIComponent(search)}&statut=${encodeURIComponent(statut)}`;
        });

        // Fonctions d'action
        function viewDetails(id) {
            window.location.href = `details.php?id=${id}`;
        }

        function editRecord(id) {
            window.location.href = `edit.php?id=${id}`;
        }

        function deleteRecord(id) {
            if (confirm('هل أنت متأكد من حذف هذا السجل؟')) {
                window.location.href = `delete.php?id=${id}`;
            }
        }

        // Tri des colonnes
        function sortTable(column) {
            const currentSort = new URLSearchParams(window.location.search).get('sort');
            const currentOrder = new URLSearchParams(window.location.search).get('order');
            
            let newOrder = 'asc';
            if (currentSort === column && currentOrder === 'asc') {
                newOrder = 'desc';
            }
            
            const search = document.getElementById('searchInput').value;
            const statut = document.getElementById('statutFilter').value;
            
            window.location.href = `?sort=${column}&order=${newOrder}&search=${encodeURIComponent(search)}&statut=${encodeURIComponent(statut)}`;
        }

        // Export Excel
        function exportToExcel() {
            const table = document.getElementById('dataTable');
            const wb = XLSX.utils.table_to_book(table, {sheet:"Sheet1"});
            XLSX.writeFile(wb, 'donnees_table.xlsx');
        }

        // Export PDF (nécessite jsPDF)
        function exportToPDF() {
            alert('لتصدير إلى PDF، يرجى تثبيت مكتبة jsPDF');
        }

        // Initialisation DataTables (optionnel)
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "language": {
                    "sProcessing": "جارٍ التحميل...",
                    "sLengthMenu": "أظهر _MENU_ مدخلات",
                    "sZeroRecords": "لم يعثر على أية سجلات",
                    "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                    "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
                    "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                    "sInfoPostFix": "",
                    "sSearch": "ابحث:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "الأول",
                        "sPrevious": "السابق",
                        "sNext": "التالي",
                        "sLast": "الأخير"
                    }
                },
                "paging": false,
                "searching": false,
                "info": false
            });
        });
    </script>
</body>
</html>