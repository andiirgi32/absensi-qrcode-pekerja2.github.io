<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="admin/css/sweetalert.css">
  <script src="admin/js/jquery-2.1.4.min.js"></script>
  <script src="admin/js/sweetalert.min.js"></script>
  <link rel="icon" href="admin/logo/smkn_labuang.png">
</head>

<body>

</body>

</html>

<?php

require 'admin/koneksi.php';

// Menentukan waktu absensi berdasarkan hari
$absen_schedule = [
  'Monday'    => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '13:30:00', 'menunggu_end' => '13:59:59', 'pulang_start' => '14:00:00', 'pulang_tepat_waktu_end' => '16:00:00', 'pulang_end' => '17:00:00'],
  'Tuesday'   => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '13:30:00', 'menunggu_end' => '13:59:59', 'pulang_start' => '14:00:00', 'pulang_tepat_waktu_end' => '16:00:00', 'pulang_end' => '17:00:00'],
  'Wednesday' => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '13:30:00', 'menunggu_end' => '13:59:59', 'pulang_start' => '14:00:00', 'pulang_tepat_waktu_end' => '16:00:00', 'pulang_end' => '17:00:00'],
  'Thursday'  => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '13:30:00', 'menunggu_end' => '13:59:59', 'pulang_start' => '14:00:00', 'pulang_tepat_waktu_end' => '16:00:00', 'pulang_end' => '17:00:00'],
  'Friday'    => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '11:15:00', 'menunggu_end' => '11:44:59', 'pulang_start' => '11:45:00', 'pulang_tepat_waktu_end' => '13:00:00', 'pulang_end' => '16:00:00'],
  'Saturday'  => ['datang_start' => '06:00:00', 'datang_cepat_end' => '06:30:00', 'datang_tepat_waktu_start' => '06:30:01', 'datang_tepat_waktu_end' => '07:30:00', 'datang_end' => '09:00:00', 'menunggu_start' => '09:00:01', 'pulang_cepat_start' => '13:30:00', 'menunggu_end' => '13:59:59', 'pulang_start' => '14:00:00', 'pulang_tepat_waktu_end' => '16:00:00', 'pulang_end' => '17:00:00'],
  'Sunday'    => ['datang_start' => '00:00:00', 'datang_end' => '00:00:00', 'pulang_start' => '00:00:00', 'pulang_end' => '00:00:00'] // Tidak ada absensi di hari Minggu
];

