<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/database.php";

$tanggal_hari_ini = date("Y-m-d");

// =============================
// TOTAL KARYAWAN AKTIF
// =============================

$query_total = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM karyawan
     WHERE status = 'Aktif'"
);

$total_karyawan = mysqli_fetch_assoc($query_total)['total'];


// =============================
// TOTAL ABSENSI HARI INI
// =============================

$query_absensi = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM absensi
     WHERE tanggal = '$tanggal_hari_ini'"
);

$total_absensi = mysqli_fetch_assoc($query_absensi)['total'];


// =============================
// TOTAL HADIR
// =============================

$query_hadir = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM absensi
     WHERE tanggal = '$tanggal_hari_ini'
     AND status = 'Hadir'"
);

$total_hadir = mysqli_fetch_assoc($query_hadir)['total'];


// =============================
// TOTAL TERLAMBAT
// =============================

$query_terlambat = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM absensi
     WHERE tanggal = '$tanggal_hari_ini'
     AND status = 'Terlambat'"
);

$total_terlambat = mysqli_fetch_assoc($query_terlambat)['total'];


// =============================
// BELUM ABSEN
// =============================

$belum_absen = $total_karyawan - $total_absensi;

if ($belum_absen < 0) {
    $belum_absen = 0;
}


// =============================
// DATA ABSENSI HARI INI
// =============================

$query_data = mysqli_query(
    $conn,
    "SELECT
        absensi.id,
        absensi.jam_masuk,
        absensi.jam_pulang,
        absensi.status,
        karyawan.nik,
        karyawan.nama,
        karyawan.departemen,
        karyawan.jabatan
     FROM absensi
     INNER JOIN karyawan
        ON absensi.karyawan_id = karyawan.id
     WHERE absensi.tanggal = '$tanggal_hari_ini'
     ORDER BY absensi.jam_masuk ASC"
);

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - DTY Absensi</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
        }


        /* SIDEBAR */

        .sidebar {
            width: 240px;
            height: 100vh;
            background: #123b5d;
            position: fixed;
            left: 0;
            top: 0;
            padding: 25px 15px;
        }

        .logo {
            color: white;
            text-align: center;
            margin-bottom: 35px;
        }

        .logo h2 {
            font-size: 23px;
        }

        .logo p {
            font-size: 12px;
            color: #cbd8e3;
            margin-top: 5px;
        }

        .menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 13px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .menu a:hover,
        .menu a.active {
            background: #1d5d89;
        }

        .logout {
            position: absolute;
            bottom: 25px;
            left: 15px;
            right: 15px;
        }

        .logout a {
            display: block;
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 12px;
            text-align: center;
            border-radius: 8px;
        }


        /* CONTENT */

        .content {
            margin-left: 240px;
            padding: 30px;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            color: #123b5d;
            margin-bottom: 5px;
        }

        .header p {
            color: #777;
        }


        /* USER */

        .user-box {
            background: white;
            padding: 18px 22px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .user-box strong {
            color: #123b5d;
        }


        /* CARDS */

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h3 {
            font-size: 14px;
            color: #777;
            margin-bottom: 12px;
        }

        .card .number {
            font-size: 30px;
            font-weight: bold;
            color: #123b5d;
        }

        .card .description {
            margin-top: 8px;
            font-size: 12px;
            color: #999;
        }


        /* TABLE */

        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .table-card h2 {
            color: #123b5d;
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #123b5d;
            color: white;
            padding: 13px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .hadir {
            background: #d1e7dd;
            color: #0f5132;
        }

        .terlambat {
            background: #fff3cd;
            color: #664d03;
        }

        .empty {
            text-align: center;
            color: #777;
            padding: 30px;
        }


        /* RESPONSIVE */

        @media (max-width: 1000px) {

            .cards {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 768px) {

            .sidebar {
                width: 200px;
            }

            .content {
                margin-left: 200px;
            }

        }

    </style>

    <link rel="stylesheet" href="assets/css/industrial-theme.css">

</head>

<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">

        <h2>DTY ABSENSI</h2>

        <p>Sistem Absensi Karyawan</p>

    </div>


    <div class="menu">

        <a href="dashboard.php" class="active">
            🏠 Dashboard
        </a>

        <a href="karyawan.php">
            👥 Data Karyawan
        </a>

        <a href="absensi.php">
            🕐 Absensi
        </a>

        <a href="riwayat.php">
            📋 Riwayat Absensi
        </a>

    </div>


    <div class="logout">

        <a href="logout.php">
            🚪 Logout
        </a>

    </div>

</div>


<!-- CONTENT -->

<div class="content">


    <div class="header">

        <h1>
            Dashboard
        </h1>

        <p>
            Sistem Informasi Absensi Karyawan DTY
        </p>

    </div>


    <!-- USER LOGIN -->

    <div class="user-box">

        Selamat datang,
        <strong>
            <?= htmlspecialchars($_SESSION['nama']) ?>
        </strong>

        👋

        <br>

        <small>
            Login sebagai <?= htmlspecialchars($_SESSION['role']) ?>
        </small>

    </div>


    <!-- STATISTICS -->

    <div class="cards">


        <div class="card">

            <h3>
                👥 Karyawan Aktif
            </h3>

            <div class="number">
                <?= $total_karyawan ?>
            </div>

            <div class="description">
                Total karyawan aktif
            </div>

        </div>


        <div class="card">

            <h3>
                ✓ Hadir Hari Ini
            </h3>

            <div class="number">
                <?= $total_hadir ?>
            </div>

            <div class="description">
                Karyawan hadir tepat waktu
            </div>

        </div>


        <div class="card">

            <h3>
                ⏰ Terlambat
            </h3>

            <div class="number">
                <?= $total_terlambat ?>
            </div>

            <div class="description">
                Karyawan datang terlambat
            </div>

        </div>


        <div class="card">

            <h3>
                ❌ Belum Absen
            </h3>

            <div class="number">
                <?= $belum_absen ?>
            </div>

            <div class="description">
                Belum melakukan absen masuk
            </div>

        </div>


    </div>


    <!-- TODAY ATTENDANCE -->

    <div class="table-card">

        <h2>
            📋 Absensi Hari Ini
            (<?= date("d-m-Y") ?>)
        </h2>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>No</th>

                        <th>NIK</th>

                        <th>Nama</th>

                        <th>Departemen</th>

                        <th>Jam Masuk</th>

                        <th>Jam Pulang</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>

                <?php

                $no = 1;

                if (mysqli_num_rows($query_data) > 0):

                    while ($row = mysqli_fetch_assoc($query_data)):

                ?>

                    <tr>

                        <td>
                            <?= $no++ ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nik']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nama']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['departemen']) ?>
                        </td>

                        <td>
                            <?= $row['jam_masuk'] ?? '-' ?>
                        </td>

                        <td>
                            <?= $row['jam_pulang'] ?? '-' ?>
                        </td>

                        <td>

                            <span class="status <?= strtolower($row['status']) ?>">

                                <?= htmlspecialchars($row['status']) ?>

                            </span>

                        </td>

                    </tr>

                <?php

                    endwhile;

                else:

                ?>

                    <tr>

                        <td
                            colspan="7"
                            class="empty">

                            Belum ada absensi hari ini.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


</div>

</body>

</html>