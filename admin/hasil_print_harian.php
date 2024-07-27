<?php
include "koneksi.php";
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location:../login.php");
} else if (!isset($_SESSION['kodeakses'])) {
    header("Location:../login_akses.php");
} else if (isset($_SESSION['userid']) && $_SESSION['role'] != "Admin" && $_SESSION['role'] != "STAF") {
    header("Location:index.php");
}

// Load TCPDF library
require_once("tcpdf/tcpdf.php");

// Create new PDF document
$pdf = new TCPDF('l', 'mm', 'F4', true, 'UTF-8', true);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->setMargins(3, 3, 3);
$pdf->setAutoPageBreak(true, 1);

// Add a page
$pdf->AddPage();

// Set font
$pdf->setFont('helvetica', '', 12);

$tanggal = $_POST['tanggal'];
$jabatanid = $_POST['jabatanid'];

// Query to get the jabatan name
$sql = "SELECT * FROM jabatan WHERE jabatanid = '$jabatanid'";
$result = mysqli_query($conn, $sql);
$namajabatan = '';
while ($row = mysqli_fetch_assoc($result)) {
    $namajabatan = $row['jabatan'];
}

$namawali = "-"; // Nilai default
$sql_kepala_sekolah = mysqli_query($conn, "SELECT * FROM pekerja,jabatan WHERE pekerja.jabatanid=jabatan.jabatanid AND jabatan.jabatan='KEPALA SEKOLAH'");
$data_kepala_sekolah = mysqli_fetch_array($sql_kepala_sekolah);
$namaKepalaSekolah = $data_kepala_sekolah['nama'];
$nipKepalaSekolah = $data_kepala_sekolah['nip'];

if (isset($_SESSION['jabatanid']) != 0) {
    $sql_wali = mysqli_query($conn, "SELECT namalengkap FROM user WHERE jabatanid='$jabatanid'");
    if ($data_wali = mysqli_fetch_array($sql_wali)) {
        $namawali = $data_wali['namalengkap'];
    }
} else if (isset($_SESSION['jabatanid']) == 0) {
    $sql_wali = "SELECT pekerja.*, pangkat.*, absen.*, jabatan.*
    FROM pekerja 
    INNER JOIN absen ON pekerja.nip = absen.nip 
    INNER JOIN pangkat ON pekerja.pangkatid = pangkat.pangkatid 
    INNER JOIN golongan ON pekerja.golonganid = golongan.golonganid 
    INNER JOIN jabatan ON pekerja.jabatanid = jabatan.jabatanid
    WHERE pekerja.jabatanid = '$jabatanid'";

    $result_wali = mysqli_query($conn, $sql_wali);
    if ($data_wali = mysqli_fetch_array($result_wali)) {
        $namawali = $data_wali['namalengkap'];
    }
}

function fetch_data()
{
    $output = '';
    $conn = mysqli_connect("localhost", "root", "", "absensiqrcodepekerjasmknlabuang");
    date_default_timezone_set('Asia/Makassar');
    $tanggal = $_POST['tanggal'];
    $jabatanid = $_POST['jabatanid'];

    $sql = "SELECT pekerja.*, pangkat.*, golongan.*, absen.*, jabatan.*
    FROM pekerja 
    INNER JOIN absen ON pekerja.nip = absen.nip 
    INNER JOIN pangkat ON pekerja.pangkatid = pangkat.pangkatid 
    INNER JOIN golongan ON pekerja.golonganid = golongan.golonganid 
    INNER JOIN jabatan ON pekerja.jabatanid = jabatan.jabatanid
    WHERE absen.tanggal = '$tanggal' AND pekerja.jabatanid = '$jabatanid'
    ORDER BY pekerja.nama ASC";
    $result = mysqli_query($conn, $sql);

    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $tanggal_dari_database = $row['tanggal'];
        $tanggal_baru = date('d-m-Y', strtotime($tanggal_dari_database));

        $waktu_datang = ($row['waktudatang'] != '00:00:00') ? $row['waktudatang'] . ' WITA<br>' . $row['keterangandatang'] : '-';
        $waktu_pulang = ($row['waktupulang'] != '00:00:00') ? $row['waktupulang'] . ' WITA<br>' . $row['keteranganpulang'] : '-';

        $output .= '
        <tr>
            <td align="center">' . $no++ . '.</td>
            <td align="left">' . $row['nama'] . '<br>NIP: ' . $row['nip'] . '</td>
            <td align="left">' . $row['pangkat'] . '/' . $row['golongan'] . '</td> 
            <td align="center">' . $row['jabatan'] . '</td>
            <td align="center">' . $row['jk'] . '</td> 
            <td align="center">' . $waktu_datang . '</td> 
            <td align="center">' . $waktu_pulang . '</td> 
        </tr>';
    }
    return $output;
}

