<?php
session_start();
include 'koneksi.php';

$nama     = $_POST['nama'];
$email    = $_POST['email'];
$telpon   = $_POST['telpon'];
$fakultas = $_POST['fakultas'];
$prodi    = $_POST['prodi'];
$nim      = $_SESSION['nim'];

$conn->query("
  UPDATE users SET
    nama='$nama',
    email='$email',
    telpon='$telpon',
    fakultas='$fakultas',
    prodi='$prodi'
  WHERE nim='$nim'
");

header("Location: profil.php");
exit;
