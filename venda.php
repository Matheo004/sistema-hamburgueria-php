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

// Lógica para obter a lista de clientes para o dropdown
$clientes_list = $conn->query("SELECT id_cliente, nome FROM cliente ORDER BY nome");
$resultados = "";

// Lógica para pesquisa
$sql = "
    SELECT
        venda.id_venda,
        venda.data_venda,
        cliente.nome AS cliente,
        venda.forma_pagamento,
        venda.total_venda,
        venda.id_pedido
    FROM venda
    INNER JOIN pedido ON venda.id_pedido = pedido.id_pedido
    INNER JOIN cliente ON pedido.id_cliente = cliente.id_cliente
    WHERE 1 = 1
";

// Adiciona filtros dinamicamente se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pesquisar_vendas'])) {
    if (!empty($_POST['filtro_cliente'])) {
        $filtro_cliente_id = $conn->real_escape_string($_POST['filtro_cliente']);
        $sql .= " AND pedido.id_cliente = '$filtro_cliente_id'";
    }

    if (!empty($_POST['filtro_pagamento'])) {
        $filtro_pagamento = $conn->real_escape_string($_POST['filtro_pagamento']);
        $sql .= " AND venda.forma_pagamento = '$filtro_pagamento'";
    }

    if (!empty($_POST['filtro_data'])) {
        $filtro_data = $conn->real_escape_string($_POST['filtro_data']);
        $sql .= " AND venda.data_venda LIKE '$filtro_data%'";
    }

    $sql .= " ORDER BY venda.data_venda DESC";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $resultados .= "<table>";
        $resultados .= "<tr><th>ID Venda</th><th>Data</th><th>Cliente</th><th>Forma de Pagamento</th><th>Total</th><th>ID Pedido</th></tr>";
        while($row = $result->fetch_assoc()) {
            $resultados .= "<tr>";
            $resultados .= "<td>" . $row['id_venda'] . "</td>";
            $resultados .= "<td>" . $row['data_venda'] . "</td>";
            $resultados .= "<td>" . htmlspecialchars($row['cliente']) . "</td>";
            $resultados .= "<td>" . htmlspecialchars($row['forma_pagamento']) . "</td>";
            $resultados .= "<td>R$ " . number_format($row['total_venda'], 2, ',', '.') . "</td>";
            $resultados .= "<td>" . $row['id_pedido'] . "</td>";
            $resultados .= "</tr>";
        }
        $resultados .= "</table>";
    } else {
        $resultados = "<p class='info'>Nenhuma venda encontrada com os filtros selecionados.</p>";
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Vendas - Serrone Burger</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #fbeee0; color: #382b22; padding: 20px; }
        .container { max-width: 900px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        h1, h2, h3 { font-family: 'Montserrat', sans-serif; color: #d9534f; text-align: center; margin-bottom: 20px; }
        .button-container { text-align: center; margin-bottom: 30px; }
        .button-link { display: inline-block; margin: 10px; padding: 12px 25px; font-size: 16px; background-color: #f0ad4e; color: #fff; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; text-decoration: none; }
        .button-link:hover { background-color: #eea236; transform: translateY(-2px); box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15); }
        .button-link i { margin-right: 8px; }
        .form-section { background-color: #f8f8f8; padding: 25px; border-radius: 8px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #5a3c2c; }
        .form-group input[type="text"], .form-group input[type="date"], .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .form-group input[type="submit"] { background-color: #d9534f; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s; }
        .form-group input[type="submit"]:hover { background-color: #c9302c; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f0ad4e; color: #fff; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .info { text-align: center; font-style: italic; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestão de Vendas</h1>
        
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

        <div class="form-section">
            <h2>Pesquisar Vendas</h2>
            <form method="post">
                <div class="form-group">
                    <label for="filtro_cliente">Cliente:</label>
                    <select id="filtro_cliente" name="filtro_cliente">
                        <option value="">Todos os Clientes</option>
                        <?php
                            while($cliente = $clientes_list->fetch_assoc()) {
                                $selected = (isset($_POST['filtro_cliente']) && $_POST['filtro_cliente'] == $cliente['id_cliente']) ? 'selected' : '';
                                echo "<option value='{$cliente['id_cliente']}' {$selected}>" . htmlspecialchars($cliente['nome']) . "</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtro_pagamento">Forma de Pagamento:</label>
                    <select id="filtro_pagamento" name="filtro_pagamento">
                        <option value="">Todas as Formas</option>
                        <option value="Dinheiro" <?= (isset($_POST['filtro_pagamento']) && $_POST['filtro_pagamento'] == 'Dinheiro') ? 'selected' : '' ?>>Dinheiro</option>
                        <option value="Cartão" <?= (isset($_POST['filtro_pagamento']) && $_POST['filtro_pagamento'] == 'Cartão') ? 'selected' : '' ?>>Cartão</option>
                        <option value="PIX" <?= (isset($_POST['filtro_pagamento']) && $_POST['filtro_pagamento'] == 'PIX') ? 'selected' : '' ?>>PIX</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtro_data">Data da Venda:</label>
                    <input type="date" id="filtro_data" name="filtro_data" value="<?= htmlspecialchars($_POST['filtro_data'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <input type="submit" name="pesquisar_vendas" value="Pesquisar Vendas">
                </div>
            </form>
            <div id="resultados">
                <?php echo $resultados; ?>
            </div>
        </div>
    </div>
</body>
</html>