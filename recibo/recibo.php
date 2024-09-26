<?php
// ------------------------------------------------------------------------------------------------
// Configurações principais
// ------------------------------------------------------------------------------------------------
$apiUrl = 'https://{{baseURL}}/message/sendText/{{instance}}'; // URL da API Evolution v2
$apiToken = 'seu-token-aqui'; // Token de autenticação da API

$securityKey = 'chave-seguranca'; // Chave de segurança para validação

$host = "localhost"; // Host do banco de dados MySQL
$usuario = "root"; // Usuário do banco de dados MySQL
$senha = "vertrigo"; // Senha do banco de dados MySQL
$db = "mkradius"; // Nome do banco de dados MySQL

// ------------------------------------------------------------------------------------------------
// Início do processamento de envio de comprovante
// ------------------------------------------------------------------------------------------------

// Verifica se o formulário foi submetido e se o campo 'titulo' está presente
if (isset($_POST["titulo"])) {
    $titulo = $_POST["titulo"]; // Armazena o valor do título enviado pelo formulário

    // Verifica se o campo 'chave' foi submetido
    if (isset($_POST["chave"])) {
        $chave = $_POST["chave"]; // Armazena a chave de segurança fornecida
    }

    // Validação simples da chave de segurança
    if ($chave == $securityKey) { // Compara a chave fornecida com a chave definida no início do arquivo
	
        // Conexão com o banco de dados MySQL usando o MySQLi
        $mysqli = new mysqli($host, $usuario, $senha, $db);
        
        // Verifica se houve erro ao conectar ao banco de dados
        if ($mysqli->connect_errno) {
            echo "Falha na conexão: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }

        // Conexão usando o método tradicional do MySQL (procedural)
        $con = mysqli_connect($host, $usuario, $senha);
        mysqli_select_db($con, $db); // Seleciona o banco de dados

        // Consulta SQL para buscar as informações do pagamento do título/boleto
        $boleto = "SELECT datavenc, datapag, valor, valorpag, coletor, formapag, login 
                   FROM sis_lanc 
                   WHERE id = $titulo"; // Faz a consulta SQL com base no número do título fornecido

        // Executa a consulta SQL e armazena o resultado
        $res = mysqli_query($con, $boleto);

        // Extrai os dados da fatura e pagamento
        while ($vreg = mysqli_fetch_row($res)) {
            $datavenc = date('d/m/Y', strtotime($vreg[0])); // Formata a data de vencimento
            $datapag = date('d/m/Y', strtotime($vreg[1]));  // Formata a data de pagamento
            $valor = $vreg[2];   // Valor da fatura
            $valorpag = $vreg[3]; // Valor pago
            $coletor = $vreg[4];  // Coletor do pagamento
            $formapag = $vreg[5]; // Forma de pagamento
            $login = $vreg[6];    // Login do cliente associado ao pagamento
        }

        // Segunda consulta SQL para buscar o número de celular do cliente com base no login
        $cliente = "SELECT celular FROM sis_cliente WHERE login = '$login'";
        $res = mysqli_query($con, $cliente);

        // Extrai o número de celular do cliente
        while ($vreg = mysqli_fetch_row($res)) {
            $celular = $vreg[0]; // Armazena o número de celular do cliente
        }

        // Prepara os dados para envio via Evolution API v2
        $data = array(
            "number" => "$celular", // Número de celular no formato internacional
            "text" => "
*Mensagem Automática de Recebimento de Pagamento*

*Pagamento recebido em*: $datapag
*Fatura com vencimento em*: $datavenc
*Valor da fatura*: R$ $valor
*Valor do pagamento*: R$ $valorpag
*Pagamento recebido por*: $coletor
*Forma de pagamento*: $formapag

Para segunda via e comprovantes dos pagamentos acesse:
https://BrLink.org/cliente (coloque o *CPF* do titular)
"
        );

        // Converte o array de dados para o formato JSON
        $jsonData = json_encode($data);

        // Inicializa a sessão cURL para envio da requisição
        $ch = curl_init($apiUrl);

        // Configurações da requisição cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Define que a resposta da requisição será retornada como string
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'apikey: ' . $apiToken // Insere o token da API no cabeçalho da requisição
        ));

        // Define o método da requisição como POST e insere os dados JSON
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Executa a requisição cURL e captura a resposta da API
        $response = curl_exec($ch);

        // Verifica se houve algum erro durante a execução da requisição
        if (curl_errno($ch)) {
            echo 'Erro ao chamar a API: ' . curl_error($ch); // Exibe a mensagem de erro, se houver
        } else {
            echo 'Mensagem enviada com sucesso!'; // Exibe uma mensagem de sucesso
        }

        // Fecha a sessão cURL
        curl_close($ch);
    } else {
        // Exibe uma mensagem de erro caso a chave de segurança fornecida seja inválida
        echo 'Chave de segurança inválida.';
    }
}
?>

<!-- Estilos CSS -->
<style>
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
    }

    form {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    label {
        margin-bottom: 10px;
    }

    input {
        margin-bottom: 20px;
    }
</style>

<!-- Formulário para inserir o número do título/boleto e a chave de segurança -->
<form name="login" method="post">
    <!-- Campo oculto para ações adicionais, mas não utilizado -->
    <input type="hidden" name="acao" value="nada">

    <!-- Campo para inserir o número do título/boleto -->
    <label for="titulo">Digite o número do título ou boleto:</label>
    <input type="text" name="titulo" id="titulo" size="16" maxlength="16" required>

    <!-- Campo para inserir a chave de segurança -->
    <label for="chave">Digite a chave de segurança:</label>
    <input type="text" name="chave" id="chave" size="16" maxlength="16" required>

    <!-- Botão para enviar os dados -->
    <button type="submit">Enviar comprovante</button>
</form>