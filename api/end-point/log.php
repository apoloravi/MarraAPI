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

class Log
{
	/**
	 * save
	 * 
	 * método utilizado para salvar os logs
	 *
	 * @param string $message A mensagem a ser salva
	 * @param string|null $type O tipo da requisição (POST,GET) - null para logs escritos manualmente
	 * @return void
	 */
	public static function save($message, $type = null)
	{
		/**
		 * paramos aqui se o debug está desativado...
		 */
		if (debug === false)
		{
			return;
		}

		/**
		 * começa o processo de salvamento do log
		 */
		$msg = '';
		
		$msg .= date('d/m/Y H:i:s');

		if ($type !== null)
		{
			$type = strtoupper($type);
			$msg .= ' - ['. $type .']';
		}

		$msg .= ' - ' . $message;

		$msg .= "\r";

		file_put_contents('LOG.txt', $msg, FILE_APPEND);
	}
}