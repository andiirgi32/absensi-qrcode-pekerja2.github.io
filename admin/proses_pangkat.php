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

$pangkat = $_POST['pangkat'];

$cekpangkat = mysqli_query($conn, "SELECT * FROM pangkat where pangkat='$pangkat'");

if (mysqli_num_rows($cekpangkat) == 1) {
	echo "<script type='text/javascript'>
	      setTimeout(function () { 
	        swal({
	                title: 'Data Pangkat Sudah Terdaftar',
	                text:  'Silahkan Input Dengan Nama Pangkat Lainnya!',
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

	$sql = mysqli_query($conn, "INSERT INTO pangkat VALUES('','$pangkat')");

	if ($sql) {
		echo "<script type='text/javascript'>
	      setTimeout(function () { 
	        swal({
	                title: 'Data Berhasil Ditambahkan',
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
	                title: 'Data Gagal Ditambahkan',
	                text:  'Silahkan Coba Lagi!',
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
?>