$content = '
<table border="1" style="padding-top: 5px; padding-bottom: 5px;">
<tr bgcolor="skyblue">
    <th align="center" width="30px"><b>No</b></th>
    <th align="center" width="188px"><b>Nama Pekerja / NIP</b></th>
    <th align="center" width="170px"><b>Pangkat / Golongan</b></th>
    <th align="center" width="160px"><b>Jabatan</b></th>
    <th align="center" width="90px"><b>Jenis Kelamin</b></th>
    <th align="center" width="135px"><b>Waktu Datang</b></th>
    <th align="center" width="135px"><b>Waktu Pulang</b></th>
</tr>';

$content .= fetch_data(); // Menggunakan output dari fetch_data()
$content .= '
</table>
<div style="width: 100%;"><br><br><br><br><br><br></div>'; // Tambahkan div sebagai pemisah

$header = '
<table width="100%">
    <tr>
        <td width="100px" align="center"><img src="logo/smkn_labuang.jpg" width="65px"></td>
        <td width="710px" align="center">
            <br>
            <b>PEMERINTAH PROVINSI SULAWESI BARAT<br>DINAS PENDIDIKAN DAN KEBUDAYAAN<br>UPTD SMK NEGERI LABUANG<br><font style="font-weight: normal;">Jl. Poros Majene, Laliko, Campalagian, Kabupaten Polewali Mandar, Sulawesi Barat 91353, Indonesia</font></b>
        </td>
        <td width="100px" align="center"><img src="logo/logo-provinsi-sulawesi-barat.jpg" width="65px"></td>
    </tr>
</table>
<hr style="height: 2px;">
<table>
    <tr>
        <td>Daftar Absensi Harian Pekerja/Jabatan : ' . $namajabatan . '</td>
        <td align="right">Tanggal : ' . date('d-m-Y', strtotime($tanggal)) . '</td>
    </tr>
</table>
<hr style="height: 3px; color: white;">';

// Output the HTML content with header
$pdf->writeHTML($header . $content, true, true, true, true, '');

// Define a function to set the footer
function setFooter($pdf, $tanggal, $namaKepalaSekolah, $nipKepalaSekolah)
{
    // Array hari dalam Bahasa Indonesia
    $hari = array(
        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
        5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
    );

    // Array bulan dalam Bahasa Indonesia
    $bulan = array(
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    );

    $timestamp = strtotime($tanggal);
    $day = date('j', $timestamp);
    $month_number = (int)date('n', $timestamp);
    $year = date('Y', $timestamp);

    // Mendapatkan hari berdasarkan tanggal
    $day_of_week = date('N', $timestamp);

    $footer = '
    <table width="100%">
        <tr>
            <td width="83%"></td>
            <td width="17%" align="left">Labuang, ' . $hari[$day_of_week] . ', ' . $day . ' ' . $bulan[$month_number] . ' ' . $year . '<br>Kepala UPTD SMKN Labuang<br><img src="default/ttd_kepala_sekolah_bapak_darwis.jpg" width="150px">
                <div style="border-bottom: 1px solid black;">' . $namaKepalaSekolah . '</div>NIP. ' . $nipKepalaSekolah . '
            </td>
        </tr>
    </table>';
    $pdf->SetY(-48); // Adjust the position to create more space from the table
    $pdf->SetFont('helvetica', '', 10); // Set font for the footer
    $pdf->writeHTML($footer, true, false, true, false, 'R'); // Add the text on the right side
}

// Set footer dengan teks "Labuang, tanggal hari ini" dalam Bahasa Indonesia berdasarkan $tanggal dari database
setFooter($pdf, $tanggal, $namaKepalaSekolah, $nipKepalaSekolah);

// Close and output PDF document
$pdf->Output('Data Absensi Pekerja Jabatan ' . $namajabatan . ' ' . date('d-m-Y', strtotime($tanggal)) . ' UPTD SMK Negeri Labuang.pdf', 'I');
