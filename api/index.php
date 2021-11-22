<?php
/**
 * 
 * ZapMe - Notificações Inteligentes via WhatsApp
 * Copyright (c) 2020
 * GARANTIMOS A FUNCIONALIDADE DESTE ARQUIVO CASO O MESMO NaO SOFRA ALTERAÇÕES.
 *
 * @package   ZapMe
 * @author    ZapMe
 * @copyright 2020
 * @link      https://zapme.com.br
 * @since     Version 1.1
 * 
 */

 /**
  * não mexa
  */
header('Content-Type: application/json');

/**
 * preencha com sua api de usuário da ZapMe
 */
define('reseller_api',       'api-ef25005d682d56f1ce85');

/**
 * preencha com sua chave secreta de usuario da ZapMe
 */
define('reseller_secret',    '6247895031');

/**
 * preencha com seu constrait de revendedor da ZapMe
 */
define('reseller_constraint', 'rev_1KXCJS-00SKCJD');

/**
 * ative para debugar problemas na requisição
 */
define('debug', true);

/**
 * ative para debugar os dados contidos na requisição
 */
define('debug_data', true);

/**
 * inclusão dos recursos de log
 */
require 'log.php';

class Endpoint 
{
	/**
	 * method
	 * 
	 * salva o tipo da requisição - post ou get
	 *
	 * @author ZapMe
	 * @var string
	 */
	private $method = null;

	/**
	 * date
	 * 
	 * salva a data da requisição, padrão Y-m-d H:i:s
	 *
	 * @author ZapMe
	 * @var string
	 */
	private $date   = '';

	/**
	 * data
	 * 
	 * salva o array de dados - post ou get
	 *
	 * @author ZapMe
	 * @var array
	 */
	private $data   = [];

	/**
	 * app
	 * 
	 * salva o tipo do aplicativo
	 *
	 * @author ZapMe
	 * @var array
	 */
	private $app   = null;

	public function __construct()
	{
		$server = $_SERVER;
		$post   = $_POST;
		$get    = $_GET;

		$this->date   = date('Y-m-d H:i:s');

		$this->method = strtolower($server['REQUEST_METHOD']);

		/**
		 * debug dos dados recebidos por post/get
		 */
		if (debug_data === true)
		{
			Log::save('DATA POST: ' . var_export($post));
			Log::save('DATA GET:  ' . var_export($get));
		}

		switch ($this->method)
		{
			/**
			 * post
			 * ... dados padrões (api, secret, phone, message)
			 */
			case 'post':

					$this->data = $post;

				break;

			/**
			 * mk auth | sgp | ixc soft
			 * ... ou dados padrões (api, secret, phone, message)
			 */
			case 'get':

					$this->data = $get;

					if (isset($get['app']) && ($get['app'] === 'webservices' || $get['app'] === 'sgp'))
					{
						$this->app = $get['app'] === 'webservices' ? 'mkauth' : 'sgp';
						/**
						 * truncamos os dados para pegar
						 * somente o que é ideal para uso
						 */
						unset($this->data);
						$this->data = 
						[
							'api'     => $get['u'],
							'secret'  => $get['p'],
							'phone'   => $get['to'],
							'message' => $get['msg'],
						];
					}

					if (isset($get['user']) && isset($get['pw']) && isset($get['dest']) && isset($get['text']))
					{
						$this->app = 'ixc';
						/**
						 * truncamos os dados para pegar
						 * somente o que é ideal para uso
						 */
						unset($this->data);
						$this->data = 
						[
							'api'     => $get['user'],
							'secret'  => $get['pw'],
							'phone'   => "55" . $get['dest'], /* para IXC soft definimos o DDI por padrão */
							'message' => $get['text'],
						];
					}
				break;
		}
	}

