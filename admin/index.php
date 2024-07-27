<?php
include "koneksi.php";
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userid'])) {
  header("Location:../login.php");
} else if (!isset($_SESSION['kodeakses'])) {
  header("Location:../login_akses.php");
}

// Check for theme in session or cookies
if (isset($_SESSION['theme'])) {
  $theme = $_SESSION['theme'];
} elseif (isset($_COOKIE['theme'])) {
  $theme = $_COOKIE['theme'];
  $_SESSION['theme'] = $theme;
} else {
  $theme = 'default';
}
?>

<?php
// Menentukan waktu absensi berdasarkan hari
$absen_schedule = [
  'Monday'    => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '13:30:00', 'menunggu_end' => '13:59:59', 'pulang_start' => '14:00:00', 'pulang_tepat_waktu_end' => '16:00:00', 'pulang_end' => '17:00:00'],
  'Tuesday'   => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '13:30:00', 'menunggu_end' => '13:59:59', 'pulang_start' => '14:00:00', 'pulang_tepat_waktu_end' => '16:00:00', 'pulang_end' => '17:00:00'],
  'Wednesday' => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '13:30:00', 'menunggu_end' => '13:59:59', 'pulang_start' => '14:00:00', 'pulang_tepat_waktu_end' => '16:00:00', 'pulang_end' => '17:00:00'],
  'Thursday'  => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '13:30:00', 'menunggu_end' => '13:59:59', 'pulang_start' => '14:00:00', 'pulang_tepat_waktu_end' => '16:00:00', 'pulang_end' => '17:00:00'],
  'Friday'    => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '11:15:00', 'menunggu_end' => '11:44:59', 'pulang_start' => '11:45:00', 'pulang_tepat_waktu_end' => '13:00:00', 'pulang_end' => '16:00:00'],
  'Saturday'  => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '11:30:00', 'menunggu_end' => '11:59:59', 'pulang_start' => '12:00:00', 'pulang_tepat_waktu_end' => '14:00:00', 'pulang_end' => '16:00:00'],
  'Sunday'    => ['datang_start' => '00:00:00', 'datang_end' => '00:00:00', 'pulang_start' => '00:00:00', 'pulang_end' => '00:00:00'] // Tidak ada absensi di hari Minggu
];

date_default_timezone_set('Asia/Makassar');
$TanggalHariIni = date("Y-m-d");
$waktuHariIni = date("H:i:s");
$hari_ini = date("l");

// Mendapatkan jadwal absensi berdasarkan hari ini
$schedule = $absen_schedule[$hari_ini];

$get_sql_total_pekerja_smkn_labuang = mysqli_query($conn, "SELECT * FROM pekerja");
$get_data_total_pekerja_smkn_labuang = mysqli_num_rows($get_sql_total_pekerja_smkn_labuang);

$get_sql_total_pekerja_laki2_smkn_labuang = mysqli_query($conn, "SELECT * FROM pekerja WHERE jk='Laki-Laki'");
$get_data_total_pekerja_laki2_smkn_labuang = mysqli_num_rows($get_sql_total_pekerja_laki2_smkn_labuang);

$get_sql_total_pekerja_perempuan_smkn_labuang = mysqli_query($conn, "SELECT * FROM pekerja WHERE jk='Perempuan'");
$get_data_total_pekerja_perempuan_smkn_labuang = mysqli_num_rows($get_sql_total_pekerja_perempuan_smkn_labuang);

$get_sql_semua_pekerja_absen_hari_ini = mysqli_query($conn, "SELECT * FROM absen WHERE tanggal='$TanggalHariIni'");
$get_data_semua_pekerja_absen_hari_ini = mysqli_num_rows($get_sql_semua_pekerja_absen_hari_ini);

$get_data_total_pekerja_tidak_absen_hari_ini = $get_data_total_pekerja_smkn_labuang - $get_data_semua_pekerja_absen_hari_ini;

$get_sql_pekerja_absen_datang_dan_absen_pulang_hari_ini = mysqli_query($conn, "SELECT * FROM absen WHERE tanggal='$TanggalHariIni' AND waktudatang != '00:00:00' AND waktupulang != '00:00:00'");
$get_data_pekerja_absen_datang_dan_absen_pulang_hari_ini = mysqli_num_rows($get_sql_pekerja_absen_datang_dan_absen_pulang_hari_ini);

$get_sql_pekerja_absen_datang_hari_ini = mysqli_query($conn, "SELECT * FROM absen WHERE tanggal='$TanggalHariIni' AND waktudatang != '00:00:00' AND waktupulang='00:00:00'");
$get_data_pekerja_absen_datang_hari_ini = mysqli_num_rows($get_sql_pekerja_absen_datang_hari_ini);

$get_sql_pekerja_absen_pulang_hari_ini = mysqli_query($conn, "SELECT * FROM absen WHERE tanggal='$TanggalHariIni' AND waktudatang = '00:00:00' AND waktupulang != '00:00:00'");
$get_data_pekerja_absen_pulang_hari_ini = mysqli_num_rows($get_sql_pekerja_absen_pulang_hari_ini);

$get_sql_semua_user = mysqli_query($conn, "SELECT * FROM user");
$get_data_semua_user = mysqli_num_rows($get_sql_semua_user);

$get_sql_semua_jabatan = mysqli_query($conn, "SELECT * FROM jabatan");
$get_data_semua_jabatan = mysqli_num_rows($get_sql_semua_jabatan);

// Query to get all data from 'pangkat'
$get_sql_semua_pangkat = mysqli_query($conn, "SELECT * FROM pangkat");
$get_data_semua_pangkat = mysqli_num_rows($get_sql_semua_pangkat);

// Query to count 'Tidak Memiliki' in 'pangkat'
$get_sql_pangkat_tidak_memiliki = mysqli_query($conn, "SELECT COUNT(*) as pangkatTidakMemiliki FROM pangkat WHERE pangkat = 'Tidak Memiliki'");
$data_pangkat_tidak_memiliki = mysqli_fetch_assoc($get_sql_pangkat_tidak_memiliki);
$pangkat_tidak_memiliki = $data_pangkat_tidak_memiliki['pangkatTidakMemiliki'];

// Calculate total 'pangkat' excluding 'Tidak Memiliki'
$total_pangkat = $get_data_semua_pangkat - $pangkat_tidak_memiliki;

// Query to get all data from 'golongan'
$get_sql_semua_golongan = mysqli_query($conn, "SELECT * FROM golongan");
$get_data_semua_golongan = mysqli_num_rows($get_sql_semua_golongan);

// Query to count 'Tidak Memiliki' in 'golongan'
$get_sql_golongan_tidak_memiliki = mysqli_query($conn, "SELECT COUNT(*) as golonganTidakMemiliki FROM golongan WHERE golongan = 'Tidak Memiliki'");
$data_golongan_tidak_memiliki = mysqli_fetch_assoc($get_sql_golongan_tidak_memiliki);
$golongan_tidak_memiliki = $data_golongan_tidak_memiliki['golonganTidakMemiliki'];

