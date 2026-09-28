<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/database.php";

$pesan = "";
$tipe_pesan = "";

// PROSES ABSEN MASUK
if (isset($_POST['absen_masuk'])) {

    $karyawan_id = intval($_POST['karyawan_id']);
    $tanggal = date("Y-m-d");
    $jam_masuk = date("H:i:s");

    // Cek apakah karyawan sudah absen hari ini
    $cek = mysqli_prepare($conn, 
        "SELECT id FROM absensi WHERE karyawan_id = ? AND tanggal = ?"
    );

    mysqli_stmt_bind_param($cek, "is", $karyawan_id, $tanggal);
    mysqli_stmt_execute($cek);
    $hasil_cek = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasil_cek) > 0) {

        $pesan = "Karyawan tersebut sudah melakukan absensi hari ini.";
        $tipe_pesan = "error";

    } else {

        // Batas masuk jam 08:00
        if ($jam_masuk <= "08:00:00") {
            $status = "Hadir";
        } else {
            $status = "Terlambat";
        }

        $insert = mysqli_prepare($conn,
            "INSERT INTO absensi 
            (karyawan_id, tanggal, jam_masuk, status)
            VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $insert,
            "isss",
            $karyawan_id,
            $tanggal,
            $jam_masuk,
            $status
        );

        if (mysqli_stmt_execute($insert)) {
            $pesan = "Absensi masuk berhasil dicatat.";
            $tipe_pesan = "success";
        } else {
            $pesan = "Gagal mencatat absensi.";
            $tipe_pesan = "error";
        }
    }
}

// PROSES ABSEN PULANG
if (isset($_POST['absen_pulang'])) {

    $absensi_id = intval($_POST['absensi_id']);
    $jam_pulang = date("H:i:s");

    // Pastikan sudah absen masuk
    $cek = mysqli_prepare($conn,
        "SELECT jam_masuk, jam_pulang 
         FROM absensi 
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($cek, "i", $absensi_id);
    mysqli_stmt_execute($cek);

    $hasil = mysqli_stmt_get_result($cek);
    $data = mysqli_fetch_assoc($hasil);

    if (!$data) {

        $pesan = "Data absensi tidak ditemukan.";
        $tipe_pesan = "error";

    } elseif ($data['jam_masuk'] == null) {

        $pesan = "Karyawan belum melakukan absen masuk.";
        $tipe_pesan = "error";

    } elseif ($data['jam_pulang'] != null) {

        $pesan = "Karyawan sudah melakukan absen pulang.";
        $tipe_pesan = "error";

    } else {

        $update = mysqli_prepare($conn,
            "UPDATE absensi 
             SET jam_pulang = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $update,
            "si",
            $jam_pulang,
            $absensi_id
        );

        if (mysqli_stmt_execute($update)) {
            $pesan = "Absensi pulang berhasil dicatat.";
            $tipe_pesan = "success";
        } else {
            $pesan = "Gagal mencatat absensi pulang.";
            $tipe_pesan = "error";
        }
    }
}

// Ambil semua karyawan aktif
$karyawan = mysqli_query(
    $conn,
    "SELECT * FROM karyawan 
     WHERE status = 'Aktif'
     ORDER BY nama ASC"
);

// Ambil absensi hari ini
$tanggal_hari_ini = date("Y-m-d");

$query_absensi = mysqli_query(
    $conn,
    "SELECT 
        absensi.id,
        absensi.tanggal,
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

    <title>Absensi Karyawan - DTY</title>

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

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h2 {
            margin-bottom: 20px;
            color: #123b5d;
        }

        .form-absen {
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

        select {
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

        .btn-masuk {
            background: #198754;
        }

        .btn-masuk:hover {
            background: #157347;
        }

        .btn-pulang {
            background: #dc3545;
        }

        .btn-pulang:hover {
            background: #bb2d3b;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .error {
            background: #f8d7da;
            color: #842029;
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

        .btn-small {
            padding: 7px 12px;
            font-size: 12px;
        }

        .sudah-pulang {
            color: #198754;
            font-weight: bold;
            font-size: 13px;
        }

        @media (max-width: 768px) {

            .sidebar {
                width: 200px;
            }

            .content {
                margin-left: 200px;
            }

            .form-absen {
                flex-direction: column;
                align-items: stretch;
            }

        }

    </style>

    <link rel="stylesheet" href="assets/css/industrial-theme.css">

</head>

<body>

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

        <a href="absensi.php" class="active">
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


<div class="content">

    <div class="header">

        <h1>Absensi Karyawan</h1>

        <p>
            Pencatatan kehadiran karyawan DTY
        </p>

    </div>


    <?php if ($pesan != ""): ?>

        <div class="alert <?= $tipe_pesan ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>

    <?php endif; ?>


    <!-- FORM ABSEN MASUK -->

    <div class="card">

        <h2>🕐 Absen Masuk</h2>

        <form method="POST">

            <div class="form-absen">

                <div class="form-group">

                    <label>
                        Pilih Karyawan
                    </label>

                    <select name="karyawan_id" required>

                        <option value="">
                            -- Pilih Karyawan --
                        </option>

                        <?php while ($row = mysqli_fetch_assoc($karyawan)): ?>

                            <option value="<?= $row['id'] ?>">

                                <?= htmlspecialchars($row['nik']) ?>
                                -
                                <?= htmlspecialchars($row['nama']) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <button
                    type="submit"
                    name="absen_masuk"
                    class="btn-masuk">

                    ✓ Absen Masuk

                </button>

            </div>

        </form>

    </div>


    <!-- DATA ABSENSI HARI INI -->

    <div class="card">

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
                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    <?php

                    $no = 1;

                    if (mysqli_num_rows($query_absensi) > 0):

                        while ($row = mysqli_fetch_assoc($query_absensi)):

                    ?>

                    <tr>

                        <td><?= $no++ ?></td>

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

                        <td>

                            <?php if ($row['jam_pulang'] == null): ?>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="absensi_id"
                                        value="<?= $row['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="absen_pulang"
                                        class="btn-pulang btn-small">

                                        Absen Pulang

                                    </button>

                                </form>

                            <?php else: ?>

                                <span class="sudah-pulang">
                                    ✓ Selesai
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                    <?php

                        endwhile;

                    else:

                    ?>

                    <tr>

                        <td
                            colspan="8"
                            style="text-align:center; padding:30px;">

                            Belum ada data absensi hari ini.

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