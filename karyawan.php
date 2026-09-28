<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Pencarian
$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search != "") {

    $search_safe = mysqli_real_escape_string($conn, $search);

    $query = "
        SELECT *
        FROM karyawan
        WHERE nik LIKE '%$search_safe%'
        OR nama LIKE '%$search_safe%'
        OR departemen LIKE '%$search_safe%'
        OR jabatan LIKE '%$search_safe%'
        ORDER BY id DESC
    ";

} else {

    $query = "
        SELECT *
        FROM karyawan
        ORDER BY id DESC
    ";

}

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Karyawan - DTY Attendance</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f6f8;
    color: #333;
}

/* SIDEBAR */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: #17324d;
    color: white;

    padding: 25px 15px;
}

.brand {
    padding: 0 15px 30px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}

.brand h2 {
    font-size: 22px;
    margin-bottom: 5px;
}

.brand p {
    font-size: 12px;
    opacity: 0.7;
}

.menu {
    margin-top: 25px;
}

.menu a {
    display: block;

    padding: 13px 15px;

    margin-bottom: 7px;

    color: #dbe7ef;

    text-decoration: none;

    border-radius: 8px;
}

.menu a:hover,
.menu a.active {
    background: #285b7a;
    color: white;
}

.menu a span {
    margin-right: 10px;
}

.logout {
    position: absolute;
    bottom: 25px;
    left: 15px;
    right: 15px;
}

.logout a {
    display: block;

    padding: 12px;

    text-align: center;

    background: #b83b3b;
    color: white;

    border-radius: 8px;

    text-decoration: none;
}

/* MAIN */

.main {
    margin-left: 250px;
    padding: 30px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 25px;
}

.topbar h1 {
    color: #17324d;
    font-size: 28px;
}

.topbar p {
    color: #777;
    margin-top: 5px;
}

.admin {
    background: white;

    padding: 12px 18px;

    border-radius: 10px;

    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

/* CONTENT */

.content {
    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
}

.toolbar {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

    gap: 15px;
}

.search-form {
    display: flex;

    gap: 8px;
}

.search-form input {
    width: 280px;

    padding: 11px 13px;

    border: 1px solid #ddd;

    border-radius: 7px;

    outline: none;
}

.btn {
    display: inline-block;

    padding: 11px 16px;

    border-radius: 7px;

    text-decoration: none;

    border: none;

    cursor: pointer;

    font-size: 14px;
}

.btn-search {
    background: #e9f2f7;
    color: #17324d;
}

.btn-add {
    background: #17324d;
    color: white;
}

.btn-edit {
    background: #f1b84b;
    color: white;
}

.btn-delete {
    background: #c0392b;
    color: white;
}

table {
    width: 100%;

    border-collapse: collapse;
}

th {
    background: #f4f6f8;

    padding: 13px;

    text-align: left;

    font-size: 13px;

    color: #555;
}

td {
    padding: 13px;

    border-bottom: 1px solid #eee;

    font-size: 14px;
}

.status {
    padding: 6px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;
}

.aktif {
    background: #dff5e5;

    color: #238b45;
}

.nonaktif {
    background: #ffe5e5;

    color: #c0392b;
}

.action {
    display: flex;

    gap: 6px;
}

.empty {
    text-align: center;

    padding: 30px;

    color: #999;
}

@media(max-width: 900px) {

    .sidebar {
        position: relative;

        width: 100%;

        height: auto;
    }

    .logout {
        position: relative;

        bottom: auto;

        margin-top: 20px;
    }

    .main {
        margin-left: 0;
    }

    .toolbar {
        flex-direction: column;

        align-items: stretch;
    }

    .search-form input {
        width: 100%;
    }

}

</style>

<link rel="stylesheet" href="assets/css/industrial-theme.css">

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="brand">

        <h2>🧵 DTY Attendance</h2>

        <p>Textile Manufacturing System</p>

    </div>

    <div class="menu">

        <a href="dashboard.php">

            <span>📊</span>

            Dashboard

        </a>

        <a href="karyawan.php" class="active">

            <span>👥</span>

            Data Karyawan

        </a>

        <a href="absensi.php">

            <span>🕐</span>

            Absensi

        </a>

        <a href="riwayat.php">

            <span>📋</span>

            Riwayat Absensi

        </a>

    </div>

    <div class="logout">

        <a href="logout.php">

            🚪 Logout

        </a>

    </div>

</div>


<!-- MAIN -->

<div class="main">

    <div class="topbar">

        <div>

            <h1>Data Karyawan</h1>

            <p>
                Kelola data karyawan manufaktur tekstil DTY.
            </p>

        </div>

        <div class="admin">

            👤

            <strong>
                <?= htmlspecialchars($_SESSION['nama']); ?>
            </strong>

        </div>

    </div>


    <div class="content">

        <div class="toolbar">

            <form method="GET" class="search-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Cari NIK, nama, departemen..."
                    value="<?= htmlspecialchars($search); ?>"
                >

                <button
                    type="submit"
                    class="btn btn-search"
                >
                    🔎 Cari
                </button>

            </form>


            <a
                href="tambah_karyawan.php"
                class="btn btn-add"
            >
                ➕ Tambah Karyawan
            </a>

        </div>


        <table>

            <thead>

                <tr>

                    <th>No</th>

                    <th>NIK</th>

                    <th>Nama</th>

                    <th>Jenis Kelamin</th>

                    <th>Departemen</th>

                    <th>Jabatan</th>

                    <th>Status</th>

                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

            <?php

            $no = 1;

            if (mysqli_num_rows($result) > 0):

                while ($row = mysqli_fetch_assoc($result)):

            ?>

                <tr>

                    <td>
                        <?= $no++; ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['nik']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['nama']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['jenis_kelamin']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['departemen']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['jabatan']); ?>
                    </td>

                    <td>

                        <?php if ($row['status'] == 'Aktif'): ?>

                            <span class="status aktif">
                                Aktif
                            </span>

                        <?php else: ?>

                            <span class="status nonaktif">
                                Nonaktif
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <div class="action">

                            <a
                                href="edit_karyawan.php?id=<?= $row['id']; ?>"
                                class="btn btn-edit"
                            >
                                ✏️
                            </a>

                            <a
                                href="hapus_karyawan.php?id=<?= $row['id']; ?>"
                                class="btn btn-delete"
                                onclick="return confirm('Yakin ingin menghapus karyawan ini?');"
                            >
                                🗑️
                            </a>

                        </div>

                    </td>

                </tr>

            <?php

                endwhile;

            else:

            ?>

                <tr>

                    <td colspan="8" class="empty">

                        Data karyawan tidak ditemukan.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>