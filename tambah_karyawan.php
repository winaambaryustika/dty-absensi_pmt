<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nik = trim($_POST['nik']);
    $nama = trim($_POST['nama']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $departemen = trim($_POST['departemen']);
    $jabatan = trim($_POST['jabatan']);
    $status = $_POST['status'];

    if (
        $nik == "" ||
        $nama == "" ||
        $jenis_kelamin == "" ||
        $departemen == "" ||
        $jabatan == ""
    ) {

        $error = "Semua data wajib diisi.";

    } else {

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM karyawan WHERE nik = ?"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $nik
        );

        mysqli_stmt_execute($check);

        $result_check = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result_check) > 0) {

            $error = "NIK sudah terdaftar.";

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO karyawan
                (nik, nama, jenis_kelamin, departemen, jabatan, status)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssss",
                $nik,
                $nama,
                $jenis_kelamin,
                $departemen,
                $jabatan,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: karyawan.php");
                exit;

            } else {

                $error = "Data gagal disimpan.";

            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Tambah Karyawan - DTY Attendance</title>

<style>

* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    margin: 0;
    background: #f4f6f8;
}

.container {
    width: 700px;
    max-width: 92%;

    margin: 50px auto;

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

h1 {
    color: #17324d;

    margin-bottom: 8px;
}

.subtitle {
    color: #777;

    margin-bottom: 25px;
}

.error {
    background: #ffe5e5;

    color: #c0392b;

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;

    margin-bottom: 7px;

    font-weight: bold;

    color: #444;
}

input,
select {
    width: 100%;

    padding: 12px;

    border: 1px solid #ddd;

    border-radius: 7px;

    outline: none;
}

input:focus,
select:focus {
    border-color: #285b7a;
}

.buttons {
    display: flex;

    gap: 10px;

    margin-top: 25px;
}

button,
a {
    padding: 12px 18px;

    border-radius: 7px;

    border: none;

    text-decoration: none;

    cursor: pointer;

    font-size: 14px;
}

button {
    background: #17324d;

    color: white;
}

.btn-back {
    background: #ddd;

    color: #333;
}

</style>

<link rel="stylesheet" href="assets/css/industrial-theme.css">

</head>

<body>

<div class="container">

    <h1>Tambah Karyawan</h1>

    <p class="subtitle">
        Tambahkan data karyawan DTY.
    </p>

    <?php if ($error != ""): ?>

        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="form-group">

            <label>NIK</label>

            <input
                type="text"
                name="nik"
                placeholder="Contoh: DTY009"
                required
            >

        </div>


        <div class="form-group">

            <label>Nama Lengkap</label>

            <input
                type="text"
                name="nama"
                placeholder="Masukkan nama lengkap"
                required
            >

        </div>


        <div class="form-group">

            <label>Jenis Kelamin</label>

            <select name="jenis_kelamin" required>

                <option value="">
                    -- Pilih Jenis Kelamin --
                </option>

                <option value="Laki-laki">
                    Laki-laki
                </option>

                <option value="Perempuan">
                    Perempuan
                </option>

            </select>

        </div>


        <div class="form-group">

            <label>Departemen</label>

            <select name="departemen" required>

                <option value="">
                    -- Pilih Departemen --
                </option>

                <option value="Produksi DTY">
                    Produksi DTY
                </option>

                <option value="Quality Control">
                    Quality Control
                </option>

                <option value="Maintenance">
                    Maintenance
                </option>

                <option value="Warehouse">
                    Warehouse
                </option>

                <option value="Administrasi">
                    Administrasi
                </option>

            </select>

        </div>


        <div class="form-group">

            <label>Jabatan</label>

            <input
                type="text"
                name="jabatan"
                placeholder="Contoh: Operator Produksi"
                required
            >

        </div>


        <div class="form-group">

            <label>Status</label>

            <select name="status">

                <option value="Aktif">
                    Aktif
                </option>

                <option value="Nonaktif">
                    Nonaktif
                </option>

            </select>

        </div>


        <div class="buttons">

            <button type="submit">
                💾 Simpan Karyawan
            </button>

            <a
                href="karyawan.php"
                class="btn-back"
            >
                Kembali
            </a>

        </div>

    </form>

</div>

</body>

</html>