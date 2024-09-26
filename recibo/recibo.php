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

<?php
// Verifica se o formulário foi submetido e se o campo 'titulo' está presente
if (isset($_POST["titulo"])) {
    $titulo = $_POST["titulo"]; // Armazena o valor do título

    // Verifica se o campo 'chave' foi submetido
    if (isset($_POST["chave"])) {
        $chave = $_POST["chave"]; // Armazena a chave de segurança fornecida
    }

    // Validação simples da chave de segurança
    if ($chave == 'chave-seguranca') { // <---- COLOQUE AQUI SUA CHAVE DE SEGURANÇA
	
        // Configurações de conexão com o banco de dados
        $host = "localhost";
        $usuario = "root";
        $senha = "vertrigo";
        $db = "mkradius";

        // Conexão com o banco de dados
        $mysqli = new mysqli($host, $usuario, $senha, $db);
        
        // Verifica se houve erro ao conectar no BD
        if ($mysqli->connect_errno) {
            echo "Falha na conexão: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }

        // Conexão usando o método tradicional do MySQL
        $con = mysqli_connect($host, $usuario, $senha);
        mysqli_select_db($con, $db); // Seleciona o banco de dados específico

        // Consulta SQL para buscar as informações do pagamento do título/boleto
        $boleto = "SELECT datavenc, datapag, valor, valorpag, coletor, formapag, login 
                   FROM sis_lanc 
                   WHERE id = $titulo"; // Faz a consulta com base no título fornecido

        // Consulta e obtém resultado
        $res = mysqli_query($con, $boleto);

        // Extrai dados da fatura e pagamento
        while ($vreg = mysqli_fetch_row($res)) {
            $datavenc = date('d/m/Y', strtotime($vreg[0])); // Converte e formata a data de vencimento
            $datapag = date('d/m/Y', strtotime($vreg[1]));  // Converte e formata a data de pagamento
            $valor = $vreg[2];   // Armazena o valor da fatura
            $valorpag = $vreg[3]; // Armazena o valor pago
            $coletor = $vreg[4];  // Armazena o coletor que recebeu o pagamento
            $formapag = $vreg[5]; // Armazena a forma de pagamento
            $login = $vreg[6];    // Armazena o login do cliente
        }

        // Nova consulta SQL para buscar celular do cliente pelo login
        $cliente = "SELECT celular FROM sis_cliente WHERE login = '$login'";
        $res = mysqli_query($con, $cliente);

        // Extrai o celular do cliente
        while ($vreg = mysqli_fetch_row($res)) {
            $celular = $vreg[0]; // Armazena celular do cliente
        }

        // Definição da nova URL da API Evolution v2
        $apiUrl = 'http://{{baseURL}}/message/sendText/{{instance}}'; // URL da API de envio de mensagens


        // Prepara o payload para Evolution API, contendo telefone e a mensagem
        $data = array(
            "number" => "$celular", // Número do celular no formato internacional
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

        // Converte o array para o formato JSON
        $jsonData = json_encode($data);

        // Inicializa uma nova sessão cURL para enviar a requisição via API
        $ch = curl_init($apiUrl);

        // Configurações da requisição cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Define que a resposta será uma string
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'apikey: digite-seu-token' // Inclui o token de autenticação da API (modificado para Evolution API v2)
        ));

        // Configura a requisição para ser um POST e insere o payload JSON
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Executa a requisição cURL e captura a resposta da API
        $response = curl_exec($ch);

        // Verifica se houve algum erro durante a execução do cURL
        if (curl_errno($ch)) {
            echo 'Erro ao chamar a API: ' . curl_error($ch); // Exibe o erro, se houver
        } else {
            echo 'Mensagem enviada com sucesso!'; // Exibe uma mensagem de sucesso se a API foi chamada corretamente
        }

        // Fecha a sessão cURL
        curl_close($ch);
    }
}
?>