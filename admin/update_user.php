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

$userid = $_POST['userid'];
$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$namalengkap = $_POST['namalengkap'];
$nip = $_POST['nip'];
$alamat = $_POST['alamat'];
$role = $_POST['role'];
$hakakses = $_POST['hakakses'];
$idpekerja = $_POST['idpekerja'];

// $sql = mysqli_query($conn, "UPDATE album SET namaalbum='$namaalbum', deskripsi='$deskripsi' WHERE albumid='$albumid'");

// if($sql) {
//     echo '<script>alert("Data berhasil diubah!"); window.location.href = "album.php"</script>';
// } else {
//     echo '<script>alert("Data gagal diubah!"); window.location.href = "album.php"</script>';
// }

$sql_jadwal_absen = mysqli_query($conn, "SELECT pekerja.*, pangkat.*, jabatan.*, golongan.*
            FROM pekerja 
            INNER JOIN pangkat ON pekerja.pangkatid = pangkat.pangkatid 
            INNER JOIN jabatan ON pekerja.jabatanid = jabatan.jabatanid
            INNER JOIN golongan ON pekerja.golonganid = golongan.golonganid
            WHERE pekerja.idpekerja = '$idpekerja'");
$data_jadwal_absen = mysqli_fetch_array($sql_jadwal_absen);

$jabatan = $data_jadwal_absen['jabatan'];
$jabatanid = $data_jadwal_absen['jabatanid'];
$pangkatid = $data_jadwal_absen['pangkatid'];
$golonganid = $data_jadwal_absen['golonganid'];

$sql_akun_pekerja = mysqli_query($conn, "SELECT * FROM user WHERE idpekerja = '$idpekerja'");
$data_akun_pekerja = mysqli_num_rows($sql_akun_pekerja);

// Cek apakah Kepala Sekolah sudah terdaftar
$sql_kepala_sekolah = mysqli_query($conn, "SELECT * FROM user WHERE role='Kepala Sekolah'");
$kepala_sekolah_exists = mysqli_num_rows($sql_kepala_sekolah) > 1;

// Cek apakah Admin sudah terdaftar
$sql_admin = mysqli_query($conn, "SELECT * FROM user WHERE role='Admin'");
$admin_exists = mysqli_num_rows($sql_admin) > 1;

if ($role == "Kepala Sekolah" && $kepala_sekolah_exists) {
  echo "<script type='text/javascript'>
          setTimeout(function () { 
            swal({
                title: 'Kepala Sekolah Telah Ada',
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
  if ($_FILES['fotouser']['name'] != "") {
    $rand = rand();
    $ekstensi = array("png", "jpeg", "jpg", "gif");
    $namafile = $_FILES['fotouser']['name'];
    $ukuran = $_FILES['fotouser']['size'];
    $ext = pathinfo($namafile, PATHINFO_EXTENSION);

    if (!in_array($ext, $ekstensi)) {
      // echo '<script>alert("Data gagal diubah!"); window.location.href = "index.php"</script>';
      echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                          title: 'Data Gagal Diubah',
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
      if ($ukuran < 20488000) {
        $sql = mysqli_query($conn, "SELECT fotouser FROM user where userid='$userid'");
        $data = mysqli_fetch_array($sql);
        $fotouser = $data['fotouser'];
        unlink("fotouser/$fotouser");

        $xx = $rand . '_' . $namafile;
        move_uploaded_file($_FILES['fotouser']['tmp_name'], 'fotouser/' . $rand . '_' . $namafile);
        mysqli_query($conn, "UPDATE user SET username='$username', password='$password', fotouser='$xx', email='$email', namalengkap='$namalengkap', nip='$nip', alamat='$alamat', role='$role', hakakses='$hakakses', jabatanid='$jabatanid', pangkatid='$pangkatid', golonganid='$golonganid', idpekerja='$idpekerja' WHERE userid='$userid'");
        // echo '<script>alert("Data berhasil diubah, harap login ulang!"); window.location.href = "index.php"</script>';

        echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                          title: 'Data Berhasil Diubah',
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

        if ($role == "Admin") {
          $sql_login_ulang = mysqli_query($conn, "SELECT * FROM user WHERE (username='$username' OR email='$username') AND password='$password'");

          $cek_login_ulang = mysqli_num_rows($sql_login_ulang);

          if ($cek_login_ulang == 1) {
            while ($data = mysqli_fetch_array($sql_login_ulang)) {
              $_SESSION['userid'] = $data['userid'];
              $_SESSION['username'] = $data['username'];
              $_SESSION['password'] = $data['password'];
              $_SESSION['email'] = $data['email'];
              $_SESSION['namalengkap'] = $data['namalengkap'];
              $_SESSION['nip'] = $data['nip'];
              $_SESSION['fotouser'] = $data['fotouser'];
              $_SESSION['alamat'] = $data['alamat'];
              $_SESSION['role'] = $data['role'];
              $_SESSION['hakakses'] = $data['hakakses'];
              $_SESSION['jabatanid'] = $data['jabatanid'];
              $_SESSION['pangkatid'] = $data['pangkatid'];
              $_SESSION['golonganid'] = $data['golonganid'];
              $_SESSION['idpekerja'] = $data['idpekerja'];
            }
          }
        }
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
  } else {
    mysqli_query($conn, "UPDATE user SET username='$username', password='$password', email='$email', namalengkap='$namalengkap', nip='$nip', alamat='$alamat', role='$role', hakakses='$hakakses', jabatanid='$jabatanid', pangkatid='$pangkatid', golonganid='$golonganid', idpekerja='$idpekerja' WHERE userid='$userid'");
    // echo '<script>alert("Data berhasil diubah, harap login ulang!"); window.location.href = "index.php"</script>';
    echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                          title: 'Data Berhasil Diubah',
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
    if ($role == "Admin") {
      $sql_login_ulang = mysqli_query($conn, "SELECT * FROM user WHERE (username='$username' OR email='$username') AND password='$password'");

      $cek_login_ulang = mysqli_num_rows($sql_login_ulang);

      if ($cek_login_ulang == 1) {
        while ($data = mysqli_fetch_array($sql_login_ulang)) {
          $_SESSION['userid'] = $data['userid'];
          $_SESSION['username'] = $data['username'];
          $_SESSION['password'] = $data['password'];
          $_SESSION['email'] = $data['email'];
          $_SESSION['namalengkap'] = $data['namalengkap'];
          $_SESSION['nip'] = $data['nip'];
          $_SESSION['fotouser'] = $data['fotouser'];
          $_SESSION['alamat'] = $data['alamat'];
          $_SESSION['role'] = $data['role'];
          $_SESSION['hakakses'] = $data['hakakses'];
          $_SESSION['jabatanid'] = $data['jabatanid'];
          $_SESSION['pangkatid'] = $data['pangkatid'];
          $_SESSION['golonganid'] = $data['golonganid'];
          $_SESSION['idpekerja'] = $data['idpekerja'];
        }
      }
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
} else if ($data_akun_pekerja > 1) {
  echo "<script type='text/javascript'>
          setTimeout(function () { 
            swal({
                title: 'Proses Ubah Data Gagal Dilakukan!',
                text:  'Dideteksi pekerja mempunyai lebih dari 1. Harap masing-masing pekerja memiliki satu akun saja!',
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
  if ($_FILES['fotouser']['name'] != "") {
    $rand = rand();
    $ekstensi = array("png", "jpeg", "jpg", "gif");
    $namafile = $_FILES['fotouser']['name'];
    $ukuran = $_FILES['fotouser']['size'];
    $ext = pathinfo($namafile, PATHINFO_EXTENSION);

    if (!in_array($ext, $ekstensi)) {
      // echo '<script>alert("Data gagal diubah!"); window.location.href = "index.php"</script>';
      echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                          title: 'Data Gagal Diubah',
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
      if ($ukuran < 20488000) {
        $sql = mysqli_query($conn, "SELECT fotouser FROM user where userid='$userid'");
        $data = mysqli_fetch_array($sql);
        $fotouser = $data['fotouser'];
        unlink("fotouser/$fotouser");

        $xx = $rand . '_' . $namafile;
        move_uploaded_file($_FILES['fotouser']['tmp_name'], 'fotouser/' . $rand . '_' . $namafile);
        mysqli_query($conn, "UPDATE user SET username='$username', password='$password', fotouser='$xx', email='$email', namalengkap='$namalengkap', nip='$nip', alamat='$alamat', role='$role', hakakses='$hakakses', jabatanid='$jabatanid', pangkatid='$pangkatid', golonganid='$golonganid', idpekerja='$idpekerja' WHERE userid='$userid'");
        // echo '<script>alert("Data berhasil diubah, harap login ulang!"); window.location.href = "index.php"</script>';

        echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                          title: 'Data Berhasil Diubah',
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
  } else {
    mysqli_query($conn, "UPDATE user SET username='$username', password='$password', email='$email', namalengkap='$namalengkap', nip='$nip', alamat='$alamat', role='$role', hakakses='$hakakses', jabatanid='$jabatanid', pangkatid='$pangkatid', golonganid='$golonganid', idpekerja='$idpekerja' WHERE userid='$userid'");
    // echo '<script>alert("Data berhasil diubah, harap login ulang!"); window.location.href = "index.php"</script>';
    echo "<script type='text/javascript'>
                setTimeout(function () { 
                  swal({
                          title: 'Data Berhasil Diubah',
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
  }
}

?>