// Memeriksa apakah NIS dan tanggal tersedia dalam POST
if (isset($_POST["nip"], $_POST["tanggal"])) {

  $nip = $_POST["nip"];
  date_default_timezone_set('Asia/Makassar');
  $tanggal = $_POST["tanggal"];

  // Mendapatkan waktu saat ini dengan zona waktu Asia/Makassar
  $waktu = date("H:i:s");
  $hari_ini = date("l");

  // Mendapatkan jadwal absensi berdasarkan hari ini
  $schedule = $absen_schedule[$hari_ini];

  // Menentukan status absensi datang dan pulang
  $status_datang = '';
  $status_pulang = '';
  $is_datang = false;
  $is_pulang = false;

  if ($hari_ini == "Sunday") {
    echo "<script type='text/javascript'>
              setTimeout(function () { 
                swal({
                        title: 'Maaf Absensi Tidak Dilakukan Pada Hari Minggu!',
                        text:  'Silahkan Lakukan Absensi Pada Hari Senin, Jangan Sampai Lewat Yah!',
                        type: 'error',
                        timer: 5000,
                        showConfirmButton: false
                    });   
              });  
              window.setTimeout(function(){ 
                	        window.history.go(-1);
              } ,3000); 
              </script>";
    return false;
  } else if ($waktu >= $schedule['datang_start'] && $waktu <= $schedule['datang_end']) {
    $is_datang = true;
    if ($waktu >= $schedule['datang_start'] && $waktu <= $schedule['datang_cepat_end']) {
      $status_datang = 'Datang Cepat';
    } else if ($waktu >= $schedule['datang_tepat_waktu_start'] && $waktu <= $schedule['datang_tepat_waktu_end']) {
      $status_datang = 'Datang Tepat Waktu';
    } else {
      $status_datang = 'Datang Terlambat';
    }
  } else if ($waktu >= $schedule['menunggu_start'] && $waktu <= $schedule['menunggu_end']) {
    if ($waktu >= $schedule['pulang_cepat_start'] && $waktu <= $schedule['menunggu_end']) {
      $is_pulang = true;
      $status_pulang = 'Pulang Cepat';
    } else {
      echo "<script type='text/javascript'>
              setTimeout(function () { 
                swal({
                        title: 'Maaf Waktu Untuk Melakukan Absensi Datang Sudah Lewat!',
                        text:  'Silahkan Lakukan Absensi Pulang Pada Pukul " . $schedule['pulang_start'] . " WITA, Jangan Sampai Lewat Yah!',
                        type: 'warning',
                        timer: 5000,
                        showConfirmButton: false
                    });   
              });  
              window.setTimeout(function(){ 
                	        window.history.go(-1);
              } ,3000); 
              </script>";
      return false;
    }
  } else if ($waktu >= $schedule['pulang_start'] && $waktu <= $schedule['pulang_end']) {
    $is_pulang = true;
    if ($waktu >= $schedule['pulang_start'] && $waktu <= $schedule['pulang_tepat_waktu_end']) {
      $status_pulang = 'Pulang Tepat Waktu';
    } else {
      $status_pulang = 'Pulang Terlambat';
    }
  } else {
    echo "<script type='text/javascript'>
              setTimeout(function () { 
                swal({
                        title: 'Maaf Waktu Untuk Melakukan Absensi Telah Selesai!',
                        text:  'Silahkan Lakukan Absensi Pada Besok Hari, Jangan Sampai Lewat Yah!',
                        type: 'error',
                        timer: 5000,
                        showConfirmButton: false
                    });   
              });  
              window.setTimeout(function(){ 
                	        window.history.go(-1);
              } ,3000); 
              </script>";
    return false;
  }

  // Memeriksa apakah NIP sudah ada dalam tabel pekerja
  $check_nip_query = mysqli_query($conn, "SELECT * FROM pekerja WHERE nip='$nip'");

  if (mysqli_num_rows($check_nip_query) == 0) {
    echo "<script type='text/javascript'>
              setTimeout(function () { 
                swal({
                        title: 'NIP tidak terdaftar!',
                        text:  'Silakan hubungi admin',
                        type: 'error',
                        timer: 3000,
                        showConfirmButton: true
                    });   
              });  
              window.setTimeout(function(){ 
                	        window.history.go(-1);
              } ,900); 
              </script>";
    return false;
  }

  // Memeriksa apakah NIP sudah ada dalam tabel absen untuk tanggal yang sama
  $check_absen_query = mysqli_query($conn, "SELECT * FROM absen WHERE nip='$nip' AND tanggal='$tanggal'");

  // Jika NIP sudah ada dalam tabel absen
  if (mysqli_num_rows($check_absen_query) == 1) {
    $absen_row = mysqli_fetch_assoc($check_absen_query);

    // Update absen pulang
    if ($is_pulang) {
      if ($absen_row['waktupulang'] != "00:00:00") {
        echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                          title: 'Mohon Maaf Anda Sudah Absen Pulang!',
                          text:  'Silahkan Absen Pada Besok Hari',
                          type: 'warning',
                          timer: 3000,
                          showConfirmButton: true
                      });   
                });  
                window.setTimeout(function(){ 
                  	        window.history.go(-1);
                } ,900); 
                </script>";
        return false;
      } else {
        $update_pulang_query = "UPDATE absen SET waktupulang='$waktu', keteranganpulang='$status_pulang' WHERE nip='$nip' AND tanggal='$tanggal'";
        $result = mysqli_query($conn, $update_pulang_query);

        $sql_nama = mysqli_query($conn, "SELECT nama FROM pekerja WHERE nip='$nip'");
        $data_nama = mysqli_fetch_array($sql_nama);
        $nama = $data_nama['nama'];

        if ($result) {
          echo "<script type='text/javascript'>
                    setTimeout(function () { 
                      swal({
                              title: 'Selamat Anda Berhasil Absen Pulang, $nama!',
                              text:  'Silahkan Pulang',
                              type: 'success',
                              timer: 3000,
                              showConfirmButton: true
                          });   
                    });  
                    window.setTimeout(function(){ 
                      	        window.history.go(-1);
                    } ,900); 
                    </script>";
        } else {
          echo "Error: " . mysqli_error($conn);
        }
      }
    } else {
      echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                          title: 'Mohon Maaf Anda Sudah Absen Datang!',
                          text:  'Silahkan Absen Pulang Pada Sore Hari',
                          type: 'warning',
                          timer: 3000,
                          showConfirmButton: true
                      });   
                });  
                window.setTimeout(function(){ 
                  	        window.history.go(-1);
                } ,900); 
                </script>";
      return false;
    }
  }

  // Jika NIP belum ada dalam tabel absen, lakukan pendaftaran absen
  else {
    if ($is_datang) {
      $result = mysqli_query($conn, "INSERT INTO absen (nip, tanggal, waktudatang, keterangandatang) VALUES ('$nip', '$tanggal', '$waktu', '$status_datang')");

      $sql_nama = mysqli_query($conn, "SELECT nama FROM pekerja WHERE nip='$nip'");
      $data_nama = mysqli_fetch_array($sql_nama);
      $nama = $data_nama['nama'];

      if ($result) {
        echo "<script type='text/javascript'>
                    setTimeout(function () { 
                      swal({
                              title: 'Selamat Anda Berhasil Absen Datang, $nama!',
                              text:  'Silahkan Masuk',
                              type: 'success',
                              timer: 3000,
                              showConfirmButton: true
                          });   
                    });  
                    window.setTimeout(function(){ 
                      	        window.history.go(-1);
                    } ,900); 
                    </script>";
      } else {
        echo "Error: " . mysqli_error($conn);
      }
    } else if ($is_pulang) {
      $result = mysqli_query($conn, "INSERT INTO absen (nip, tanggal, waktupulang, keteranganpulang) VALUES ('$nip', '$tanggal', '$waktu', '$status_pulang')");
      // echo "<script type='text/javascript'>
      //           setTimeout(function () { 
      //             swal({
      //                     title: 'Mohon Maaf Anda Belum Absen Datang!',
      //                     text:  'Silahkan Absen Datang Terlebih Dahulu',
      //                     type: 'warning',
      //                     timer: 3000,
      //                     showConfirmButton: true
      //                 });   
      //           });  
      //           window.setTimeout(function(){ 
      //             	        window.history.go(-1);
      // window.location.replace('index.php');
      //           } ,900); 
      //           </script>";
      $sql_nama = mysqli_query($conn, "SELECT nama FROM pekerja WHERE nip='$nip'");
      $data_nama = mysqli_fetch_array($sql_nama);
      $nama = $data_nama['nama'];
      echo "<script type='text/javascript'>
                    setTimeout(function () { 
                      swal({
                              title: 'Selamat Anda Berhasil Absen Pulang, $nama!',
                              text:  'Silahkan Pulang',
                              type: 'success',
                              timer: 3000,
                              showConfirmButton: true
                          });   
                    });  
                    window.setTimeout(function(){ 
                      	        window.history.go(-1);
                    } ,900); 
                    </script>";
    }
  }
} else {
  echo "<script type='text/javascript'>
              setTimeout(function () { 
                swal({
                        title: 'Gagal Melakukan Absensi!',
                        text:  'Data NIP dan tanggal tidak tersedia dalam permintaan.',
                        type: 'error',
                        timer: 3000,
                        showConfirmButton: true
                    });   
              });  
              window.setTimeout(function(){ 
                	        window.history.go(-1);
              } ,900); 
              </script>";
}

?>