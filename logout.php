<?php
require 'koneksi.php';
session_destroy();
header('Location: index.php');
exit;
