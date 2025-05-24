<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . " - JundBio" : "JundBio - Serra do Japi" ?></title>
    <link rel="stylesheet" href="<?= isset($path) ? $path : '' ?>css/main.css">
    <?php
    if(array_search('admin', $css) !== false):
        echo "<link rel='stylesheet' href='" . (isset($path) ? $path : '') . "css/admin.css'>";
        $css = array_diff($css, ['admin']);
    endif;
    if(isset($css) && !empty($css)):
        foreach($css as $css_file):
            echo "<link rel='stylesheet' href='" . (isset($path) ? $path : '') . "css/pages/$css_file.css'>";
        endforeach;
    endif;
    ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="<?= isset($path) ? $path : '' ?>assets/logo.png" type="image/x-icon">
</head>
