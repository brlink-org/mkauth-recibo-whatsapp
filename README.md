# README - Recibo WhatsApp

Addon para o Sistema Mk-Auth que envia automaticamente recibos de pagamento via WhatsApp aos clientes de forma rápida e eficiente.

## Estrutura de Arquivos

O Addon é composto pelos seguintes arquivos:

- `index.php`: Página principal que inclui as funcionalidades do addon.
- `recibo.php`: Contém o formulário para inserção dos dados do boleto e o código PHP para processamento e envio do recibo.
- `manifest.json`: Arquivo de manifesto do addon, contendo informações sobre o nome, versão e autores do addon.
- `addon.js`: Arquivo JavaScript que adiciona um link para o addon no menu de clientes do Mk-Auth.

## Instruções de Uso

1. Configure o ambiente:
   - Certifique-se de ter o Mk-Auth instalado e configurado em seu servidor.
   - Importe os arquivos do projeto para o diretório `/opt/mk-auth/admin/addons/` no Mk-Auth.

2. Personalize o código:
   - No arquivo `recibo.php` já encontra-se pré definido as configurações padrão para conexão com o banco de dados do Mk-Auth (host, usuário, senha e banco de dados).
   - Configure a chave de segurança.
   - Adapte a URL da EvolutionAPI para envio das mensagens via WhatsApp.
   - Substitua o token de API pelo token correto.

3. Acesse o sistema:
   - Após configurar o ambiente e personalizar o código, acesse o sistema Mk-Auth.
   - Na interface do sistema, localize o link para o Addon "Recibo WhatsApp" no menu de clientes.

4. Envio de Recibos:
   - Preencha o formulário com o número do título ou boleto e a chave de segurança.
   - Após enviar o formulário, o sistema irá processar os dados e enviar o recibo automaticamente para o cliente via WhatsApp.

## Observações

- Este projeto é uma extensão (Addon) para o Sistema Mk-Auth e requer conhecimento prévio sobre o funcionamento do sistema.
- Certifique-se de configurar corretamente as permissões de acesso e segurança para proteger as informações sensíveis, como chaves de segurança e tokens de API.

**Nota:** Este projeto foi desenvolvido como um exemplo de integração e pode exigir ajustes adicionais para atender às necessidades específicas do seu ambiente e sistema.
