<?php
// 1. INCLUSÃO DA CONEXÃO CENTRALIZADA
// No GitHub, use dados genéricos. Localmente, você pode usar seu arquivo config.php ou conexao.php
$servername = "seu_servidor_aqui";
$username = "seu_usuario_aqui";
$password = "sua_senha_aqui";
$dbname = "seu_banco_de_dados";

$conn = new mysqli($servername, $username, $password, $dbname);

// Forçar UTF-8 para evitar erros de acentuação nos nomes dos produtos
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// 2. CONSULTA DE PROMOÇÕES
$sql_promocoes = "SELECT id_produto, nome_produto, descricao_produto, valor_produto FROM produto WHERE promocao_ativa = 1";
$result_promocoes = $conn->query($sql_promocoes);

// Não fechamos a conexão aqui para permitir o loop no HTML abaixo, 
// fecharemos ao final do arquivo.
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoções - Gestão de Hamburgueria</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #fbeee0; color: #382b22; padding: 20px; }
        .container { max-width: 900px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        h1 { font-family: 'Montserrat', sans-serif; color: #d9534f; text-align: center; margin-bottom: 20px; }
        .button-container { text-align: center; margin-bottom: 30px; }
        .button-link { display: inline-block; margin: 10px; padding: 12px 25px; font-size: 16px; background-color: #f0ad4e; color: #fff; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; text-decoration: none; }
        .button-link:hover { background-color: #eea236; transform: translateY(-2px); box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15); }
        .button-link i { margin-right: 8px; }
        .promo-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
        .promo-card { background-color: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .promo-card:hover { transform: translateY(-5px); }
        .promo-image { height: 200px;
