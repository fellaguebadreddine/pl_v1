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
if ($current_user->type !== 'super_admin') {
    $session->logout();
    redirect_to('../login.php');
}
?>
<?php

$titre = "الرئيسية ";

$active_menu = "index";

$active_submenu = "index";

$header = array('todo');

if ($current_user->type =='super_admin'){

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
                    <i class="fas fa-building me-3"></i> الرئيسية
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
                  <div class="row mb-4">

<div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
        <div class="inner">
            <h3>58</h3>
            <p>إجمالي الولايات</p>
        </div>
        <div class="icon">
            <i class="fas fa-map"></i>
        </div>
    </div>
</div>

<div class="col-lg-3 col-6">
    <div class="small-box bg-success">
        <div class="inner">
            <h3>320</h3>
            <p>إجمالي المؤسسات</p>
        </div>
        <div class="icon">
            <i class="fas fa-building"></i>
        </div>
    </div>
</div>

<div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
        <div class="inner">
            <h3>1,250</h3>
            <p>عدد المستخدمين</p>
        </div>
        <div class="icon">
            <i class="fas fa-users"></i>
        </div>
    </div>
</div>

<div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
        <div class="inner">
            <h3>78%</h3>
            <p>نسبة التقدم العامة</p>
        </div>
        <div class="icon">
            <i class="fas fa-chart-line"></i>
        </div>
    </div>
</div>

</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-area me-2"></i> تطور الأداء حسب الأشهر</h3>
            </div>
            <div class="card-body">
                <canvas id="evolutionChart" height="100"></canvas>
            </div>
        </div>
    </div>

<div class="col-md-6">
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table me-2"></i> تفاصيل حسب الولاية</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>الولاية</th>
                        <th>عدد المؤسسات</th>
                        <th>نسبة التقدم</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>الجزائر</td>
                        <td>45</td>
                        <td>92%</td>
                        <td><span class="badge bg-success">ممتاز</span></td>
                    </tr>
                    <tr>
                        <td>وهران</td>
                        <td>30</td>
                        <td>88%</td>
                        <td><span class="badge bg-warning">جيد</span></td>
                    </tr>
                    <tr>
                        <td>بشار</td>
                        <td>12</td>
                        <td>45%</td>
                        <td><span class="badge bg-danger">ضعيف</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

                 
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
       <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('evolutionChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['جانفي','فيفري','مارس','أفريل','ماي','جوان'],
        datasets: [{
            label: 'نسبة التقدم',
            data: [20, 35, 50, 65, 72, 78],
            borderWidth: 3,
            fill: true
        }]
    }
});
</script>


    <!--begin::Footer-->
    <?php require_once("composit/footer.php");?>