	/**
	 * verification
	 * 
	 * realiza as etapas de verificações
	 *
	 * @author ZapMe
	 * 
	 * @return mixed
	 */
	private function verifications()
	{
		/**
		 * parametros de revendedor nao configurado
		 * necessário configurar os defines no inicio do arquivo
		 */
		if (reseller_api === 'preencha_aqui' || reseller_secret === 'preencha_aqui' || reseller_constraint === 'preencha_aqui')
		{
			header("HTTP/1.1 400 Bad Request");
			return ['result' => 'error', 'status' => 400, 'date' => $this->date, 'message' => 'ambiente nao preparado'];
		}
	
		/**
		 * nada a fazer ...
		 * dados de revendor configurado
		 * mas os dados essenciais não foram
		 * encontrados na requisição da api
		 */
		if (!isset($this->data['api']) && !isset($this->data['secret']) && !isset($this->data['phone']) && !isset($this->data['message']))
		{
			header("HTTP/1.1 400 Bad Request");
			return ['result' => 'error', 'status' => 400, 'date' => $this->date, 'message' => 'nada a fazer'];
		}

		/**
		 * requisição inválida - falta de api / secret
		 */
		if (!isset($this->data['api']) || !isset($this->data['secret']))
		{
			header("HTTP/1.1 400 Bad Request");
			return ['result' => 'error', 'status' => 400, 'date' => $this->date, 'message' => 'api ou chave secreta nao definido'];
		}

		/**
		 * requisição inválida - `phone` indefinido ou em branco
		 */
		if (!isset($this->data['phone']) || empty($this->data['phone']))
		{
			header("HTTP/1.1 400 Bad Request");
			return ['result' => 'error', 'status' => 400, 'date' => $this->date, 'message' => 'parametro phone nao definido ou em branco'];
		}

		/**
		 * requisição inválida - `message` indefinida ou em branco
		 */
		if (!isset($this->data['message']) || empty($this->data['message']))
		{
			header("HTTP/1.1 400 Bad Request");
			return ['result' => 'error', 'status' => 400, 'date' => $this->date, 'message' => 'parametro message nao definido ou em branco'];
		}

		/**
		 * validação concluída ... tudo ok!
		 */
		return true;
	}

	/**
	 * run
	 * 
	 * método que realiza todas as etapas 
	 * essenciais para o end-point funcionar
	 *
	 * @author ZapMe
	 * 
	 * @return mixed
	 */
	public function run()
	{

		/**
		 * iniciamos a validação...
		 */
		$verifications = $this->verifications();

		/**
		 * se nao true, imprimimos o erro
		 */
		if ($verifications !== true)
		{
			Log::save('ERRO: ' . $verifications['message'], $this->method);
			return json_encode($verifications);
		}

		header("HTTP/1.1 200 OK");

		/**
		 * realizamos o cURL
		 */
		$return = $this->cURL();

		/**
		 * sucesso... mensagem adicionada a fila
		 */
		if (isset($return['result']) && $return['result'] === 'success' && $return['status'] == 200)
		{
			$result = ['result' => 'success', 'status' => 200, 'date' => $this->date, 'message' => 'mensagem adicionada a fila'];

			/**
			 * salvamos o log de sucesso
			 */
			Log::save('SUCESSO: ' . $return['message'] . ' - Id: ' . $return['messageid']);
		}
		/**
		 * erro interpretado pela ZapMe ... $json['message'] dará a razão do erro
		 */
		else
		{
			$result  = ['result' => 'error', 'status' => 400, 'date' => $this->date, 'message' => $return['message']];

			/**
			 * salvamos o log de erro
			 */
			Log::save('ERRO: ' . $return['message'], $this->method);
		}

		/**
		 * app não nulo ... retorno de provedor
		 */
		if ($this->app !== null)
		{
			switch ($this->app)
			{
				/**
				 * mk auth - não importa
				 */
				case 'mkauth':
						return json_encode($result);
					break;
				/**
				 * sgp - espera retornar somente a frase
				 */
				case 'sgp':
						return $result['message'];
					break;
				/**
				 * ixc - espera retornar true
				 */
				case 'ixc':
						return true;
					break;
			}
		}

		return json_encode($result);
	}

	/**
	 * cURL
	 * 
	 * requisição por função privada 
	 * apenas para melhor organização
	 *
	 * @author ZapMe
	 * @return void
	 */
	private function cURL()
	{
		$data =
		[
			'api'          => reseller_api,
			'secret'       => reseller_secret,
			'method'       => 'iamreseller',
			'constraint'   => reseller_constraint,
			'clientapi'    => $this->data['api'],
			'clientsecret' => $this->data['secret'],
			'todo'         => 'sendmessage',
			'phone'        => $this->data['phone'],
			'message'      => $this->data['message'],
		];

		if (isset($this->data['document']) && isset($this->data['filetype']))
		{
			$data += 
			[
				'document' => $this->data['document'],
				'filetype' => $this->data['filetype'],
			];
		}

		$curl = curl_init('https://api.zapme.com.br');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($curl);
		curl_close($curl);
		return json_decode($response, true);
	}
}

/**
 * rodamos o core da aplicação
 */
echo (new Endpoint)->run();