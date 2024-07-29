
<h1> Documentação </h1>

Documentação detalhada do pacote para integração com API do Bling


<h2> 1 - Tabela Bling_Tokens </h2>

Composto de dois únicos campos: access_token e refresh_token já preenchidos com valores aleatorios


<h2> 2 - Arquivo Config.php </h2>


Definindo as variáveis estáticas para o banco de dados e rota inicial.


<h2> 3 - Mysql.php </h2>


Classe que faz a conexão com o banco


<h2> 4 - BlingModel </h2>

Classe de modelo do que buscará as informações na tabela do banco de dados.
Possui dois metodos:

BlingModel::getRefreshToken(): busca o refresh token no banco

BlingModel::update($access_code, $refresh_code) : atualiza os tokens do banco


<h2> 5 - Bling API </h2>

Por fim a classe principal que faz a comunicação com o Bling.

Ela necessita das variaveis constantes de client_id e client_secret.

seus métodos só podem ser utilizados ao instanciar uma classe

$bling = new Bling(); #não é passadao nenhum parâmetro

O método construtor busca se existe um access code e seta a variavel privada bearerToken


$bling->api() // faz a requisição que inicia o processo de autenticação, envia a requisição ao Bling com o client_id solicitando o autorization_code, ao executa-la você sera redirecionado para uma tela de login do bling para autorizar que a aplicação tenha acesso a api.


$bling->requestApi()  // é o metodo que recebe o calback do servidor Bling com o get['code'],  após receber o autorization code faz nova requisição solicitando o access_token e o refresh_token que são resgatados e salvos no banco de dados.


$bling->connect($endpoint, $code)  // método de requisição principal da classe, por padrão é uma requisição GET, a API do Bling, com o access tokens já setado espera nos parametros o endpoint e a requisição a ser feita. Verifica se o token é valido, se não usa chama o método refreshToken() e redireciona para raiz. Se é valido entrega a requisição inicialmente pedida.

$bling->refreshToken() // faz a requisição de um novo access_code com base no ultimo refreshToken e atualiza o banco de dados.


Os demais métodos buscam informações no Bling com a classe connect() como base.

A página index, contém um exemplo de todos os métodos public disponíveis. 


