=== Pix 4x sem juros - Pagaleve ===

Contributors: apiki, pagaleve, aguiart0
Tags: checkout, woocommerce, pagaleve, gateway, payments
Requires at least: 5.0
Requires PHP: 7.1
Tested up to: 6.4
Stable tag: 1.6.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A Pagaleve é uma fintech brasileira fundada em 2021 e que oferece aos varejistas a solução de Pix 4x sem juros.

== Description ==

A Pagaleve é uma fintech brasileira fundada em 2021 e que oferece aos varejistas a solução de Pix 4x sem juros. É uma forma de pagamento para dividir as compras em 4x sem juros pelo PIX. E melhor: não precisa de cartão de crédito. O cliente paga a primeira parcela no ato da compra e as três outras parcelas são pagas a cada 15 dias. É simples. É fácil. É leve.

= Requirements =

- PHP version 7.4 ou maior.
- WooCommerce version 7.0.x ou maior.
- Brazilian Market on WooCommerce.

== Installation ==

1. Faça upload deste plugin em seu WordPress, e ative-o;
2. Entre no menu lateral "WooCommerce > Configurações > Pagaleve";
3. Escolha o ambiente ( Homologação ou Produção ) e adicione usuário e senha.
4. No menu Pagamentos, entre em Pagaleve e Adicione Título, Descrição, Título do checkout, Status do pedido e habilite o metódo, depois salve alterações.

== Changelog ==

= 1.6.6 - 16/08/2024 =
- Considera o status de 'Aguardando' durante o processo de cancelamento de pedidos

= 1.6.5 - 15/08/2024 =
- Refatora o sistema de gerenciamento automático de status
- Adiciona a opção de prefixo de pedido às configurações

= 1.6.4 – 12/03/2024 =
- Definido constante com URLs distintas para os ambientes de sandbox e production
- Definição do carregamento do Script de acordo com o ambiente
- Inclusão da condicional do Script de Checkout para determinar o script correto para cada ambiente

= 1.6.3 – 12/03/2024 =
- Compatibilidade com o checkout de blocos do WooCommerce

= 1.6.2 – 14/11/2023 =
- Compatibilidade com a versão 6.4 do WordPress
- Compatibilidade com o WooCommerce HPOS
- Removendo enqueue de script Pagaleve em páginas onde não é utilizado

= 1.6.1 – 20/07/2023 =
- Abstraindo o processo de pagamento para uma classe Abstract_Payment
- Adicionado filtro pagaleve_redirect_success_order para redirecionamento após pagamento bem sucedido
- Restauração do carrinho após cancelamento do popup de pagamento

= 1.6.0 – 13/07/2023 =
- Refatorando o checkout transparente
- Movendo a aparição do popup para a página de agradecimento
- Abstraindo função de renderização de checkout para uma classe estática. Podendo assim o popup ser utilizado em outras páginas além da página de agradecimento

= 1.5.9 – 19/06/2023 =
- Refatorando a criação de orders para checkout transparente para que taxas, fees e coupons sejam aplicados da maneira correta

= 1.5.8 – 06/06/2023 =
- Correção de utilização de metodos de entrega para checkout transparente dentro do WooCommerce
- Criação de novo webhook para alteração de status automática
- Correção de duplicidade de requisição para a API de payments
- Correção alteração de status ao acessar a thankyou page mais de uma vez

= 1.5.6 – 11/05/2023 =
- Implementando Onboarding Automático

= 1.5.5 – 24/04/2023 =
- Melhoria na aplicação de cupons para checkout transparente dentro do WooCommerce

= 1.5.4 – 17/04/2023 =
- Alteração de status após confirmação de pagamento

= 1.5.3 – 03/04/2023 =
- Correção de utilização de cupons para checkout transparente dentro do WooCommerce

= 1.5.2 – 31/03/2023 =
- Correção de constante de versão.
- Correção de utilização de cupons para checkout transparente

= 1.5.1 – 29/03/2023 =
- Adaptando o checkout transparent para o novo formato da PagaLeve.

= 1.5.0 – 28/02/2023 =
- Adicionando opção de checkout transparente.
- Resolvendo o problema de Javascript do widget.
- Resolvendo o problema do status de pedido aguardando.
- Corrigindo layout para pagamentos via desktop.

= 1.4.0 – 14/12/2022 =
- Resolvendo o problema de alteração de status depois de executar a cron.
- Removendo status on-hold e deixando somente o status pending.

= 1.3.9.1 – 13/12/2022 =
- Resolvendo o problema de alteração de status quando realiza um pagamento no pix à vista.

= 1.3.9 – 09/12/2022 =
- Resolvendo o problema de alteração de status quando realiza um pagamento.

= 1.3.8 – 02/12/2022 =
- Resolvendo o problema de alteração de status.

= 1.3.7 – 30/11/2022 =
- Resolvendo o problema de status da cron de 5 e 10 minutos.

= 1.3.6 – 22/11/2022 =
- Alterando o tempo da cron para 5 e 10 minutos.
- Deixando a cron alterar o status dos pedidos.
- Adicionando o status de pendente para todos os pedidos.

= 1.3.5 – 01/11/2022 =
- Otimizando query dos pedidos da cron.

= 1.3.4 – 26/10/2022 =
- Atualizando o estilo do checkout e minificando os arquivos.

= 1.3.2 – 24/10/2022 =
- Adicionando novo método de pagamento Pix à vista.

= 1.3.1 – 22/09/2022 =
- Resolvendo problema de erro e_parse.
- Removendo um fechamento de div.

= 1.3.0 – 26/04/2022 =
- Adicionando função para adicionar marketing widget na página de produto e carrinho.

= 1.2.0 - 18/04/2022 =
- Adicionando função para atualizar pedidos com status aguardando.

= 1.1.0 - 01/04/2022 =
- Resolvendo o problema de adicionar produto no carrinho quando cancelo a compra na pagaleve.

= 1.0.0 - 21/03/2022 =
- Release inicial
