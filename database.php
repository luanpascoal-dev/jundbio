<?php

$host = 'localhost';
$usuario = 'root';
$senha = '';
$database = 'jundbio';

$conn = new mysqli($host, $usuario, $senha);

if ($conn->connect_error) {
    die("Falha na conexão com o servidor MySQL: " . $conn->connect_error);
}

$resultado = $conn->query("SHOW DATABASES LIKE '$database'");

if ($resultado->num_rows == 0) {
    run_sql($conn);
} else {
    $conn = new mysqli($host, $usuario, $senha, $database);
}
    
function run_sql($conn) {
    // Lê o conteúdo do arquivo
    $sql_content = file_get_contents("Codigo.sql");

    if($sql_content == false) {
        $sql_content = file_get_contents("../Codigo.sql");
    }

    // Executa as consultas SQL
    if (mysqli_multi_query($conn, $sql_content)) {
        // Se a execução for bem-sucedida
        do {
            if ($result = mysqli_store_result($conn)) {
                // Processa o resultado
                while($row = mysqli_fetch_assoc($result)) {
                    // Faça algo com os resultados
                    $row = $row;
                }
                mysqli_free_result($result);
            }
        } while (mysqli_next_result($conn));
    } else {
        // Se ocorrer um erro
        $_SESSION['error'] = "Erro: " . mysqli_error($conn);
    }

    // Fecha a conexão
    mysqli_close($conn);
    
    session_destroy();
    header("Location: ./");

}

