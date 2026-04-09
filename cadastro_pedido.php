<?php
// Configurações do banco de dados (Ocultas para segurança)
$servername = "seu_servidor_aqui";
$username = "seu_usuario_aqui";
$password = "sua_senha_aqui";
$dbname = "seu_banco_de_dados";

$conexao = new mysqli($servername, $username, $password, $dbname);
$conexao->set_charset("utf8");

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmar'])) {
    $id_cliente = $_POST['id_cliente'];
    $forma_pagamento = $_POST['forma_pagamento'];
    $produtos_selecionados = $_POST['produtos'];

    // INÍCIO DA TRANSAÇÃO: Segurança para garantir que o pedido completo seja gravado
    $conexao->begin_transaction();

    try {
        // 1. Inserir Pedido
        $stmt_ped = $conexao->prepare("INSERT INTO pedido (id_cliente, status_pedido) VALUES (?, 'finalizado')");
        $stmt_ped->bind_param("i", $id_cliente);
        $stmt_ped->execute();
        $id_pedido = $conexao->insert_id;
        $stmt_ped->close();

        $valor_total = 0;

        // 2. Inserir Itens do Pedido
        foreach ($produtos_selecionados as $id_produto) {
            $campo_qtd = "quantidade_" . $id_produto;
            $quantidade = isset($_POST[$campo_qtd]) ? (int)$_POST[$campo_qtd] : 0;

            if ($quantidade > 0) {
                // Buscar valor unitário com segurança
                $stmt_val = $conexao->prepare("SELECT valor_produto FROM produto WHERE id_produto = ?");
                $stmt_val->bind_param("i", $id_produto);
                $stmt_val->execute();
                $res_val = $stmt_val->get_result();
                $produto_info = $res_val->fetch_assoc();
                
                if ($produto_info) {
                    $valor_unitario = $produto_info['valor_produto'];
                    $subtotal = $valor_unitario * $quantidade;
                    $valor_total += $subtotal;

                    // Inserir item_pedido
                    $stmt_item = $conexao->prepare("INSERT INTO item_pedido (id_pedido, id_produto, quantidade, subtotal) VALUES (?, ?, ?, ?)");
                    $stmt_item->bind_param("iiid", $id_pedido, $id_produto, $quantidade, $subtotal);
                    $stmt_item->execute();
                    $stmt_item->close();
                }
                $stmt_val->close();
            }
        }
        
        // 3. Inserir Venda
        $stmt_venda = $conexao->prepare("INSERT INTO venda (id_pedido, data_venda, forma_pagamento, total_venda) VALUES (?, NOW(), ?, ?)");
        $stmt_venda->bind_param("isd", $id_pedido, $forma_pagamento, $valor_total);
        $stmt_venda->execute();
        $stmt_venda->close();

        // Se chegou aqui sem erros, confirma tudo no banco
        $conexao->commit();
        $mensagem = "<p class='success'>✅ Pedido registrado com sucesso! Total: R$ " . number_format($valor_total, 2, ',', '.') . "</p>";

    } catch (Exception $e) {
        // Se algo deu errado, desfaz todas as inserções para não sujar o banco
        $conexao->rollback();
        $mensagem = "<p class='error'>❌ Erro ao registrar pedido: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Pedido - Serrone Burger</title>
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
        .form-group select, .form-group input[type="text"], .form-group input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .form-group input[type="submit"] { background-color: #d9534f; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s; }
        .form-group input[type="submit"]:hover { background-color: #c9302c; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f0ad4e; color: #fff; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .success { color: green; font-weight: bold; text-align: center; }
        .error { color: red; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastro de Pedido</h1>
        
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

        <?php echo $mensagem; ?>

        <div class="form-section">
            <h2>Formulário de Pedido</h2>
            <form method="post">
                <div class="form-group">
                    <label for="id_cliente">Cliente:</label>
                    <select id="id_cliente" name="id_cliente" required>
                        <option value="">Selecione um cliente</option>
                        <?php
                        $consulta_clientes = $conexao->query("SELECT id_cliente, nome FROM cliente ORDER BY nome");
                        while ($dados_cliente = $consulta_clientes->fetch_assoc()) {
                            echo "<option value='{$dados_cliente['id_cliente']}'>" . htmlspecialchars($dados_cliente['nome']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="forma_pagamento">Forma de Pagamento:</label>
                    <select id="forma_pagamento" name="forma_pagamento" required>
                        <option value="">Selecione a forma de pagamento</option>
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Cartão">Cartão</option>
                        <option value="PIX">PIX</option>
                    </select>
                </div>

                <h3>Escolha os Produtos:</h3>
                <table>
                    <tr><th>Produto</th><th>Valor</th><th>Quantidade</th></tr>
                    
                    <?php
                    $consulta_produtos = $conexao->query("SELECT id_produto, nome_produto, valor_produto FROM produto");
                    while ($dados_produto = $consulta_produtos->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($dados_produto['nome_produto']) . "</td>";
                        echo "<td>R$ " . number_format($dados_produto['valor_produto'], 2, ',', '.') . "</td>";
                        echo "<td>";
                        echo "<input type='number' name='quantidade_" . $dados_produto['id_produto'] . "' min='0' value='0' style='width: 80px;'>";
                        echo "<input type='hidden' name='produtos[]' value='" . $dados_produto['id_produto'] . "'>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
                <div class="form-group" style="margin-top: 20px;">
                    <input type="submit" name="confirmar" value="Registrar Pedido">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php $conexao->close(); ?>
