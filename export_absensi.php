<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/database.php";

$tanggal = isset($_GET['tanggal'])
    ? $_GET['tanggal']
    : date("Y-m-d");

$keyword = isset($_GET['keyword'])
    ? trim($_GET['keyword'])
    : "";

$sql = "SELECT
            absensi.tanggal,
            karyawan.nik,
            karyawan.nama,
            karyawan.departemen,
            karyawan.jabatan,
            absensi.jam_masuk,
            absensi.jam_pulang,
            absensi.status,
            absensi.keterangan
        FROM absensi
        INNER JOIN karyawan
            ON absensi.karyawan_id = karyawan.id
        WHERE absensi.tanggal = ?";

$params = [$tanggal];
$types = "s";

if ($keyword != "") {

    $sql .= " AND (
                karyawan.nik LIKE ?
                OR karyawan.nama LIKE ?
                OR karyawan.departemen LIKE ?
                OR karyawan.jabatan LIKE ?
              )";

    $search = "%" . $keyword . "%";

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types .= "ssss";
}

$sql .= " ORDER BY karyawan.nama ASC";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


// Nama file
$nama_file = "laporan_absensi_" . $tanggal . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="' . $nama_file . '"'
);


// Output
$output = fopen('php://output', 'w');


// BOM supaya Excel membaca UTF-8 dengan benar
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));


// Judul laporan
fputcsv($output, [
    "LAPORAN ABSENSI KARYAWAN DTY"
]);

fputcsv($output, [
    "Tanggal",
    date("d-m-Y", strtotime($tanggal))
]);

fputcsv($output, []);


// Header tabel
fputcsv($output, [
    "No",
    "Tanggal",
    "NIK",
    "Nama",
    "Departemen",
    "Jabatan",
    "Jam Masuk",
    "Jam Pulang",
    "Status",
    "Keterangan"
]);


// Data
$no = 1;

while ($row = mysqli_fetch_assoc($result)) {

    fputcsv($output, [
        $no++,
        date("d-m-Y", strtotime($row['tanggal'])),
        $row['nik'],
        $row['nama'],
        $row['departemen'],
        $row['jabatan'],
        $row['jam_masuk'] ?? "-",
        $row['jam_pulang'] ?? "-",
        $row['status'],
        $row['keterangan'] ?? "-"
    ]);

}

fclose($output);

exit;