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
$pdf = new TCPDF('l', 'mm', array(300, 500), true, 'UTF-8', true);
// $pdf = new TCPDF('l', 'mm', 'F4', true, 'UTF-8', true);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->setMargins(3, 3, 3);
$pdf->setAutoPageBreak(true, 1);

// Add a page
$pdf->AddPage();

// Set font
$pdf->setFont('dejavusans', '', 11);

$tanggal = $_POST['tanggal'];
$jabatanid = $_POST['jabatanid'];

// Calculate the start and end date of the week
$timestamp = strtotime($tanggal);
$start_date = strtotime('last Monday', $timestamp);
$end_date = strtotime('next Saturday', $start_date);

// Get dates for the week excluding Sunday
$dates = [];
for ($i = $start_date; $i <= $end_date; $i = strtotime('+1 day', $i)) {
    $dates[] = date('Y-m-d', $i);
}
// $dates = [];
// for ($i = $start_date + 86400; $i <= $end_date; $i = strtotime('+1 day', $i)) {
//     $dates[] = date('Y-m-d', $i);
// }

// Memformat tanggal awal dan akhir untuk ditampilkan
$formatted_start_date = date('d-m-Y', $start_date);
$formatted_end_date = date('d-m-Y', $end_date);

// Query to get the jabatan name
$sql = "SELECT * FROM jabatan WHERE jabatanid = '$jabatanid'";
$result = mysqli_query($conn, $sql);
$namajabatan = '';
while ($row = mysqli_fetch_assoc($result)) {
    $namajabatan = $row['jabatan'];
}

$namawali = "-"; // Nilai default
$sql_kepala_sekolah = mysqli_query($conn, "SELECT * FROM pekerja, jabatan WHERE pekerja.jabatanid=jabatan.jabatanid AND jabatan.jabatan='KEPALA SEKOLAH'");
$data_kepala_sekolah = mysqli_fetch_array($sql_kepala_sekolah);
$namaKepalaSekolah = $data_kepala_sekolah['nama'];
$nipKepalaSekolah = $data_kepala_sekolah['nip'];

