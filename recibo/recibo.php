<!-- Estilos CSS aplicados à página -->
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
    <!-- Campo oculto para ações adicionais, mas não usado aqui -->
    <input type="hidden" name="acao" value="nada">

    <!-- Campo para inserção do número do título ou boleto -->
    <label for="titulo">Digite o número do título ou boleto:</label>
    <input type="text" name="titulo" id="titulo" size="16" maxlength="16" required>

    <!-- Campo para inserção da chave de segurança -->
    <label for="chave">Digite a chave de segurança:</label>
    <input type="text" name="chave" id="chave" size="16" maxlength="16" required>

    <!-- Botão para enviar os dados -->
    <button type="submit">Enviar comprovante</button>
</form>

<?php
// Verifica se o formulário foi submetido e se o campo 'titulo' está presente
if (isset($_POST["titulo"])) {
    $titulo = $_POST["titulo"]; // Armazena o valor do título fornecido pelo formulário

    // Verifica se o campo 'chave' foi submetido
    if (isset($_POST["chave"])) {
        $chave = $_POST["chave"];
    }

    // Validação simples da chave de segurança
    if ($chave == 'chave-seguranca') { // <---- COLOQUE AQUI SUA CHAVE DE SEGURANÇA
        // Configurações de conexão com o banco de dados (credenciais hardcoded - melhorar segurança)
        $host = "localhost";
        $usuario = "root";
        $senha = "vertrigo";
        $db = "mkradius";

        // Estabelece conexão com o banco de dados usando MySQLi
        $mysqli = new mysqli($host, $usuario, $senha, $db);
        
        // Verifica se houve algum erro ao conectar ao banco de dados
        if ($mysqli->connect_errno) {
            echo "Falha na conexão: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }

        // Conexão tradicional com o MySQL
        $con = mysqli_connect($host, $usuario, $senha);
        mysqli_select_db($con, $db);

        // Consulta SQL para buscar as informações do pagamento do título/boleto
        $boleto = "SELECT datavenc, datapag, valor, valorpag, coletor, formapag, login 
                   FROM sis_lanc 
                   WHERE id = $titulo";

        // Executa a consulta e obtém o resultado
        $res = mysqli_query($con, $boleto);

        // Extrai os dados da fatura e pagamento
        while ($vreg = mysqli_fetch_row($res)) {
            $datavenc = date('d/m/Y', strtotime($vreg[0])); // Formata a data de vencimento
            $datapag = date('d/m/Y', strtotime($vreg[1]));  // Formata a data de pagamento
            $valor = $vreg[2];   // Valor da fatura
            $valorpag = $vreg[3]; // Valor pago
            $coletor = $vreg[4];  // Coletor do pagamento
            $formapag = $vreg[5]; // Forma de pagamento
            $login = $vreg[6];    // Login do cliente
        }

        // Segunda consulta SQL para buscar o número de celular do cliente associado ao login
        $cliente = "SELECT celular FROM sis_cliente WHERE login = '$login'";
        $res = mysqli_query($con, $cliente);

        // Extrai o número de celular do cliente
        while ($vreg = mysqli_fetch_row($res)) {
            $celular = $vreg[0];
        }

        // Definição da nova URL da API Evolution v2
        $apiUrl = 'http://{{baseURL}}/message/sendText/{{instance}}'; # DIGITE A URL AQUI


        // Prepara o payload de dados a ser enviado pela API com número e texto
        $data = array(
            "number" => "$celular", // Número de telefone do cliente com código internacional
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

        // Inicializa uma nova sessão cURL
        $ch = curl_init($apiUrl);

        // Configurações cURL: define o retorno da resposta como string e configura os headers da requisição
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'apikey: digite-seu-token' // DIGITE SEU TOKEN AQUI (alterado para o Evolution API v2)
        ));

        // Configura a requisição como POST e define o payload JSON
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Executa a requisição cURL e captura a resposta
        $response = curl_exec($ch);

        // Verifica se houve erros durante a execução do cURL
        if (curl_errno($ch)) {
            echo 'Erro ao chamar a API: ' . curl_error($ch);
        } else {
            echo 'Mensagem enviada com sucesso!';
        }

        // Fecha a sessão cURL
        curl_close($ch);
    }
}
?>