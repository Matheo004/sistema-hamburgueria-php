<?php
// Configurações do banco de dados
$servername = "localhost";
$username = "Matheo_Serrone";
$password = "Ribeiro@04";
$dbname = "DB_TI63_Matheo";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Consulta para buscar todos os produtos
$sql_produtos = "SELECT id_produto, nome_produto, descricao_produto, valor_produto FROM produto";
$result = $conn->query($sql_produtos);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - Serrone Burger</title>
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
        .product-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
        .product-card { background-color: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .product-card:hover { transform: translateY(-5px); }
        .product-image { height: 200px; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-info { padding: 15px; text-align: left; }
        .product-info h3 { font-size: 1.5em; margin-bottom: 5px; color: #d9534f; }
        .product-info p { font-size: 1em; color: #5a3c2c; line-height: 1.4; }
        .product-price { font-weight: bold; color: #f0ad4e; font-size: 1.2em; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cardápio</h1>
        
        <div class="button-container">
            <a href="index.html" class="button-link"><i class="fas fa-home"></i> Início</a>
            <a href="cardapio.php" class="button-link"><i class="fas fa-hamburger"></i> Cardápio</a>
            <a href="promocoes.php" class="button-link"><i class="fas fa-bullhorn"></i> Promoções</a>
            <a href="contato.html" class="button-link"><i class="fas fa-envelope"></i> Contato</a>
            <a href="cadastro_cliente.php" class="button-link"><i class="fas fa-users"></i> Gestão de Clientes</a>
            <a href="cadastro_produto.php" class="button-link"><i class="fas fa-box"></i> Gestão de Produtos</a>
            <a href="cadastro_pedido.php" class="button-link"><i class="fas fa-clipboard-list"></i> Gestão de Pedidos</a>
            <a href="venda.php" class="button-link"><i class="fas fa-cash-register"></i> Gestão de Vendas</a>
            <a href="relatorios.php" class="button-link"><i class="fas fa-chart-bar"></i> Relatórios</a>
        </div>
        
        <div class="product-list">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Lógica para encontrar o caminho da imagem
                    $id_produto = $row['id_produto'];
                    $image_path = "imagens/produto_" . $id_produto;
                    $image_src = "";

                    // Verifica se o arquivo .jpg existe
                    if (file_exists($image_path . '.jpg')) {
                        $image_src = $image_path . '.jpg';
                    // Se não existir, verifica se o arquivo .png existe
                    } elseif (file_exists($image_path . '.png')) {
                        $image_src = $image_path . '.png';
                    }

                    // Se uma imagem foi encontrada, exibe o card do produto
                    if ($image_src) {
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($row['nome_produto']); ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($row['nome_produto']); ?></h3>
                            <p><?php echo htmlspecialchars($row['descricao_produto']); ?></p>
                            <p class="product-price">R$ <?php echo number_format($row['valor_produto'], 2, ',', '.'); ?></p>
                        </div>
                    </div>
                    <?php
                    }
                }
            } else {
                echo "<p>Nenhum produto cadastrado no momento.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>