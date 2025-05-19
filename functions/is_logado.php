<?php

if(!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header("Location: login");
    exit();
}