if (isset($_SESSION['jabatanid']) && $_SESSION['jabatanid'] != 0) {
    $sql_wali = mysqli_query($conn, "SELECT namalengkap FROM user WHERE jabatanid='$jabatanid'");
    if ($data_wali = mysqli_fetch_array($sql_wali)) {
        $namawali = $data_wali['namalengkap'];
    }
} else if (isset($_SESSION['jabatanid']) && $_SESSION['jabatanid'] == 0) {
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

function fetch_data($conn, $jabatanid, $dates)
{
    $output = '';
    $no = 1;

    // Adjusted query to fetch attendance data including arrival and departure times
    $sql = "SELECT pekerja.*, pangkat.*, golongan.*, jabatan.*, absen.*, 
            GROUP_CONCAT(DISTINCT DATE_FORMAT(absen.tanggal, '%Y-%m-%d') SEPARATOR ',') AS attendance_dates,
            GROUP_CONCAT(DISTINCT IF(absen.waktudatang != '00:00:00', CONCAT(DATE_FORMAT(absen.tanggal, '%Y-%m-%d'), '|', absen.waktudatang, '|', IF(absen.keterangandatang IS NOT NULL, absen.keterangandatang, '')), NULL) SEPARATOR ',') AS hadir_datang,
            GROUP_CONCAT(DISTINCT IF(absen.waktupulang != '00:00:00', CONCAT(DATE_FORMAT(absen.tanggal, '%Y-%m-%d'), '|', absen.waktupulang, '|', IF(absen.keteranganpulang IS NOT NULL, absen.keteranganpulang, '')), NULL) SEPARATOR ',') AS hadir_pulang
            FROM pekerja 
            LEFT JOIN absen ON pekerja.nip = absen.nip 
            INNER JOIN pangkat ON pekerja.pangkatid = pangkat.pangkatid 
            INNER JOIN golongan ON pekerja.golonganid = golongan.golonganid 
            INNER JOIN jabatan ON pekerja.jabatanid = jabatan.jabatanid
            WHERE pekerja.jabatanid = '$jabatanid'
            GROUP BY pekerja.nip
            ORDER BY pekerja.nama ASC";

    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $attendance_dates = explode(',', $row['attendance_dates']);
        $attendance_dates = array_map('trim', $attendance_dates);

        $hadir_datang = explode(',', $row['hadir_datang']);
        $hadir_datang = array_map('trim', $hadir_datang);

        $hadir_pulang = explode(',', $row['hadir_pulang']);
        $hadir_pulang = array_map('trim', $hadir_pulang);

        $pangkat = $row['pangkat'] == "Tidak Memiliki" ? "-" : $row['pangkat'];
        $golongan = $row['golongan'] == "Tidak Memiliki" ? "-" : $row['golongan'];
        $nipCleaned = str_replace(' ', '', $row['nip']);
        $nip = is_numeric($nipCleaned) ? $row['nip'] : "-";

        $output .= '
        <tr style="font-size: 9px;">
            <td align="center">' . $no++ . '.</td>
            <td align="left">' . $row['nama'] . '<br>NIP: ' . $nip . '</td>
            <td align="center">' . $pangkat . '/' . $golongan . '</td> 
            <td align="center">' . $row['jabatan'] . '</td>
            <td align="center">' . $row['jk'] . '</td>';

        // Loop through each day of the week
        foreach ($dates as $date) {
            $hadir_datang_check = '';
            $hadir_pulang_check = '';

            $waktu_datang = '';
            $hadir_datang_keterangan = '';

            $waktu_pulang = '';
            $hadir_pulang_keterangan = '';

            // foreach ($hadir_datang as $datang) {
            //     list($datang_date, $datang_time, $datang_status) = explode('|', $datang);
            //     if ($datang_date === $date) {
            //         $waktu_datang = $datang_time;
            //         $hadir_datang_keterangan = $datang_status;
            //         switch ($datang_status) {
            //             case 'Absen Cepat':
            //                 $hadir_datang_check = '<span style="color: orange;">✓</span>';
            //                 break;
            //             case 'Tepat Waktu':
            //                 $hadir_datang_check = '<span style="color: green;">✓</span>';
            //                 break;
            //             case 'Terlambat':
            //                 $hadir_datang_check = '<span style="color: red;">✓</span>';
            //                 break;
            //         }
            //         break;
            //     }
            // }

            // foreach ($hadir_pulang as $pulang) {
            //     list($pulang_date, $pulang_time, $pulang_status) = explode('|', $pulang);
            //     if ($pulang_date === $date) {
            //         $waktu_pulang = $pulang_time;
            //         $hadir_pulang_keterangan = $pulang_status;
            //         switch ($pulang_status) {
            //             case 'Pulang Tepat Waktu':
            //                 $hadir_pulang_check = '<span style="color: green;">✓</span>';
            //                 break;
            //             case 'Pulang Terlambat':
            //                 $hadir_pulang_check = '<span style="color: red;">✓</span>';
            //                 break;
            //         }
            //         break;
            //     }
            // }

            foreach ($hadir_datang as $datang) {
                $datang_parts = explode('|', $datang);
                if (isset($datang_parts[0]) && isset($datang_parts[1]) && isset($datang_parts[2])) {
                    list($datang_date, $datang_time, $datang_status) = $datang_parts;
                    if ($datang_date === $date) {
                        $waktu_datang = $datang_time;
                        $hadir_datang_keterangan = $datang_status;
                        switch ($datang_status) {
                            case 'Datang Cepat':
                                $hadir_datang_check = '<span style="color: orange;">✓</span>';
                                break;
                            case 'Datang Tepat Waktu':
                                $hadir_datang_check = '<span style="color: green;">✓</span>';
                                break;
                            case 'Datang Terlambat':
                                $hadir_datang_check = '<span style="color: red;">✓</span>';
                                break;
                        }
                        break;
                    }
                }
            }

            foreach ($hadir_pulang as $pulang) {
                $pulang_parts = explode('|', $pulang);
                if (isset($pulang_parts[0]) && isset($pulang_parts[1]) && isset($pulang_parts[2])) {
                    list($pulang_date, $pulang_time, $pulang_status) = $pulang_parts;
                    if ($pulang_date === $date) {
                        $waktu_pulang = $pulang_time;
                        $hadir_pulang_keterangan = $pulang_status;
                        switch ($pulang_status) {
                            case 'Pulang Cepat':
                                $hadir_pulang_check = '<span style="color: orange;">✓</span>';
                                break;
                            case 'Pulang Tepat Waktu':
                                $hadir_pulang_check = '<span style="color: green;">✓</span>';
                                break;
                            case 'Pulang Terlambat':
                                $hadir_pulang_check = '<span style="color: red;">✓</span>';
                                break;
                        }
                        break;
                    }
                }
            }

            $output .= '<td align="center">' . $waktu_datang . '</td>';
            $output .= '<td align="center" style="font-size: 20px;">' . $hadir_datang_check . '</td>';
            $output .= '<td align="center">' . $waktu_pulang . '</td>';
            $output .= '<td align="center" style="font-size: 20px;">' . $hadir_pulang_check . '</td>';
        }

        $output .= '
        <td></td></tr>';
    }
    return $output;
}


$content = '
<table border="1" style="padding-top: 5px; padding-bottom: 5px;">
<tr bgcolor="skyblue" style="font-size: 9px;">
    <th align="center" rowspan="4" width="25px"><div></div><div></div><b>No</b></th>
    <th align="center" rowspan="4" width="150px"><div></div><div></div><b>Nama/NIP</b></th>
    <th align="center" rowspan="4" width="110px"><div></div><div></div><b>Pangkat/Golongan</b></th>
    <th align="center" rowspan="4" width="80px"><div></div><div></div><b>Jabatan</b></th>
    <th align="center" rowspan="4" width="80px"><div></div><div></div><b>Jenis Kelamin</b></th>
    <th align="center" colspan="24" width="850px"><b>Hari</b></th>
    <th align="center" rowspan="4" width="100px"><div></div><div></div><b>Keterangan</b></th>
