<?php


class Bling {

    private $bearerToken = '';
    const URL_API = 'https://bling.com.br/Api/v3/';

    const CLIENT_ID = ''; //INSIRA AQUI SEU CLIENT_ID
    const CLIENT_SECRET = ''; //INSIRA AQUI SEU CLIENT_SECRET


    function __construct() {

        $sql = Mysql::conectar()->prepare("SELECT `access_token` FROM `Bling_Tokens`");
        $sql->execute();
        $token = $sql->fetch()['access_token'];

        $this->bearerToken = $token;
    }

    #RETORNA CALLBACK COM O autorization CODE
    public function api() {

        $client_id = self::CLIENT_ID;
        $state = '42';
        $url = "https://bling.com.br/Api/v3/oauth/authorize?response_type=code&client_id=$client_id&state=$state";

        // Inicializa a sessão cURL
        $ch = curl_init();

        // Configura a URL e outras opções
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true); // Definindo o método GET

        // Executa a requisição e obtém a resposta
        $response = curl_exec($ch);
   
        // Verifica se ocorreu algum erro
        if ($response === false) {
            echo 'Erro: ' . curl_error($ch);
        } else {
            echo 'Resposta: ' . $response;
        }
        
        // Fecha a sessão cURL
        curl_close($ch);
    }

    #RECEBE autorization code CODE  E REQUISITA TOKEN ACCESS E REFRESH TOKEN
    public static function requestApi() {

        if(isset($_GET['code'])){
            echo $_GET['code'];
            #authorization code
            $code = $_GET['code'];
            
            #header necessário para requizição de token:
            $str = self::CLIENT_ID . ':' . self::CLIENT_SECRET;
            $base64BasicHeader = base64_encode($str);

            $url = 'https://www.bling.com.br/Api/v3/oauth/token';
            
            $data = [
                'grant_type' => 'authorization_code',
                'code' => $code
            ];

            // Define o cabeçalho Authorization com o valor apropriado
            $authorization = 'Basic ' . $base64BasicHeader;

            // Inicializa a sessão cURL
            $ch = curl_init();

            // Configura as opções cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Converte o array de dados para a string query

            // Define os cabeçalhos da requisição
            $headers = [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: 1.0',
                'Authorization: ' . $authorization
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // Executa a requisição e obtém a resposta
            $response = curl_exec($ch);

            // Verifica se ocorreu algum erro
            if ($response === false) {
                echo 'Erro: ' . curl_error($ch);
            } else {
                echo 'Resposta: ' . $response;
                $response = json_decode($response);
                //salvar dados no db
                BlingModel::update($response->access_token, $response->refresh_token);
            }

            // Fecha a sessão cURL
            curl_close($ch);

                    }
                }


    #envia o refresh token para receber novo token
    private function refreshToken(){

        #header necessário para requizição de token:
        $str = self::CLIENT_ID . ':' . self::CLIENT_SECRET;
        $base64BasicHeader = base64_encode($str);


        #get refresh token from a database
        $refresh_token = BlingModel::getRefreshToken();
        // URL da API
        $url = 'https://www.bling.com.br/Api/v3/oauth/token';

        // Dados do formulário
        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        ];

        // Credenciais base64 codificadas
        $authorization = 'Basic ' . $base64BasicHeader;

        // Inicializa a sessão cURL
        $ch = curl_init($url);

        // Configurações da sessão cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: 1.0',
            'Authorization: ' . $authorization
        ]);

        // Executa a requisição e obtém a resposta
        $response = curl_exec($ch);

        // Verifica se ocorreu um erro
        if ($response === false) {
            echo 'Erro: ' . curl_error($ch);
            return false;
        } else {
            // Exibe a resposta da API
            #echo 'Resposta: ' . $response;
            $response = json_decode($response);
            
            $access_token = $response->access_token;
            $refresh_token = $response->refresh_token;
            $this->bearerToken = 'Bearer ' . $access_token;
            //Salva no db access-token e refresh_token
            if(BlingModel::update($access_token, $refresh_token)){
                return true;
            } else {
                return false;
            };             
        }
        // Fecha a sessão cURL
        curl_close($ch);
            }


   

    #conexão curl base para a API, como GET
    private function connect($endpoint, $code) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL =>  self::URL_API . $endpoint . $code,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Authorization: Bearer ' . $this->bearerToken
            ),
        ));
        
        $response = json_decode(curl_exec($curl));
        
        if(isset($response->error)) {
            if($response->error->type == 'invalid_token'){
                //chama funçao refreshtoken que retorna true ou false
                self::refreshToken();
                header('Location: '. INITIAL_PATH);
                die();
                                 
            }
        }
          
        curl_close($curl);

        return $response;
    }

    #CODE 14-1310
    public function getProductByCode($code) {

        $response =  $this->connect('produtos?codigo=', $code);

        #echo $response;
        var_dump($response);
    } 
    
    public function listProducts($page = 1) {

       $response = $this->connect('produtos?pagina=', $page);

       /* $response->data[0]->nome */
       var_dump($response);
       #return $response;

    }

    
    public function getNumbOfPages() {

        $page = 1;
        $continue = True;

        $curl = curl_init();
        while($continue) {
            curl_setopt_array($curl, array(
                CURLOPT_URL =>  self::URL_API . 'produtos?pagina=' .$page,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Authorization: ' . $this->bearerToken
                ),
                ));
        
                $response = curl_exec($curl);
        
                $response = json_decode($response);
                if(count($response->data) == 0 ){
                    #not more pages
                    $continue = false;
                } else {
                    $page++;
                }
            
        }
        curl_close($curl);

        echo $page;
        
    }
}


?>
