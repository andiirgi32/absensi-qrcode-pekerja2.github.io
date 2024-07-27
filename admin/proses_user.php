<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="css/sweetalert.css">
  <script src="js/jquery-2.1.4.min.js"></script>
  <script src="js/sweetalert.min.js"></script>
  <link rel="icon" href="logo/smkn_labuang.png">
</head>

<body>

</body>

</html>
<?php

include "koneksi.php";
session_start();

$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$alamat = $_POST['alamat'];
$role = $_POST['role'];
$hakakses = $_POST['hakakses'];
$idpekerja = $_POST['idpekerja'];

$sql_jadwal_absen = mysqli_query($conn, "SELECT pekerja.*, pangkat.*, jabatan.*, golongan.*
            FROM pekerja 
            INNER JOIN pangkat ON pekerja.pangkatid = pangkat.pangkatid 
            INNER JOIN jabatan ON pekerja.jabatanid = jabatan.jabatanid
            INNER JOIN golongan ON pekerja.golonganid = golongan.golonganid
            WHERE pekerja.idpekerja = '$idpekerja'");
$data_jadwal_absen = mysqli_fetch_array($sql_jadwal_absen);

$namalengkap = $data_jadwal_absen['nama'];
$nip = $data_jadwal_absen['nip'];
$jabatan = $data_jadwal_absen['jabatan'];
$jabatanid = $data_jadwal_absen['jabatanid'];
$pangkatid = $data_jadwal_absen['pangkatid'];
$golonganid = $data_jadwal_absen['golonganid'];

$sql_akun_pekerja = mysqli_query($conn, "SELECT * FROM user WHERE idpekerja = '$idpekerja'");
$data_akun_pekerja = mysqli_num_rows($sql_akun_pekerja);

$rand = rand();
$ekstensi = array("png", "jpg", "jpeg", "gif");
$namafile = $_FILES['fotouser']['name'];
$ukuran = $_FILES['fotouser']['size'];
$ext = pathinfo($namafile, PATHINFO_EXTENSION);

// Cek apakah Kepala Sekolah sudah terdaftar
$sql_kepala_sekolah = mysqli_query($conn, "SELECT * FROM user WHERE role='Kepala Sekolah'");
$kepala_sekolah_exists = mysqli_num_rows($sql_kepala_sekolah) > 0;

// Cek apakah Admin sudah terdaftar
$sql_admin = mysqli_query($conn, "SELECT * FROM user WHERE role='Admin'");
$admin_exists = mysqli_num_rows($sql_admin) > 0;

if ($role == "Kepala Sekolah" && $kepala_sekolah_exists) {
  echo "<script type='text/javascript'>
          setTimeout(function () { 
            swal({
                title: 'Kepala Sekolah Telah Terdaftar',
                text:  'Hanya Boleh Menginput satu Kepala Sekolah saja!',
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
} else if ($role == "Admin" && $admin_exists) {
  echo "<script type='text/javascript'>
          setTimeout(function () { 
            swal({
                title: 'Admin Telah Terdaftar',
                text:  'Hanya Boleh Menginput satu Admin saja!',
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

if ($role == "Admin" || $role == "Kepala Sekolah") {
  if (!in_array($ext, $ekstensi)) {
    echo "<script type='text/javascript'>
            setTimeout(function () { 
              swal({
                  title: 'Data Gagal Ditambah',
                  text:  'Silahkan Coba Lagi!',
                  type: 'error',
                  timer: 3000,
                  showConfirmButton: true
              });   
            });  
            window.setTimeout(function(){ 
              window.history.go(-1);
            } ,900); 
          </script>";
  } else {
    if ($ukuran < 204488000) {
      $sqlUser = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
      if (mysqli_fetch_array($sqlUser)) {
        echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                      title: 'Username Telah Terdaftar',
                      text:  'Silahkan Cari Username Yang Lain!',
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

      $xx = $rand . '_' . $namafile;
      move_uploaded_file($_FILES['fotouser']['tmp_name'], 'fotouser/' . $xx);
      mysqli_query($conn, "INSERT INTO user VALUES('','$username','$password','$email','$namalengkap','$nip','$alamat','$xx','$role', '$hakakses','$jabatanid','$pangkatid','$golonganid','$idpekerja')");
      echo "<script type='text/javascript'>
              setTimeout(function () { 
                swal({
                    title: 'Data Berhasil Ditambah',
                    text:  'Data Segera Ditampilkan!',
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
      echo "<script type='text/javascript'>
              setTimeout(function () { 
                swal({
                    title: 'Ukuran Gambar Terlalu Besar',
                    text:  'Silahkan Cari Gambar Lain Atau Perkecil Size Gambar!',
                    type: 'warning',
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
} else if ($role != $jabatan) {
  echo "<script type='text/javascript'>
          setTimeout(function () { 
            swal({
                title: 'Status Akses Tak Sesuai Dengan Jabatan!',
                text:  'Usahakan Status Akses Sesuai Dengan Jabatan Pekerja',
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
} else if ($data_akun_pekerja > 0) {
  echo "<script type='text/javascript'>
          setTimeout(function () { 
            swal({
                title: 'Pemilik Akun Sudah Terdaftar!',
                text:  'Para Pekerja Hanya Boleh Memiliki Maksimal Satu Akun Saja',
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
  if (!in_array($ext, $ekstensi)) {
    echo "<script type='text/javascript'>
            setTimeout(function () { 
              swal({
                  title: 'Data Gagal Ditambah',
                  text:  'Silahkan Coba Lagi!',
                  type: 'error',
                  timer: 3000,
                  showConfirmButton: true
              });   
            });  
            window.setTimeout(function(){ 
              window.history.go(-1);
            } ,900); 
          </script>";
  } else {
    if ($ukuran < 204488000) {
      $sqlUser = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
      if (mysqli_fetch_array($sqlUser)) {
        echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                      title: 'Username Telah Terdaftar',
                      text:  'Silahkan Cari Username Yang Lain!',
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

      $xx = $rand . '_' . $namafile;
      move_uploaded_file($_FILES['fotouser']['tmp_name'], 'fotouser/' . $xx);
      mysqli_query($conn, "INSERT INTO user VALUES('','$username','$password','$email','$namalengkap','$nip','$alamat','$xx','$role', '$hakakses','$jabatanid','$pangkatid','$golonganid','$idpekerja')");
      echo "<script type='text/javascript'>
              setTimeout(function () { 
                swal({
                    title: 'Data Berhasil Ditambah',
                    text:  'Data Segera Ditampilkan!',
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
      echo "<script type='text/javascript'>
              setTimeout(function () { 
                swal({
                    title: 'Ukuran Gambar Terlalu Besar',
                    text:  'Silahkan Cari Gambar Lain Atau Perkecil Size Gambar!',
                    type: 'warning',
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
}

?>