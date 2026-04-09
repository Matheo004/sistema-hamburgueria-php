<?php
// Configurações do banco de dados (Ocultas para segurança)
$servername = "seu_servidor_aqui";
$username = "seu_usuario_aqui";
$password = "sua_senha_aqui";
$dbname = "seu_banco_de_dados";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$mensagem = "";
$resultados = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CADASTRAR CLIENTE (Seguro contra SQL Injection)
    if (isset($_POST['cadastrar'])) {
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $endereco = $_POST['endereco'];
        $estado = $_POST['estado'];
        $telefone = $_POST['telefone'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("INSERT INTO cliente (nome, cpf, endereco, estado, telefone, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nome, $cpf, $endereco, $estado, $telefone, $email);
        
        if ($stmt->execute()) {
            $mensagem = "<p class='success'>✅ Cliente cadastrado com sucesso!</p>";
        } else {
            $mensagem = "<p class='error'>❌ Erro ao cadastrar: " . $conn->error . "</p>";
        }
        $stmt->close();
    }

    // PESQUISAR CLIENTE (Seguro contra SQL Injection)
    if (isset($_POST['pesquisar'])) {
        $buscar_nome = "%" . $_POST['buscar_nome'] . "%";
        
        $stmt_busca = $conn->prepare("SELECT id_cliente, nome, cpf, endereco, estado, telefone, email FROM cliente WHERE nome LIKE ? ORDER BY nome");
        $stmt_busca->bind_param("s", $buscar_nome);
        $stmt_busca->execute();
        $result_busca = $stmt_busca->get_result();
        
        if ($result_busca->num_rows > 0) {
            $resultados .= "<table>";
            $resultados .= "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Endereço</th><th>Estado</th><th>Telefone</th><th>E-mail</th></tr>";
            while($row = $result_busca->fetch_assoc()) {
                $resultados .= "<tr>";
                $resultados .= "<td>" . $row['id_cliente'] . "</td>";
                $resultados .= "<td>" . htmlspecialchars($row['nome']) . "</td>";
                $resultados .= "<td>" . htmlspecialchars($row['cpf']) . "</td>";
                $resultados .= "<td>" . htmlspecialchars($row['endereco']) . "</td>";
                $resultados .= "<td>" . htmlspecialchars($row['estado']) . "</td>";
                $resultados .= "<td>" . htmlspecialchars($row['telefone']) . "</td>";
                $resultados .= "<td>" . htmlspecialchars($row['email']) . "</td>";
                $resultados .= "</tr>";
            }
            $resultados .= "</table>";
        } else {
            $resultados = "<p class='info'>Nenhum cliente encontrado.</p>";
        }
        $stmt_busca->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Clientes - Serrone Burger</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #fbeee0; color: #382b22; padding: 20px; }
        .container { max-width: 900px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        h1, h2 { font-family: 'Montserrat', sans-serif; color: #d9534f; text-align: center; margin-bottom: 20px; }
        .button-container { text-align: center; margin-bottom: 30px; }
        .button-link { display: inline-block; margin: 10px; padding: 12px 25px; font-size: 16px; background-color: #f0ad4e; color: #fff; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; text-decoration: none; }
        .button-link:hover { background-color: #eea236; transform: translateY(-2px); box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15); }
        .button-link i { margin-right: 8px; }
        .form-section { background-color: #f8f8f8; padding: 25px; border-radius: 8px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #5a3c2c; }
        .form-group input[type="text"], .form-group input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .form-group input[type="submit"] { background-color: #d9534f; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s; }
        .form-group input[type="submit"]:hover { background-color: #c9302c; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f0ad4e; color: #fff; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .info { text-align: center; font-style: italic; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestão de Clientes</h1>
        
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
            <h2>Cadastrar Novo Cliente</h2>
            <form method="post">
                <div class="form-group"><label for="nome">Nome:</label><input type="text" id="nome" name="nome" required></div>
                <div class="form-group"><label for="cpf">CPF:</label><input type="text" id="cpf" name="cpf" required></div>
                <div class="form-group"><label for="endereco">Endereço:</label><input type="text" id="endereco" name="endereco" required></div>
                <div class="form-group"><label for="estado">Estado:</label><input type="text" id="estado" name="estado"></div>
                <div class="form-group"><label for="telefone">Telefone:</label><input type="text" id="telefone" name="telefone"></div>
                <div class="form-group"><label for="email">E-mail:</label><input type="email" id="email" name="email"></div>
                <div class="form-group"><input type="submit" name="cadastrar" value="Cadastrar Cliente"></div>
            </form>
        </div>

        <div class="form-section">
            <h2>Pesquisar Cliente</h2>
            <form method="post" action="#resultados">
                <div class="form-group"><label for="buscar_nome">Nome do Cliente:</label><input type="text" id="buscar_nome" name="buscar_nome" required></div>
                <div class="form-group"><input type="submit" name="pesquisar" value="Pesquisar Cliente"></div>
            </form>
            <div id="resultados">
                <?php echo $resultados; ?>
            </div>
        </div>
    </div>
</body>
</html>
