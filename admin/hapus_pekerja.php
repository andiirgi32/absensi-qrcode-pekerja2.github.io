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
$idpekerja = $_GET['idpekerja'];

$sql = mysqli_query($conn, "SELECT fotopekerja FROM pekerja where idpekerja='$idpekerja'");
$data = mysqli_fetch_array($sql);
$fotopekerja = $data['fotopekerja'];
unlink("fotopekerja/$fotopekerja");

$sql2 = mysqli_query($conn, "DELETE FROM pekerja WHERE idpekerja='$idpekerja'");

if ($sql2) {
	echo "<script type='text/javascript'>
	      setTimeout(function () { 
	        swal({
	                title: 'Data Berhasil Dihapus',
	                text:  'Data Sudah Tidak Ada!',
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
	                title: 'Data Gagal Dihapus',
	                text:  'Proses Hapus Dibatalkan!',
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