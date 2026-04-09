<?php

// 1. CONEXÃO (Dados genéricos para o GitHub)
$servername = "seu_servidor_aqui";
$username = "seu_usuario_aqui";
$password = "sua_senha_aqui";
$dbname = "seu_banco_de_dados";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Lógica para obter a lista de clientes para o dropdown
$clientes_list = $conn->query("SELECT id_cliente, nome FROM cliente ORDER BY nome");
$resultados = "";

// 2. LÓGICA DE PESQUISA (PROTEGIDA)
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

$params = [];
$types = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pesquisar_vendas'])) {
    if (!empty($_POST['filtro_cliente'])) {
        $sql .= " AND pedido.id_cliente = ?";
        $params[] = $_POST['filtro_cliente'];
        $types .= "i";
    }

    if (!empty($_POST['filtro_pagamento'])) {
        $sql .= " AND venda.forma_pagamento = ?";
        $params[] = $_POST['filtro_pagamento'];
        $types .= "s";
    }

    if (!empty($_POST['filtro_data'])) {
        $sql .= " AND venda.data_venda LIKE ?";
        $params[] = $_POST['filtro_data'] . "%";
        $types .= "s";
    }

    $sql .= " ORDER BY venda.data_venda DESC";

    // --- USO DE PREPARED STATEMENTS (O diferencial do seu portfólio) ---
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $resultados .= "<table>";
        $resultados .= "<tr><th>ID Venda</th><th>Data</th><th>Cliente</th><th>Forma de Pagamento</th><th>Total</th><th>ID Pedido</th></tr>";
        while($row = $result->fetch_assoc()) {
            $resultados .= "<tr>";
            $resultados .= "<td>" . $row['id_venda'] . "</td>";
            $resultados .= "<td>" . date('d/m/Y H:i', strtotime($row['data_venda'])) . "</td>";
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
    $stmt->close();
}

$conn->close();
?>
