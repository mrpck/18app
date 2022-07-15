<?php
namespace Mrpck\app18;

/**
 * 18app PHP SDK
 * @author Michele Rosica <michelerosica@gmail.com>
 * 
 * 18app
 * https://www.18app.italia.it/
 *
 *
 * Endpoint aggiornati dei nostri ambienti:
 *
 * validazione
 * https://wstest.18app.italia.it/VerificaVoucherWEB/VerificaVoucher
 *
 *
 * produzione
 * https://ws.18app.italia.it/VerificaVoucherWEB/VerificaVoucher
 *
 */
class app18 
{
	var $pi_esercente;
	var $log_path;
	var $client = null;

	/**
	* Constructor
	*/
	/*
    function __construct($host="localhost", $database=null, $user="root", $password="") 
	{
		$this->user = $user;
		$this->password = $password;
		$this->server = $host;
		$this->dbname = $database;

        // connecting to database
        $this->sql_connect();
    }
	*/
	
	/**
	 * Costruttore della classe app18
	 * 
	 * @param string $certificato_ssl Url del certificato dell'esercente
	 * @param string $passphrase Password del certificato dell'esercente
	 * @param string $wdsl_url Url del WDSL
	 * @param string $pi_esercente Partita IVA dell'esercente
	 * @param string $log_path Url del file di log
	 * @return NULL
	 */
	function __construct($certificato_ssl, $passphrase, $wdsl_url, $pi_esercente, $log_path="") 
	{
		/*
		$options = array(
		'location'      => $location_url,
		'local_cert'    => $certificato_ssl,
		'passphrase'    => $passphrase,
		'stream_context'=> stream_context_create(array('ssl' => 
			array(
			   'verify_peer'=>false,
			   'verify_peer_name'=>false,
			   'allow_self_signed' => true
			)))
		);
		*/
		$this->pi_esercente = $pi_esercente;
		$this->log_path     = $log_path;
		//$this->client       = new SoapClient($wdsl_url, $options);
		
		// $certificato_ssl || __DIR__ . '/certificato.pem' 
		$ssl = file_exists($certificato_ssl);

		$ch = curl_init($wdsl_url);
		curl_setopt($ch, CURLOPT_URL, $wdsl_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if ($ssl) {
			//curl_setopt($ch, CURLOPT_HTTPHEADER, array('SOAPAction: ""')); 
			curl_setopt($ch, CURLOPT_CAPATH, __DIR__);
			curl_setopt($ch, CURLOPT_CAINFO, $certificato_ssl);
			curl_setopt($ch, CURLOPT_SSLCERT, $certificato_ssl);
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, "PEM");
			curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $passphrase);

			//curl_setopt($ch, CURLOPT_SSLCERT, 'PROTPLUSSOL_SSO.pem');
			//curl_setopt($ch, CURLOPT_SSLCERTPASSWD, 'xxxxxxxxxxxx');
		}
		else {
			// for debug only!
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$headers = array(
		   "Content-Type: application/soap+xml",
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$this->client = $ch;
	}

	 /*
	function __construct($location_url, $certificato_ssl, $passphrase, $wdsl_url, $pi_esercente, $log_path="") 
	{
		$options = array(
		'location'      => $location_url,
		'local_cert'    => $certificato_ssl,
		'passphrase'    => $passphrase,
		'stream_context'=> stream_context_create(array('ssl' => 
			array(
			   'verify_peer'=>false,
			   'verify_peer_name'=>false,
			   'allow_self_signed' => true
			)))
		);

		$this->pi_esercente = $pi_esercente;
		$this->log_path     = $log_path;
		$this->client       = new SoapClient($wdsl_url, $options);
	}
	*/

	/**
	* destructor
	*/
    function __destruct() 
	{
        // closing db connection
		if ($this->client != null)
			curl_close($this->client);
    }
	
	public final function VerificaVoucher($codice_voucher) 
	{
		if ($this->client == null)
			return;

		$data = <<< XML
		<?xml version="1.0" encoding="utf-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ver="http://bonus.mibact.it/VerificaVoucher/">
		   <soapenv:Header/>
		   <soapenv:Body>
			  <ver:CheckRequestObj>
				 <checkReq>
					<tipoOperazione>1</tipoOperazione>
					<codiceVoucher>$codice_voucher</codiceVoucher>
					<!--Optional:-->
					<partitaIvaEsercente>$this->pi_esercente</partitaIvaEsercente>
				 </checkReq>
			  </ver:CheckRequestObj>
		   </soapenv:Body>
		</soapenv:Envelope>
		XML;
		
		curl_setopt($this->client, CURLOPT_POSTFIELDS, $data);
		
		$response = curl_exec($this->client);
		$error    = curl_error($this->client);
		$httpcode = curl_getinfo($this->client, CURLINFO_HTTP_CODE);
		//curl_close($this->client);

		// Check if any error occured
		if($error)
		{
			echo 'Error no : '.curl_errno($this->client).' Curl error: ' . curl_error($this->client); die;
			//var_dump($response, $httpcode, $error); die;
		}

		//return $this->Esegui_Operazione(1, $codice_voucher, NULL);
		return $response;
	}
}