// Calculate total 'golongan' excluding 'Tidak Memiliki'
$total_golongan = $get_data_semua_golongan - $golongan_tidak_memiliki;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
  <script src="js/color-modes.js"></script>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="logo/smkn_labuang.png" rel="icon">
  <title>Ruang<?= $_SESSION['role'] ?> - Dashboard</title>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> -->
  <!-- <link rel="stylesheet" href="css/iconbootstrap.css"> -->
  <link rel="stylesheet" href="css/node_modules/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/node_modules/bootstrap-icons/font/bootstrap-icons.min.css">

  <link rel="stylesheet" href="css/sweetalert.css">
  <script src="js/jquery-2.1.4.min.js"></script>
  <script src="js/sweetalert.min.js"></script>
  <script src="js/Chart.js"></script>


  <?php if ($theme == 'default') : ?>
    <link rel="stylesheet" href="theme/default.css">
  <?php elseif ($theme == 'theme1') : ?>
    <link rel="stylesheet" href="theme/theme1.css">
  <?php elseif ($theme == 'theme2') : ?>
    <link rel="stylesheet" href="theme/theme2.css">
  <?php elseif ($theme == 'theme3') : ?>
    <link rel="stylesheet" href="theme/theme3.css">
  <?php elseif ($theme == 'theme4') : ?>
    <link rel="stylesheet" href="theme/theme4.css">
  <?php elseif ($theme == 'theme5') : ?>
    <link rel="stylesheet" href="theme/theme5.css">
  <?php endif; ?>

  <style>
    .berita-user-profile {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      position: relative;
      left: 50%;
      transform: translateX(-50%);
    }

    .berita-user-profile-utama {
      width: 125px;
      height: 125px;
      border-radius: 50%;
      object-fit: cover;
      /* position: relative;
            left: 50%;
            transform: translateX(-50%); */
    }

    .berita-user-profile-nav {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
    }

    .space-between-body {
      display: flex;
      justify-content: space-between;
      flex-direction: column;
      min-height: 100vh;
    }

    .navbar-fixed {
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1;
    }

    .container-margin {
      margin-top: 100px;
    }

    .space-between-card {
      display: flex;
      justify-content: space-between;
      flex-direction: column;
      min-height: 100%;
    }

    .container-grid {
      display: grid;
      grid-template-areas: 'tentang-akun' 'semua-post';
    }

    .tentang-akun {
      grid-area: tentang-akun;
    }

    .semua-post {
      grid-area: semua-post;
    }

    /* =============================================================== */
    a {
      text-decoration: none;
    }

    .bd-placeholder-img {
      font-size: 1.125rem;
      text-anchor: middle;
      -webkit-user-select: none;
      -moz-user-select: none;
      user-select: none;
    }

    @media (min-width: 768px) {
      .bd-placeholder-img-lg {
        font-size: 3.5rem;
      }
    }

    .b-example-divider {
      width: 100%;
      height: 3rem;
      background-color: rgba(0, 0, 0, .1);
      border: solid rgba(0, 0, 0, .15);
      border-width: 1px 0;
      box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
    }

    .b-example-vr {
      flex-shrink: 0;
      width: 1.5rem;
      height: 100vh;
    }

    .bi {
      vertical-align: -.125em;
      fill: currentColor;
    }

    .nav-scroller {
      position: relative;
      z-index: 2;
      height: 2.75rem;
      overflow-y: hidden;
    }

    .nav-scroller .nav {
      display: flex;
      flex-wrap: nowrap;
      padding-bottom: 1rem;
      margin-top: -1px;
      overflow-x: auto;
      text-align: center;
      white-space: nowrap;
      -webkit-overflow-scrolling: touch;
    }

    .btn-bd-primary {
      --bd-violet-bg: #712cf9;
      --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

      --bs-btn-font-weight: 600;
      --bs-btn-color: var(--bs-white);
      --bs-btn-bg: var(--bd-violet-bg);
      --bs-btn-border-color: var(--bd-violet-bg);
      --bs-btn-hover-color: var(--bs-white);
      --bs-btn-hover-bg: #6528e0;
      --bs-btn-hover-border-color: #6528e0;
      --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
      --bs-btn-active-color: var(--bs-btn-hover-color);
      --bs-btn-active-bg: #5a23c8;
      --bs-btn-active-border-color: #5a23c8;
    }

    .bd-mode-toggle {
      z-index: 1500;
    }

    .bd-mode-toggle .dropdown-menu .active .bi {
      display: block !important;
    }
  </style>
  <link rel="stylesheet" href="css/bootstrap.css">
  <script src="vendor/jquery/jquery.min.js"></script>

</head>

