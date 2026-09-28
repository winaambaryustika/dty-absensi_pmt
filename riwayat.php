```php
<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/database.php";

// =============================
// FILTER
// =============================

$tanggal = isset($_GET['tanggal'])
    ? $_GET['tanggal']
    : date("Y-m-d");

$keyword = isset($_GET['keyword'])
    ? trim($_GET['keyword'])
    : "";


// =============================
// QUERY RIWAYAT
// =============================

$sql = "SELECT
            absensi.id,
            absensi.tanggal,
            absensi.jam_masuk,
            absensi.jam_pulang,
            absensi.status,
            absensi.keterangan,
            karyawan.nik,
            karyawan.nama,
            karyawan.departemen,
            karyawan.jabatan
        FROM absensi
        INNER JOIN karyawan
            ON absensi.karyawan_id = karyawan.id
        WHERE absensi.tanggal = ?";

$params = [$tanggal];
$types = "s";


// =============================
// FILTER PENCARIAN
// =============================

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


// =============================
// URUTKAN DATA
// =============================

$sql .= " ORDER BY absensi.jam_masuk ASC";


// =============================
// JALANKAN QUERY
// =============================

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


// =============================
// STATISTIK
// =============================

$total = mysqli_num_rows($result);

$data = [];

$hadir = 0;
$terlambat = 0;

while ($row = mysqli_fetch_assoc($result)) {

    $data[] = $row;

    if ($row['status'] == "Hadir") {
        $hadir++;
    }

    if ($row['status'] == "Terlambat") {
        $terlambat++;
    }
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Absensi - DTY</title>


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


        /* =========================
           SIDEBAR
        ========================= */

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

            font-size: 24px;

        }


        .logo p {

            font-size: 12px;

            margin-top: 5px;

            color: #cbd8e3;

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


        /* =========================
           CONTENT
        ========================= */

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


        /* =========================
           CARD
        ========================= */

        .card {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.08);

        }


        /* =========================
           FILTER
        ========================= */

        .filter {

            display: flex;

            gap: 15px;

            align-items: end;

        }


        .form-group {

            flex: 1;

        }


        label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

        }


        input {

            width: 100%;

            padding: 12px;

            border: 1px solid #ddd;

            border-radius: 7px;

            font-size: 14px;

        }


        button {

            border: none;

            padding: 12px 20px;

            border-radius: 7px;

            cursor: pointer;

            font-weight: bold;

            color: white;

        }


        .btn-filter {

            background: #123b5d;

        }


        .btn-filter:hover {

            background: #0d2d46;

        }


        .btn-reset {

            background: #6c757d;

            text-decoration: none;

            color: white;

            padding: 12px 20px;

            border-radius: 7px;

            display: inline-block;

        }


        .btn-reset:hover {

            background: #5c636a;

        }


        .btn-export {

            background: #198754;

            text-decoration: none;

            color: white;

            padding: 12px 20px;

            border-radius: 7px;

            display: inline-block;

            white-space: nowrap;

        }


        .btn-export:hover {

            background: #157347;

        }


        /* =========================
           STATISTIK
        ========================= */

        .stats {

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 25px;

        }


        .stat {

            background: white;

            padding: 22px;

            border-radius: 12px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.08);

        }


        .stat h3 {

            font-size: 14px;

            color: #777;

            margin-bottom: 10px;

        }


        .stat p {

            font-size: 28px;

            font-weight: bold;

            color: #123b5d;

        }


        /* =========================
           TABLE
        ========================= */

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


        /* =========================
           STATUS
        ========================= */

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


        .kosong {

            text-align: center;

            padding: 30px;

            color: #777;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .filter {

                flex-wrap: wrap;

            }

            .form-group {

                min-width: 200px;

            }

        }


        @media (max-width: 768px) {

            .sidebar {

                width: 200px;

            }


            .content {

                margin-left: 200px;

            }


            .filter {

                flex-direction: column;

                align-items: stretch;

            }


            .stats {

                grid-template-columns: 1fr;

            }


            .btn-export,
            .btn-reset,
            .btn-filter {

                text-align: center;

            }

        }

    </style>

    <link rel="stylesheet" href="assets/css/industrial-theme.css">

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">


    <div class="logo">

        <h2>DTY ABSENSI</h2>

        <p>Sistem Absensi Karyawan</p>

    </div>


    <div class="menu">

        <a href="dashboard.php">

            🏠 Dashboard

        </a>


        <a href="karyawan.php">

            👥 Data Karyawan

        </a>


        <a href="absensi.php">

            🕐 Absensi

        </a>


        <a href="riwayat.php" class="active">

            📋 Riwayat Absensi

        </a>

    </div>


    <div class="logout">

        <a href="logout.php">

            🚪 Logout

        </a>

    </div>


</div>


<!-- =========================
     CONTENT
========================= -->

<div class="content">


    <div class="header">

        <h1>

            Riwayat Absensi

        </h1>


        <p>

            Melihat data kehadiran karyawan berdasarkan tanggal

        </p>

    </div>


    <!-- =========================
         FILTER
    ========================= -->

    <div class="card">

        <form method="GET">

            <div class="filter">


                <div class="form-group">

                    <label>

                        Tanggal

                    </label>


                    <input

                        type="date"

                        name="tanggal"

                        value="<?= htmlspecialchars($tanggal) ?>"

                    >

                </div>


                <div class="form-group">

                    <label>

                        Cari Karyawan

                    </label>


                    <input

                        type="text"

                        name="keyword"

                        placeholder="NIK, nama, departemen..."

                        value="<?= htmlspecialchars($keyword) ?>"

                    >

                </div>


                <button

                    type="submit"

                    class="btn-filter">

                    🔍 Filter

                </button>


                <a

                    href="riwayat.php"

                    class="btn-reset">

                    Reset

                </a>


                <a

                    href="export_absensi.php?tanggal=<?= urlencode($tanggal) ?>&keyword=<?= urlencode($keyword) ?>"

                    class="btn-export">

                    📥 Export CSV

                </a>


            </div>

        </form>

    </div>


    <!-- =========================
         STATISTIK
    ========================= -->

    <div class="stats">


        <div class="stat">

            <h3>

                Total Absensi

            </h3>


            <p>

                <?= $total ?>

            </p>

        </div>


        <div class="stat">

            <h3>

                Hadir

            </h3>


            <p>

                <?= $hadir ?>

            </p>

        </div>


        <div class="stat">

            <h3>

                Terlambat

            </h3>


            <p>

                <?= $terlambat ?>

            </p>

        </div>


    </div>


    <!-- =========================
         TABEL
    ========================= -->

    <div class="card">


        <h2 style="margin-bottom:20px; color:#123b5d;">

            Data Absensi

            <?= date("d-m-Y", strtotime($tanggal)) ?>

        </h2>


        <div class="table-wrapper">


            <table>


                <thead>

                    <tr>

                        <th>No</th>

                        <th>NIK</th>

                        <th>Nama</th>

                        <th>Departemen</th>

                        <th>Jabatan</th>

                        <th>Jam Masuk</th>

                        <th>Jam Pulang</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                    <?php if (count($data) > 0): ?>


                        <?php $no = 1; ?>


                        <?php foreach ($data as $row): ?>


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

                                    <?= htmlspecialchars($row['jabatan']) ?>

                                </td>


                                <td>

                                    <?= $row['jam_masuk'] ?? '-' ?>

                                </td>


                                <td>

                                    <?= $row['jam_pulang'] ?? '-' ?>

                                </td>


                                <td>


                                    <span

                                        class="status <?= strtolower($row['status']) ?>">

                                        <?= htmlspecialchars($row['status']) ?>

                                    </span>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>


                            <td

                                colspan="8"

                                class="kosong">

                                Tidak ada data absensi pada tanggal tersebut.

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
```
