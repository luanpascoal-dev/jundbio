<?php

if(!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header("Location: /JundBio/login");
    exit();
}