</tr>
<tr bgcolor="skyblue" style="font-size: 9px;">
    <th align="center" colspan="4"><b>Senin</b></th>
    <th align="center" colspan="4"><b>Selasa</b></th>
    <th align="center" colspan="4"><b>Rabu</b></th>
    <th align="center" colspan="4"><b>Kamis</b></th>
    <th align="center" colspan="4"><b>Jumat</b></th>
    <th align="center" colspan="4"><b>Sabtu</b></th>
</tr>
<tr bgcolor="skyblue" style="font-size: 9px;">
    <th align="center" colspan="2"><b>Datang</b></th>
    <th align="center" colspan="2"><b>Pulang</b></th>
    <th align="center" colspan="2"><b>Datang</b></th>
    <th align="center" colspan="2"><b>Pulang</b></th>
    <th align="center" colspan="2"><b>Datang</b></th>
    <th align="center" colspan="2"><b>Pulang</b></th>
    <th align="center" colspan="2"><b>Datang</b></th>
    <th align="center" colspan="2"><b>Pulang</b></th>
    <th align="center" colspan="2"><b>Datang</b></th>
    <th align="center" colspan="2"><b>Pulang</b></th>
    <th align="center" colspan="2"><b>Datang</b></th>
    <th align="center" colspan="2"><b>Pulang</b></th>
</tr>
<tr bgcolor="skyblue" style="font-size: 9px;">
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
    <th align="center"><b>Jam</b></th>
    <th align="center"><b>✓</b></th>
</tr>';

$content .= fetch_data($conn, $jabatanid, $dates); // Menggunakan output dari fetch_data()
$content .= '
</table>
<div style="width: 100%;"><br><br><br><br><br><br></div>'; // Tambahkan div sebagai pemisah

$header = '
<table width="100%">
    <tr>
        <td width="10%" align="center"><img src="logo/smkn_labuang.jpg" width="65px"></td>
        <td width="80%" align="center">
            <br>
            <b>PEMERINTAH PROVINSI SULAWESI BARAT<br>DINAS PENDIDIKAN DAN KEBUDAYAAN<br>UPTD SMK NEGERI LABUANG<br><font style="font-weight: normal;">Jl. Poros Majene, Laliko, Campalagian, Kabupaten Polewali Mandar, Sulawesi Barat 91353, Indonesia</font></b>
        </td>
        <td width="10%" align="center"><img src="logo/logo-provinsi-sulawesi-barat.jpg" width="65px"></td>
    </tr>
</table>
<hr style="height: 2px;">
<table>
    <tr>
        <td>Daftar Absensi Mingguan Pekerja/Jabatan : ' . $namajabatan . '</td>
        <td align="right">Tanggal : ' . $formatted_start_date . ' s/d ' . $formatted_end_date . '</td>
    </tr>
</table>
<hr style="height: 3px; color: white;">';

// Output the HTML content with header
$pdf->writeHTML($header . $content, true, true, true, true, '');

// Define a function to set the footer
function setFooter($pdf, $dates, $namaKepalaSekolah, $nipKepalaSekolah)
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

    // Get the last date in the $dates array which corresponds to Saturday
    $last_date = end($dates);
    $timestamp = strtotime($last_date);
    $day = date('j', $timestamp);
    $month_number = (int)date('n', $timestamp);
    $year = date('Y', $timestamp);

    // Mendapatkan hari berdasarkan tanggal
    $day_of_week = date('N', $timestamp);

    $footer = '
    <table width="100%">
        <tr>
            <td width="89%"></td>
            <td width="11%" align="left">Labuang, ' . $hari[$day_of_week] . ', ' . $day . ' ' . $bulan[$month_number] . ' ' . $year . '<br>Kepala UPTD SMKN Labuang<br><img src="default/ttd_kepala_sekolah_bapak_darwis.jpg" width="150px">
                <div style="border-bottom: 1px solid black;">' . $namaKepalaSekolah . '</div>NIP. ' . $nipKepalaSekolah . '
            </td>
        </tr>
    </table>';
    $pdf->SetY(-52); // Adjust the position to create more space from the table
    $pdf->SetFont('helvetica', '', 10); // Set font for the footer
    $pdf->writeHTML($footer, true, false, true, false, 'R'); // Add the text on the right side
}

// Call setFooter with $dates array and other required parameters
setFooter($pdf, $dates, $namaKepalaSekolah, $nipKepalaSekolah);

// Close and output PDF document
$pdf->Output('Data Absensi Mingguan Pekerja Jabatan ' . $namajabatan . ' ' . date('d-m-Y', $start_date) . ' s-d ' . date('d-m-Y', $end_date) . ' UPTD SMK Negeri Labuang.pdf', 'I');