<body id="page-top">

  <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
    <symbol id="check2" viewBox="0 0 16 16">
      <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" />
    </symbol>
    <symbol id="circle-half" viewBox="0 0 16 16">
      <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z" />
    </symbol>
    <symbol id="moon-stars-fill" viewBox="0 0 16 16">
      <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z" />
      <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z" />
    </symbol>
    <symbol id="sun-fill" viewBox="0 0 16 16">
      <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
    </symbol>
  </svg>

  <div class="dropdown position-fixed bottom-0 star-0 mb-3 ml-3 me-3 bd-mode-toggle">
    <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
      <svg class="bi my-1 theme-icon-active" width="1em" height="1em">
        <use href="#circle-half"></use>
      </svg>
      <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
          <svg class="bi me-2 opacity-50" width="1em" height="1em">
            <use href="#sun-fill"></use>
          </svg>
          Light
          <svg class="bi ms-auto d-none" width="1em" height="1em">
            <use href="#check2"></use>
          </svg>
        </button>
      </li>
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
          <svg class="bi me-2 opacity-50" width="1em" height="1em">
            <use href="#moon-stars-fill"></use>
          </svg>
          Dark
          <svg class="bi ms-auto d-none" width="1em" height="1em">
            <use href="#check2"></use>
          </svg>
        </button>
      </li>
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
          <svg class="bi me-2 opacity-50" width="1em" height="1em">
            <use href="#circle-half"></use>
          </svg>
          Auto
          <svg class="bi ms-auto d-none" width="1em" height="1em">
            <use href="#check2"></use>
          </svg>
        </button>
      </li>
    </ul>
  </div>

  <div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav sidebar sidebar-light bg-body-tertiary accordion" id="accordionSidebar">
      <a class="sidebar-brand bg-sidebar-theme d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon">
          <img src="logo/smkn_labuang.png" style="border-radius: 50%;">
        </div>
        <div class="sidebar-brand-text mx-2">SMKN Labuang</div>
      </a>
      <?php
      if ($_SESSION['role'] == "Admin" || $_SESSION['role'] == "Kepala Sekolah" || $_SESSION['role'] == "Kepala Tata Usaha" || $_SESSION['role'] == "STAF") {
      ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">
          Modal Pop Up
        </div>
        <li class="nav-item">
          <a class="nav-link" href="" data-bs-toggle="modal" data-bs-target="#cetakHarian">
            <i class="fas fa-fw fa-print"></i>
            <span>Cetak</span></a>
        </li>
      <?php
      }
      ?>
      <?php
      if ($_SESSION['role'] == "Admin") {
      ?>
        <li class="nav-item">
          <a class="nav-link" href="" data-bs-toggle="modal" data-bs-target="#buatQRCode">
            <i class="fas fa-fw fa-qrcode"></i>
            <span>Buat QR Code</span></a>
        </li>
      <?php
      }
      ?>
      <hr class="sidebar-divider">
      <div class="sidebar-heading">
        Halaman
      </div>
      <li class="nav-item active">
        <a class="nav-link" href="index.php">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="../index.php">
          <i class="fas fa-fw fa-qrcode"></i>
          <span>E-Absensi</span></a>
      </li>
      <?php
      if ($_SESSION['role'] == "Admin" || $_SESSION['role'] == "Kepala Sekolah" || $_SESSION['role'] == "Kepala Tata Usaha" || $_SESSION['role'] == "STAF") {
      ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">
          Daftar Absensi
        </div>
        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTableAbsensiPelajar" aria-expanded="true" aria-controls="collapseTable">
            <i class="fas fa-fw fa-address-card"></i>
            <span>Card</span>
          </a>
          <div id="collapseTableAbsensiPelajar" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
            <div class="card py-2 collapse-inner rounded">
              <h6 class="collapse-header">Pelajar</h6>
              <a class="collapse-item" href="absensi_harian.php">
                Absensi Harian
              </a>
              <a class="collapse-item" href="absensi_bulanan.php">
                Absensi Bulanan
              </a>
            </div>
          </div>
        </li>
      <?php
      }
      ?>
      <?php
      if ($_SESSION['role'] == "Admin" || $_SESSION['role'] == "Kepala Sekolah") {
      ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">
          Features 001
        </div>
        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTable" aria-expanded="true" aria-controls="collapseTable">
            <i class="fas fa-fw fa-table"></i>
            <span>Tables</span>
          </a>
          <div id="collapseTable" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
            <div class="card py-2 collapse-inner rounded">
              <h6 class="collapse-header">Tables</h6>
              <a class="collapse-item" href="table_jabatan.php">Table Jabatan</a>
              <a class="collapse-item" href="table_pangkat.php">Table Pangkat</a>
              <a class="collapse-item" href="table_Golongan.php">Table Golongan</a>
              <a class="collapse-item" href="table_pekerja.php">Table Pekerja</a>
              <a class="collapse-item" href="table_aksesmasuk.php">Table Akses</a>
              <a class="collapse-item" href="table_user.php">Table User</a>
            </div>
          </div>
        </li>
      <?php
      }
      ?>
      <?php
      if ($_SESSION['role'] == "Admin" || $_SESSION['role'] == "Kepala Sekolah" || $_SESSION['role'] == "Kepala Tata Usaha" || $_SESSION['role'] == "STAF") {
      ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">
          Features 002
        </div>
        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTablePekerja" aria-expanded="true" aria-controls="collapseTable">
            <i class="fas fa-fw fa-table"></i>
            <span>Tables Pekerja</span>
          </a>
          <div id="collapseTablePekerja" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
            <div class="card py-2 collapse-inner rounded">
              <h6 class="collapse-header">Tables</h6>
              <?php
              $selectedjabatanid = isset($_GET['jabatanid']) ? $_GET['jabatanid'] : '';
              $sql_kelas = mysqli_query($conn, "SELECT * FROM jabatan");
              while ($data_kelas = mysqli_fetch_array($sql_kelas)) {
                $activeClass = ($data_kelas['jabatanid'] == $selectedjabatanid) ? 'active btn-light' : '';
              ?>
                <a class="collapse-item <?= $activeClass ?>" href="data_jabatan_pekerja.php?jabatanid=<?= $data_kelas['jabatanid'] ?>&jabatan=<?= $data_kelas['jabatan'] ?>"><?= $data_kelas['jabatan'] ?></a>
              <?php
              }
              ?>
            </div>
          </div>
        </li>
      <?php
      }
      ?>
      <!-- <hr class="sidebar-divider">
      <div class="version" id="version-ruangadmin"></div> -->
    </ul>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column bg-body-tertiary">
      <div id="content">
        <!-- TopBar -->
        <nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top">
          <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>
          <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown no-arrow">
              <div class="nav-link dropdown-toggle">
                <i class="fas fa-user-tie fa-fw text-user"></i>&nbsp;
                <div class="text-user"><?= $_SESSION['role'] ?></div>
              </div>
            </li>
            <div class="topbar-divider d-none d-sm-block"></div>
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php
                $fotoPath = 'fotouser/' . $_SESSION['fotouser'];
                // Jika file tidak ada atau nama file tidak valid, gunakan gambar default
                if (!file_exists($fotoPath) || empty($_SESSION['fotouser'])) {
                  $fotoPath = 'default/user.png'; // Ganti dengan path gambar default
                }
                ?>
                <img class="img-profile rounded-circle" src="<?= $fotoPath ?>" style="max-width: 60px; object-fit: cover;">
                <span class="ml-2 d-none d-lg-inline text-user small"><?= $_SESSION['namalengkap'] ?></span>
              </a>
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="" data-bs-toggle="modal" data-bs-target="#myProfile">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a>
                <a class="dropdown-item" href="" data-bs-toggle="modal" data-bs-target="#setting">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  Settings
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                  Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#logoutModal" id="buttonModalLogout">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>
          </ul>
        </nav>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid bg-body-tertiary" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0">Dashboard</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
          </div>

          <div class="container-fluid">
            <?php
            $idpekerja = $_SESSION['idpekerja'];
            $sql_jadwal_absen = mysqli_query($conn, "SELECT pekerja.*, pangkat.*, absen.*, jabatan.*, golongan.*
            FROM pekerja 
            INNER JOIN absen ON pekerja.nip = absen.nip 
            INNER JOIN pangkat ON pekerja.pangkatid = pangkat.pangkatid 
            INNER JOIN jabatan ON pekerja.jabatanid = jabatan.jabatanid
            INNER JOIN golongan ON pekerja.golonganid = golongan.golonganid
            WHERE absen.tanggal = '$TanggalHariIni' AND pekerja.idpekerja = '$idpekerja'");
            $data_jadwal_absen = mysqli_fetch_array($sql_jadwal_absen);

            $waktuDatang = "00:00:00";
            $waktuPulang = "00:00:00";
            if (mysqli_num_rows($sql_jadwal_absen) > 0) {
              $waktuDatang = $data_jadwal_absen['waktudatang'];
              $waktuPulang = $data_jadwal_absen['waktupulang'];
            }

            if ($hari_ini == "Sunday") {
            ?>
              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                  <div class="alert alert-info alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-info text-gray-700"></i><b> Information Kepada Yth. <?= $_SESSION['namalengkap'] ?></b></h6>
                    Absensi Tidak Dilaksanakan Karena Hari Minggu. Absensi Akan Kembali Dilaksanakan Pada Hari Senin, harap jangan telat ya!
                  </div>
                </div>

              </div>
            <?php
            } else if ($waktuHariIni >= '00:00:00' && $waktuHariIni <=  $schedule['datang_start']) {
            ?>
              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                  <div class="alert alert-info alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-info text-gray-700"></i><b> Information Kepada Yth. <?= $_SESSION['namalengkap'] ?></b></h6>
                    Absensi Datang Segera Dibuka Pada Jam <?= $schedule['datang_start'] ?> WITA, harap jangan telat ya!
                  </div>
                </div>

              </div>
            <?php
            } else if ($waktuDatang != "00:00:00" && $waktuHariIni >=  $schedule['datang_start'] && $waktuHariIni <= $schedule['datang_end']) {
            ?>
              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                  <div class="alert alert-success alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-check text-gray-700"></i><b> Yth. <?= $_SESSION['namalengkap'] ?>. Anda Sudah Absensi Datang Hari Ini!</b></h6>
                    Silahkan Lakukan Absensi Pulang Pada <?= $schedule['pulang_start'] ?> WITA!
                  </div>
                </div>

              </div>
            <?php
            } else if ($waktuDatang == "00:00:00" && $waktuHariIni >= $schedule['datang_start'] && $waktuHariIni <= $schedule['datang_end']) {
            ?>
              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                  <div class="alert alert-warning alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-exclamation-triangle text-gray-700"></i><b> Yth. <?= $_SESSION['namalengkap'] ?>. Anda Belum Melakukan Absensi Hari Ini!</b></h6>
                    Silahkan Lakukan Absensi Datang Dengan Segera!
                  </div>
                </div>

              </div>
            <?php
            } else if ($waktuHariIni >= $schedule['menunggu_start'] && $waktuHariIni <= $schedule['menunggu_end']) {
            ?>
              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                  <div class="alert alert-info alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-info text-gray-700"></i><b> Information Kepada Yth. <?= $_SESSION['namalengkap'] ?></b></h6>
                    Absensi Datang Sudah Selesai. Absensi Pulang Segera Dibuka Pada Jam <?= $schedule['pulang_start'] ?> WITA, harap jangan telat ya!
                  </div>
                </div>

              </div>
            <?php
            } else if ($waktuPulang == "00:00:00" && $waktuHariIni >= $schedule['pulang_start'] && $waktuHariIni <= $schedule['pulang_end']) {
            ?>
              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                  <div class="alert alert-warning alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-exclamation-triangle text-gray-700"></i><b> Yth. <?= $_SESSION['namalengkap'] ?>. Anda Belum Melakukan Absensi Hari Ini!</b></h6>
                    Silahkan Lakukan Absensi Pulang Dengan Segera!
                  </div>
                </div>

              </div>
            <?php
            } else if ($waktuPulang != "00:00:00" && $waktuHariIni >= $schedule['pulang_start'] && $waktuHariIni <= $schedule['pulang_end']) {
            ?>
              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                  <div class="alert alert-success alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-check text-gray-700"></i><b> Yth. <?= $_SESSION['namalengkap'] ?>. Anda Sudah Absensi Pulang Hari Ini!</b></h6>
                    Silahkan Pulang Dan Datang Pada Besok Hari Untuk Melakukan Absensi Datang Pada <?= $schedule['datang_start'] ?> WITA!
                  </div>
                </div>

              </div>
            <?php
            } else {
            ?>
              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                  <div class="alert alert-info alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-info text-gray-700"></i><b> Information Kepada Yth. <?= $_SESSION['namalengkap'] ?></b></h6>
                    Absensi Pulang Sudah Selesai. Absensi Datang Segera Dibuka Pada <?= $schedule['datang_start'] ?> WITA. Kecuali Hari Minggu, harap jangan telat ya!
                  </div>
                </div>

              </div>
            <?php
            }
            if ($_SESSION['role'] == "Admin" || $_SESSION['role'] == "Kepala Sekolah" || $_SESSION['role'] == "Kepala Tata Usaha" || $_SESSION['role'] == "STAF") {
            ?>

              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mb-4">
                  <div class="alert alert-success alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-check text-gray-700"></i><b> Selamat Datang <?= $_SESSION['namalengkap'] ?>!</b></h6>
                    Selamat Datang Di Halaman Dashboard <?= $_SESSION['role'] ?>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">User</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $_SESSION['namalengkap'] ?></div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-user fa-3x" style="color: blue;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Pangkat/Golongan</div>
                          <?php
                          $pangkatid = $_SESSION['pangkatid'];
                          $sql_pangkat_pekerja = mysqli_query($conn, "SELECT * FROM pangkat WHERE pangkatid='$pangkatid'");
                          $data_pangkat_pekerja = mysqli_fetch_array($sql_pangkat_pekerja);
                          // $pangkat_pekerja = "-";
                          if (mysqli_num_rows($sql_pangkat_pekerja) > 0) {
                            $pangkat_pekerja = $data_pangkat_pekerja['pangkat'];
                            if ($data_pangkat_pekerja['pangkat'] == "Tidak Memiliki") {
                              $pangkat_pekerja = "-";
                            }
                          }
                          ?>
                          <?php
                          $golonganid = $_SESSION['golonganid'];
                          $sql_golongan_pekerja = mysqli_query($conn, "SELECT * FROM golongan WHERE golonganid='$golonganid'");
                          $data_golongan_pekerja = mysqli_fetch_array($sql_golongan_pekerja);
                          $golongan_pekerja = "-";
                          if (mysqli_num_rows($sql_golongan_pekerja) > 0) {
                            $golongan_pekerja = $data_golongan_pekerja['golongan'];
                            if ($data_golongan_pekerja['golongan'] == "Tidak Memiliki") {
                              $golongan_pekerja = "-";
                            }
                          }
                          ?>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $pangkat_pekerja ?>/<?= $golongan_pekerja ?></div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <!-- <i class="fas fa-user fa-3x" style="color: blue;"></i> -->
                          <i class="bi bi-easel2-fill fa-3x" style="color: violet;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Jabatan</div>
                          <?php
                          $jabatanid = $_SESSION['jabatanid'];
                          $sql_jabatan_pekerja = mysqli_query($conn, "SELECT * FROM jabatan WHERE jabatanid='$jabatanid'");
                          $data_jabatan_pekerja = mysqli_fetch_array($sql_jabatan_pekerja);
                          // $jabatan_pekerja = "-";
                          if (mysqli_num_rows($sql_jabatan_pekerja) > 0) {
                            $jabatan_pekerja = $data_jabatan_pekerja['jabatan'];
                            if ($data_jabatan_pekerja['jabatan'] == "Tidak Memiliki") {
                              $jabatan_pekerja = "-";
                            }
                          }
                          ?>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $jabatan_pekerja ?></div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="bi bi-lightbulb-fill fa-3x" style="color: skyblue;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Tanggal & Waktu</div>
                          <div class="h6 mb-0 font-weight-bold text-gray-700">
                            <?php
                            $sqlPointsLAndP = "SELECT k.jabatan, COALESCE(SUM(CASE WHEN s.jk = 'Laki-Laki' THEN 1 ELSE 0 END), 0) AS jumlah_laki, COALESCE(SUM(CASE WHEN s.jk = 'Perempuan' THEN 1 ELSE 0 END), 0) AS jumlah_perempuan FROM jabatan k LEFT JOIN pekerja s ON k.jabatanid = s.jabatanid GROUP BY k.jabatan";

                            $result = $conn->query($sqlPointsLAndP);

                            // Array untuk menyimpan hasil query
                            $dataPointsL = array();
                            $dataPointsP = array();

                            if ($result->num_rows > 0) {
                              while ($rowPointsLAndP = $result->fetch_assoc()) {
                                $dataPointsL[] = array("label" => $rowPointsLAndP['jabatan'], "y" => (int)$rowPointsLAndP['jumlah_laki']);
                                $dataPointsP[] = array("label" => $rowPointsLAndP['jabatan'], "y" => (int)$rowPointsLAndP['jumlah_perempuan']);
                              }
                            }

                            // Tutup koneksi database
                            // $conn->close();
                            ?>
                            <script src="js/canvasjs.min.js"></script>
                            <script>
                              function displayTime() {
                                var clientTime = new Date();
                                var time = new Date(clientTime.getTime());
                                var sh = time.getHours().toString();
                                var sm = time.getMinutes().toString();
                                var ss = time.getSeconds().toString();
                                document.getElementById("jam").innerHTML = (sh.length == 1 ? "0" + sh : sh) + " : " + (sm.length == 1 ? "0" + sm : sm) + " : " + (ss.length == 1 ? "0" + ss : ss);
                                document.getElementById("jaminput").value = (sh.length == 1 ? "0" + sh : sh) + ":" + (sm.length == 1 ? "0" + sm : sm) + ":" + (ss.length == 1 ? "0" + ss : ss);
                              }

                              window.onload = function() {
                                displayTime(); // Display time immediately when page loads
                                setInterval(displayTime, 1000); // Update time every second

                                var chart = new CanvasJS.Chart("chartContainer", {
                                  exportEnabled: true,
                                  animationEnabled: true,
                                  title: {
                                    text: "Total Pekerja Laki-Laki dan Perempuan per Jabatan"
                                  },
                                  axisX: {
                                    title: "Jabatan"
                                  },
                                  axisY: {
                                    title: "Jumlah Pekerja",
                                    includeZero: true
                                  },
                                  toolTip: {
                                    shared: true
                                  },
                                  legend: {
                                    cursor: "pointer",
                                    itemclick: toggleDataSeries
                                  },
                                  data: [{
                                      type: "column",
                                      name: "Laki-Laki",
                                      showInLegend: true,
                                      yValueFormatString: "#,##0.# Orang",
                                      dataPoints: <?php echo json_encode($dataPointsL, JSON_NUMERIC_CHECK); ?>
                                    },
                                    {
                                      type: "column",
                                      name: "Perempuan",
                                      showInLegend: true,
                                      yValueFormatString: "#,##0.# Orang",
                                      dataPoints: <?php echo json_encode($dataPointsP, JSON_NUMERIC_CHECK); ?>
                                    }
                                  ]
                                });

                                chart.render();

                                function toggleDataSeries(e) {
                                  if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                                    e.dataSeries.visible = false;
                                  } else {
                                    e.dataSeries.visible = true;
                                  }
                                  e.chart.render();
                                }
                              };
                            </script>
                            <?php
                            function getDayIndonesia($date)
                            {
                              if ($date != '0000-00-00') {
                                $data = hari(date('D', strtotime($date)));
                              } else {
                                $data = '-';
                              }

                              return $data;
                            }

                            function hari($day)
                            {
                              $hari = $day;

                              switch ($hari) {
                                case "Sun":
                                  $hari = "Minggu";
                                  break;
                                case "Mon":
                                  $hari = "Senin";
                                  break;
                                case "Tue":
                                  $hari = "Selasa";
                                  break;
                                case "Wed":
                                  $hari = "Rabu";
                                  break;
                                case "Thu":
                                  $hari = "Kamis";
                                  break;
                                case "Fri":
                                  $hari = "Jum'at";
                                  break;
                                case "Sat":
                                  $hari = "Sabtu";
                                  break;
                              }
                              return $hari;
                            }

                            // Menampilkan nama hari format Bahasa Indonesia
                            $hari_ini = date('Y-m-d');
                            echo getDayIndonesia($hari_ini);
                            echo date(" d/m/Y");
                            ?><br>
                            <font id="jam"></font> WITA
                            <input type="text" id="jaminput" name="" style="display: none;">
                          </div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-clock fa-3x" style="color: purple;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Total Pekerja Absensi Hari Ini</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_semua_pekerja_absen_hari_ini ?> Orang</div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($_SESSION['role'] == 'Admin') { ?>absensi_harian.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-user-tie fa-3x" style="color: greenyellow;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Total Pekerja Absen Datang & Absen Pulang Hari Ini</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_pekerja_absen_datang_dan_absen_pulang_hari_ini ?> Orang</div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-user-tie fa-3x" style="color: green;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Total Pekerja Hanya Absen Datang Hari Ini</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_pekerja_absen_datang_hari_ini ?> Orang</div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-user-tie fa-3x" style="color: yellow;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Total Pekerja Hanya Absen Pulang Hari Ini</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_pekerja_absen_pulang_hari_ini ?> Orang</div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-user-tie fa-3x" style="color: orange;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Total Pekerja Tidak Absensi Hari Ini</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_total_pekerja_tidak_absen_hari_ini ?> Orang</div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-user-tie fa-3x" style="color: red;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Total Semua Pekerja Perempuan SMKN Labuang</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_total_pekerja_perempuan_smkn_labuang ?> Orang</div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-user-tie fa-3x" style="color: pink;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Total Semua Pekerja Laki-Laki SMKN Labuang</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_total_pekerja_laki2_smkn_labuang ?> Orang</div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-user-tie fa-3x text-primary"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Total Semua Pekerja SMKN Labuang</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_total_pekerja_smkn_labuang ?> Orang</div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-users fa-3x" style="color: gray;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <?php
                if ($_SESSION['role'] == "Admin" || $_SESSION['role'] == "Kepala Sekolah") {
                ?>
                  <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card h-100">
                      <div class="card-body">
                        <div class="row align-items-center">
                          <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">User</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_semua_user ?> Akun</div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                              <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                              <!-- <span>Since last month</span> -->
                            </div>
                          </div>
                          <div class="col-auto">
                            <i class="fas fa-user fa-3x" style="color: blue;"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card h-100">
                      <div class="card-body">
                        <div class="row align-items-center">
                          <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Pangkat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $total_pangkat ?> Pangkat</div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                              <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                              <!-- <span>Since last month</span> -->
                            </div>
                          </div>
                          <div class="col-auto">
                            <!-- <i class="fas fa-user fa-3x" style="color: blue;"></i> -->
                            <i class="bi bi-easel2-fill fa-3x" style="color: violet;"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card h-100">
                      <div class="card-body">
                        <div class="row align-items-center">
                          <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Jabatan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $get_data_semua_jabatan ?> Jabatan</div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                              <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                              <!-- <span>Since last month</span> -->
                            </div>
                          </div>
                          <div class="col-auto">
                            <i class="bi bi-lightbulb-fill fa-3x" style="color: skyblue;"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card h-100">
                      <div class="card-body">
                        <div class="row align-items-center">
                          <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Golongan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $total_golongan ?> Golongan</div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                              <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                              <!-- <span>Since last month</span> -->
                            </div>
                          </div>
                          <div class="col-auto">
                            <i class="bi bi-bar-chart-line-fill fa-3x" style="color: darkolivegreen;"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php
                }
                ?>

              </div>

              <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-6 col-md-10 col-sm-11 col-12 mb-4">
                  <div class="card" style="height: 100%;">
                    <font class="text-secondary fw-bold">&nbsp;Grafik Polar Area Dari Jumlah Seluruh Pekerja SMKN Labuang</font>
                    <canvas id="kelasChart" width="100" height="100"></canvas>
                  </div>
                </div>
                <div class="col-xl-6 col-lg-6 col-md-10 col-sm-11 col-12 mb-4">
                  <div class="card" style="height: 100%;">
                    <font class="text-secondary fw-bold">&nbsp;Grafik Pie Dari Card Informasi Absensi Pekerja SMKN Labuang Hari Ini</font>
                    <canvas id="kehadiranChart" width="100" height="100"></canvas>
                  </div>
                </div>
                <div class="col-12 mb-5">
                  <style>
                    a.canvasjs-chart-credit {
                      display: none;
                    }
                  </style>
                  <div id="chartContainer" style="height: 400px; width: 100%;"></div>
                </div>
              </div>

            <?php
            } else {
            ?>

              <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mb-4">
                  <div class="alert alert-success alert-dismissible text-gray-700" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h6><i class="fas fa-check"></i><b> Selamat Datang <?= $_SESSION['namalengkap'] ?>!</b></h6>
                    Selamat Datang Di Halaman Dashboard <?= $_SESSION['role'] ?>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">User</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $_SESSION['namalengkap'] ?></div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-user fa-3x" style="color: blue;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Tanggal & Waktu</div>
                          <div class="h6 mb-0 font-weight-bold text-gray-700">
                            <?php
                            $sqlPointsLAndP = "SELECT k.jabatan, COALESCE(SUM(CASE WHEN s.jk = 'Laki-Laki' THEN 1 ELSE 0 END), 0) AS jumlah_laki, COALESCE(SUM(CASE WHEN s.jk = 'Perempuan' THEN 1 ELSE 0 END), 0) AS jumlah_perempuan FROM jabatan k LEFT JOIN pekerja s ON k.jabatanid = s.jabatanid GROUP BY k.jabatan";

                            $result = $conn->query($sqlPointsLAndP);

                            // Array untuk menyimpan hasil query
                            $dataPointsL = array();
                            $dataPointsP = array();

                            if ($result->num_rows > 0) {
                              while ($rowPointsLAndP = $result->fetch_assoc()) {
                                $dataPointsL[] = array("label" => $rowPointsLAndP['jabatan'], "y" => (int)$rowPointsLAndP['jumlah_laki']);
                                $dataPointsP[] = array("label" => $rowPointsLAndP['jabatan'], "y" => (int)$rowPointsLAndP['jumlah_perempuan']);
                              }
                            }

                            // Tutup koneksi database
                            // $conn->close();
                            ?>
                            <script src="js/canvasjs.min.js"></script>
                            <script>
                              function displayTime() {
                                var clientTime = new Date();
                                var time = new Date(clientTime.getTime());
                                var sh = time.getHours().toString();
                                var sm = time.getMinutes().toString();
                                var ss = time.getSeconds().toString();
                                document.getElementById("jam").innerHTML = (sh.length == 1 ? "0" + sh : sh) + " : " + (sm.length == 1 ? "0" + sm : sm) + " : " + (ss.length == 1 ? "0" + ss : ss);
                                document.getElementById("jaminput").value = (sh.length == 1 ? "0" + sh : sh) + ":" + (sm.length == 1 ? "0" + sm : sm) + ":" + (ss.length == 1 ? "0" + ss : ss);
                              }

                              window.onload = function() {
                                displayTime(); // Display time immediately when page loads
                                setInterval(displayTime, 1000); // Update time every second

                                var chart = new CanvasJS.Chart("chartContainer", {
                                  exportEnabled: true,
                                  animationEnabled: true,
                                  title: {
                                    text: "Total Pekerja Laki-Laki dan Perempuan per Jabatan"
                                  },
                                  axisX: {
                                    title: "Jabatan"
                                  },
                                  axisY: {
                                    title: "Jumlah Pekerja",
                                    includeZero: true
                                  },
                                  toolTip: {
                                    shared: true
                                  },
                                  legend: {
                                    cursor: "pointer",
                                    itemclick: toggleDataSeries
                                  },
                                  data: [{
                                      type: "column",
                                      name: "Laki-Laki",
                                      showInLegend: true,
                                      yValueFormatString: "#,##0.# Orang",
                                      dataPoints: <?php echo json_encode($dataPointsL, JSON_NUMERIC_CHECK); ?>
                                    },
                                    {
                                      type: "column",
                                      name: "Perempuan",
                                      showInLegend: true,
                                      yValueFormatString: "#,##0.# Orang",
                                      dataPoints: <?php echo json_encode($dataPointsP, JSON_NUMERIC_CHECK); ?>
                                    }
                                  ]
                                });

                                chart.render();

                                function toggleDataSeries(e) {
                                  if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                                    e.dataSeries.visible = false;
                                  } else {
                                    e.dataSeries.visible = true;
                                  }
                                  e.chart.render();
                                }
                              };
                            </script>
                            <?php
                            function getDayIndonesia($date)
                            {
                              if ($date != '0000-00-00') {
                                $data = hari(date('D', strtotime($date)));
                              } else {
                                $data = '-';
                              }

                              return $data;
                            }

                            function hari($day)
                            {
                              $hari = $day;

                              switch ($hari) {
                                case "Sun":
                                  $hari = "Minggu";
                                  break;
                                case "Mon":
                                  $hari = "Senin";
                                  break;
                                case "Tue":
                                  $hari = "Selasa";
                                  break;
                                case "Wed":
                                  $hari = "Rabu";
                                  break;
                                case "Thu":
                                  $hari = "Kamis";
                                  break;
                                case "Fri":
                                  $hari = "Jum'at";
                                  break;
                                case "Sat":
                                  $hari = "Sabtu";
                                  break;
                              }
                              return $hari;
                            }

                            // Menampilkan nama hari format Bahasa Indonesia
                            $hari_ini = date('Y-m-d');
                            echo getDayIndonesia($hari_ini);
                            echo date(" d/m/Y");
                            ?><br>
                            <font id="jam"></font> WITA
                            <input type="text" id="jaminput" name="" style="display: none;">
                          </div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="fas fa-clock fa-3x" style="color: purple;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Pangkat/Golongan</div>
                          <?php
                          $pangkatid = $_SESSION['pangkatid'];
                          $sql_pangkat_pekerja = mysqli_query($conn, "SELECT * FROM pangkat WHERE pangkatid='$pangkatid'");
                          $data_pangkat_pekerja = mysqli_fetch_array($sql_pangkat_pekerja);
                          $pangkat_pekerja = "-";
                          if (mysqli_num_rows($sql_pangkat_pekerja) > 0) {
                            $pangkat_pekerja = $data_pangkat_pekerja['pangkat'];
                          }
                          ?>
                          <?php
                          $golonganid = $_SESSION['golonganid'];
                          $sql_golongan_pekerja = mysqli_query($conn, "SELECT * FROM golongan WHERE golonganid='$golonganid'");
                          $data_golongan_pekerja = mysqli_fetch_array($sql_golongan_pekerja);
                          $golongan_pekerja = "-";
                          if (mysqli_num_rows($sql_golongan_pekerja) > 0) {
                            $golongan_pekerja = $data_golongan_pekerja['golongan'];
                          }
                          ?>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $pangkat_pekerja ?>/<?= $golongan_pekerja ?></div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <!-- <i class="fas fa-user fa-3x" style="color: blue;"></i> -->
                          <i class="bi bi-easel2-fill fa-3x" style="color: violet;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1">Jabatan</div>
                          <?php
                          $jabatanid = $_SESSION['jabatanid'];
                          $sql_jabatan_pekerja = mysqli_query($conn, "SELECT * FROM jabatan WHERE jabatanid='$jabatanid'");
                          $data_jabatan_pekerja = mysqli_fetch_array($sql_jabatan_pekerja);
                          $jabatan_pekerja = "-";
                          if (mysqli_num_rows($sql_jabatan_pekerja) > 0) {
                            $jabatan_pekerja = $data_jabatan_pekerja['jabatan'];
                          }
                          ?>
                          <div class="h5 mb-0 font-weight-bold text-gray-700"><?= $jabatan_pekerja ?></div>
                          <div class="mt-2 mb-0 text-muted text-xs">
                            <!-- <span class="text-success mr-2"><a class="text-decoration-none" href="<?php if ($fetch['role'] == 'Admin' || $fetch['role'] == 'Kepala Sekolah') { ?>datatables_rpl.php<?php } ?>">Lihat Detail <i class="fas fa-arrow-right text-gray-700"></i></a></span> -->
                            <!-- <span>Since last month</span> -->
                          </div>
                        </div>
                        <div class="col-auto">
                          <i class="bi bi-lightbulb-fill fa-3x" style="color: skyblue;"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>

            <?php
            }
            ?>

          </div>

          <!-- Modal Logout -->
          <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabelLogout" aria-hidden="true">
            <!-- <div class="kelap-kelip" data-dismiss="modal" aria-label="Close"></div> -->
            <div class="modal-dialog" role="document">
              <form class="modal-content" action="../logout.php" method="post">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabelLogout">Ohh No!</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" style="margin: 0; padding: 0; display: flex; justify-content: center; align-items: center;">
                  <img src="default/Tak berjudul28_20240708190427.png" alt="" style="width: 90%;">
                </div>
                <div class="modal-footer" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div class="h5 mb-3">Apa Jawaban Anda?</div>
                  <div style="display: flex; align-items: center;">
                    <button type="submit" class="btn btn-primary mr-2" id="logoutButton">I'm Sure</button>
                    <div class="h5 mx-2">Atau</div>
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Not Sure</button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <script>
            $(document).ready(function() {
              $('#logoutModal').on('shown.bs.modal', function() {
                // $('#focusInput').trigger('focus');
                document.addEventListener('keydown', function(event) {
                  if (event.key === 'Enter') {
                    document.getElementById('logoutButton').click();
                  }
                }, {
                  once: true
                });
              });

              document.addEventListener('keydown', function(event) {
                if (event.key === 'Alt') {
                  document.addEventListener('keydown', function(event) {
                    if (event.key === '/') {
                      document.getElementById('buttonModalLogout').click();
                    }
                  }, {
                    once: true
                  });
                }
              });
            });
          </script>
          <!-- Akhir Modal Logout -->

        </div>
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
      <footer class="sticky-footer bg-navbar">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright &copy;
              <?= date("Y") ?> i RPL UPTD SMK Negeri Labuang
            </span>
          </div>
        </div>
      </footer>
      <!-- Footer -->
    </div>
  </div>

  <?php
  if ($_SESSION['hakakses'] == "Dilarang") {
    echo "<script type='text/javascript'>
        setTimeout(function () { 
          swal({
                  title: 'Anda Dilarang Untuk Mengakses Halaman Ini',
                  text:  'Silahkan Hubungi Admin Utama Untuk Meminta Izin Akses!',
                  type: 'error',
                  timer: 5000,
                  showConfirmButton: false
              });   
        });  
        window.setTimeout(function(){ 
      window.location.replace('../index.php');
    } ,5000); 
        </script>";
  }
  ?>

  <!-- Modal Scrollable -->
  <div class="modal fade" id="cetakHarian" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <form class="modal-content" id="printForm" method="post" target="_blank">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalScrollableTitle"><i class="bi bi-printer-fill"></i> Cetak (Masukan Data)</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <label for="tanggal">Tanggal</label>
          <input type="date" class="form-control mb-2" id="tanggal" name="tanggal" placeholder="klik dan ketik disini..." required value="<?= date("Y-m-d") ?>">

          <label for="jabatanid">Jabatan</label>
          <select name="jabatanid" id="jabatanid" class="form-control mb-2" required>
            <option value="">Pilihan</option>
            <?php
            $sql_jabatan = mysqli_query($conn, "SELECT * FROM jabatan");
            while ($data_jabatan = mysqli_fetch_array($sql_jabatan)) {
            ?>
              <option value="<?= $data_jabatan['jabatanid'] ?>"><?= $data_jabatan['jabatan'] ?></option>
            <?php
            }
            ?>
          </select>

          <label for="printOption">Pilih Hasil Print</label>
          <select name="printOption" id="printOption" class="form-control mb-2" required>
            <option value="hasil_print_mingguan.php">Hasil Print Mingguan</option>
            <option value="hasil_print_harian.php">Hasil Print Harian</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
          <input type="submit" value="Tampikan" class="btn btn-success">
          <button type="reset" class="btn btn-danger">Hapus</button>
        </div>
      </form>
    </div>
  </div>
  <!-- End Modal Scrollable -->

  <script>
    document.getElementById('printForm').addEventListener('submit', function(event) {
      var printOption = document.getElementById('printOption').value;
      this.action = printOption;
    });
  </script>


  <!-- Modal Scrollable -->
  <div class="modal fade" id="buatQRCode" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <form id="qrcode-form" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalScrollableTitle">Buat QR-Code</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
        </div>
        <style>
          #qrcode {
            margin-top: 20px;
          }

          #qrcode canvas {
            width: 100%;
            /* Set the width you want here */
            max-width: 300px;
            /* Optional: set a max width */
            height: auto;
            /* Maintain the aspect ratio */
            /* margin: 0 auto; */
          }

          #qrcode-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
          }
        </style>
        <div class="modal-body">
          <div class="form-group">
            <label for="text">Kode Akses</label>
            <input class="form-control" type="text" id="text" placeholder="Enter text" required>
          </div>
          <div class="form-group">
            <label for="logo">Pilih Gambar Anda</label>
            <input class="form-control" type="file" id="logo" accept="image/*">
          </div>
          <div id="error-message" style="color: red; display: none;">Logo must be a square image (1:1 aspect ratio).</div>
          <div id="qrcode-container" style="display: none; text-align: center;">
            <div id="qrcode"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button id="download-btn" class="btn btn-warning" style="display:none;">Download QR Code</button>
          <button type="submit" class="btn btn-success">Generate QR Code</button>
          <button type="reset" class="btn btn-danger">Hapus</button>
          <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <!-- End Modal Scrollable -->

  <!-- Modal Scrollable -->
  <div class="modal fade" id="myProfile" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <form class="modal-content" action="update_profile.php" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalScrollableTitle">Profile Saya</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
        </div>
        <div class="modal-body">
          <!-- <form action="update_user.php" method="post" enctype="multipart/form-data"> -->
          <?php
          $userid = $_SESSION['userid'];
          $sql = mysqli_query($conn, "SELECT * FROM user WHERE userid='$userid'");
          while ($data = mysqli_fetch_array($sql)) {
            $fotoPath = 'fotouser/' . $data['fotouser'];
            // Jika file tidak ada atau nama file tidak valid, gunakan gambar default
            if (!file_exists($fotoPath) || empty($data['fotouser'])) {
              $fotoPath = 'default/user.png'; // Ganti dengan path gambar default
            }
          ?>
            <input type="text" class="form-control mb-2" id="userid" name="userid" placeholder="klik dan ketik disini..." value="<?= $data['userid'] ?>" hidden required>
            <label for="inputFoto" style="display: block;" class="mb-3">
              <img src="<?= $fotoPath ?>" alt="" class="berita-user-profile" id="imgFoto">
            </label>
            <input type="file" name="fotouser" id="inputFoto" class="form-control" hidden>
            <label for="username">Username</label>
            <input type="text" class="form-control mb-2" id="username" name="username" placeholder="klik dan ketik disini..." value="<?= $data['username'] ?>" required>
            <script>
              // Add event listener to the Username input field
              $('#username').on('input', function() {
                var username = $(this).val();
                // Convert to lowercase and remove spaces
                username = username.toLowerCase().replace(/\s+/g, '');
                $(this).val(username);
              });
            </script>
            <label for="password">Password</label>
            <input type="password" class="form-control mb-2" id="password" name="password" placeholder="klik dan ketik disini..." value="<?= $data['password'] ?>" minlength="8" required>
            <input type="checkbox" id="show-password"> <label for="show-password">Tampilkan Sandi</label>
            <label for="email" style="display: block;">Email</label>
            <input type="email" class="form-control mb-2" id="email" name="email" placeholder="klik dan ketik disini..." value="<?= $data['email'] ?>" required>
            <label for="namalengkap">Nama Lengkap</label>
            <input type="text" class="form-control mb-2" id="namalengkap" name="namalengkap" placeholder="klik dan ketik disini..." value="<?= $data['namalengkap'] ?>" required>
            <input type="hidden" class="form-control mb-2" id="nip" name="nip" placeholder="klik dan ketik disini..." value="<?= $data['nip'] ?>" required>
            <input type="hidden" class="form-control mb-2" id="jabatanid" name="jabatanid" placeholder="klik dan ketik disini..." value="<?= $data['jabatanid'] ?>" required>
            <input type="hidden" class="form-control mb-2" id="pangkatid" name="pangkatid" placeholder="klik dan ketik disini..." value="<?= $data['pangkatid'] ?>" required>
            <input type="hidden" class="form-control mb-2" id="golonganid" name="golonganid" placeholder="klik dan ketik disini..." value="<?= $data['golonganid'] ?>" required>
            <input type="hidden" class="form-control mb-2" id="hakakses" name="hakakses" placeholder="klik dan ketik disini..." value="<?= $data['hakakses'] ?>" required>
            <input type="hidden" class="form-control mb-2" id="idpekerja" name="idpekerja" placeholder="klik dan ketik disini..." value="<?= $data['idpekerja'] ?>" required>
            <label for="alamat">Alamat</label>
            <textarea name="alamat" class="form-control mb-2" id="alamat" cols="30" rows="5" placeholder="klik dan ketik disini..." required><?= $data['alamat'] ?></textarea>
            <input type="text" class="form-control mb-2" id="role" name="role" placeholder="klik dan ketik disini..." value="<?= $data['role'] ?>" hidden required>
          <?php
          }
          ?>
          <!-- </form> -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
          <input type="submit" value="Ubah" class="btn btn-success">
          <button type="reset" class="btn btn-danger">Hapus</button>
        </div>
      </form>
    </div>
  </div>
  <!-- End Modal Scrollable -->

  <!-- Modal Scrollable -->
  <div class="modal fade" id="setting" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <form class="modal-content" action="change_theme.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalScrollableTitle"><i class="bi bi-gear-wide-connected"></i> Pengaturan</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <label for="theme">Pilih Tema:</label>
          <div class="form-check">
            <input type="radio" class="form-check-input" name="theme" id="themeDefault" value="default" <?php if ($theme == 'default') echo 'checked'; ?>>
            <label class="form-check-label" for="themeDefault">
              <img src="theme/images/Tak berjudul20_20240608002134.png" alt="Default Tema" class="img-thumbnail" style="width: 50%;"> Default Tema
            </label>
          </div>
          <div class="form-check">
            <input type="radio" class="form-check-input" name="theme" id="theme1" value="theme1" <?php if ($theme == 'theme1') echo 'checked'; ?>>
            <label class="form-check-label" for="theme1">
              <img src="theme/images/Tak berjudul20_20240608002241.png" alt="Biru & Biru Langit" class="img-thumbnail" style="width: 50%;"> Biru & Biru Langit
            </label>
          </div>
          <div class="form-check">
            <input type="radio" class="form-check-input" name="theme" id="theme2" value="theme2" <?php if ($theme == 'theme2') echo 'checked'; ?>>
            <label class="form-check-label" for="theme2">
              <img src="theme/images/Tak berjudul20_20240608002352.png" alt="Orange & Kuning" class="img-thumbnail" style="width: 50%;"> Orange & Kuning
            </label>
          </div>
          <div class="form-check">
            <input type="radio" class="form-check-input" name="theme" id="theme3" value="theme3" <?php if ($theme == 'theme3') echo 'checked'; ?>>
            <label class="form-check-label" for="theme3">
              <img src="theme/images/Tak berjudul20_20240608002447.png" alt="Hijau & Hijau Muda" class="img-thumbnail" style="width: 50%;"> Hijau & Hijau Muda
            </label>
          </div>
          <div class="form-check">
            <input type="radio" class="form-check-input" name="theme" id="theme4" value="theme4" <?php if ($theme == 'theme4') echo 'checked'; ?>>
            <label class="form-check-label" for="theme4">
              <img src="theme/images/Tak berjudul20_20240608002529.png" alt="Merah & Merah Muda" class="img-thumbnail" style="width: 50%;"> Merah & Merah Muda
            </label>
          </div>
          <div class="form-check">
            <input type="radio" class="form-check-input" name="theme" id="theme5" value="theme5" <?php if ($theme == 'theme5') echo 'checked'; ?>>
            <label class="form-check-label" for="theme5">
              <img src="theme/images/Tak berjudul20_20240608002628.png" alt="Ungu & Lavender" class="img-thumbnail" style="width: 50%;"> Ungu & Lavender
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-outline-success" data-bs-dismiss="modal">Ubah Tema</button>
        </div>
      </form>
    </div>
  </div>
  <!-- End Modal Scrollable -->

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <!-- <script src="vendor/chart.js/Chart.min.js"></script> -->
  <!-- <script src="js/demo/chart-area-demo.js"></script> -->
  <script src="js/ruang-admin.min.js"></script>

  <script src="js/image.js"></script>
  <script src="js/navbar.js"></script>
  <script src="js/bootstrap.js"></script>

  <script>
    document.getElementById('show-password').addEventListener('change', function() {
      var passwordInput = document.getElementById('password');
      if (this.checked) {
        passwordInput.type = 'text';
      } else {
        passwordInput.type = 'password';
      }
    });
  </script>

  <script>
    // Add event listener to the Username input field
    $('#username').on('input', function() {
      var username = $(this).val();
      // Convert to lowercase and remove spaces
      username = username.toLowerCase().replace(/\s+/g, '');
      $(this).val(username);
    });
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

  <script src="js/qrcode.js"></script>
  <script src="js/qrcode.min.js"></script>

  <?php
  // Ambil data dari tabel dengan join
  $sql = "SELECT k.jabatan, COUNT(s.idpekerja) as count FROM pekerja s JOIN jabatan k ON s.jabatanid = k.jabatanid GROUP BY k.jabatan";
  $result = $conn->query($sql);

  $kelasData = [];
  $kelasLabels = [];
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $kelasData[] = $row['count'];
      $kelasLabels[] = $row['jabatan'];
    }
  } else {
    echo "0 results";
  }
  $conn->close();
  ?>

  <script>
    // Siapkan data untuk Chart.js
    const kelasLabels = <?php echo json_encode($kelasLabels); ?>;
    const kelasData = <?php echo json_encode($kelasData); ?>;

    // Tampilkan diagram donat
    const ctx = document.getElementById('kelasChart').getContext('2d');
    const kelasChart = new Chart(ctx, {
      type: 'polarArea',
      data: {
        labels: kelasLabels,
        datasets: [{
          label: 'Jumlah pekerja per Jabatan',
          data: kelasData,
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(199, 199, 199, 0.2)',
            'rgba(83, 102, 255, 0.2)'
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(199, 199, 199, 1)',
            'rgba(83, 102, 255, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: 'Jumlah Pekerja per Jabatan'
          }
        }
      }
    });
  </script>

  <script>
    // Data dari PHP
    const PekerjaAbsenDatangDanAbsenPulangHariIni = <?php echo $get_data_pekerja_absen_datang_dan_absen_pulang_hari_ini; ?>;
    const PekerjaHanyaAbsenDatangHariIni = <?php echo $get_data_pekerja_absen_datang_hari_ini; ?>;
    const PekerjaHanyaAbsenPulangHariIni = <?php echo $get_data_pekerja_absen_pulang_hari_ini; ?>;
    const TotalPekerjaTidakAbsensiHariIni = <?php echo $get_data_total_pekerja_tidak_absen_hari_ini; ?>;

    // Membuat chart pie
    const ctx2 = document.getElementById('kehadiranChart').getContext('2d');
    const kehadiranChart = new Chart(ctx2, {
      type: 'pie',
      data: {
        labels: [
          'Pekerja Absen Datang & Absen Pulang Hari Ini',
          'Pekerja Hanya Absen Datang Hari Ini',
          'Pekerja Hanya Absen Pulang Hari Ini',
          'Total Pekerja Tidak Absensi Hari Ini',
        ],
        datasets: [{
          data: [
            PekerjaAbsenDatangDanAbsenPulangHariIni,
            PekerjaHanyaAbsenDatangHariIni,
            PekerjaHanyaAbsenPulangHariIni,
            TotalPekerjaTidakAbsensiHariIni,
          ],
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(201, 203, 207, 0.2)' // Warna tambahan
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(201, 203, 207, 1)' // Warna tambahan
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: 'Kehadiran pekerja Hari Ini'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.label || '';
                let value = context.raw || 0;
                if (label) {
                  label += ': ';
                }
                label += value;
                return label;
              }
            }
          }
        }
      }
    });
  </script>

</body>

</html>