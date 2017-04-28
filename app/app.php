<?php
ini_set("display_errors",1);
error_reporting(E_ALL);
date_default_timezone_set ( 'Europe/Rome' );

class AciPraWebservice extends SoapClient{

	private $username;
	private $password;
	private $canale = "gaiola";


	private $filepath;
	private $filehandle;

	function __construct($wsdl, $options = array()) {
		parent::__construct($wsdl, $options);
	  }

	function __doRequest($request, $location, $action, $version, $one_way = 0) {
		return parent::__doRequest($request, $location, $action, $version, $one_way = 0);
	}
	
	public function setCredential($username,$password){
		$this->username = $username;
		$this->password = $password;	
	}
	
	public function visuraTargaTelaio($DatiRichiesta = array()){
		try{
			return $this->__soapCall('visura-targa-telaio',array(array(
				"username" => $this->username,
				"password" => $this->password,
				"canale" => $this->canale,
				"DatiRichiesta" => $DatiRichiesta,
			)));
		}catch(Exception $e){
			return $e;
		}
	}
}
function printr($text){echo "<pre>".print_r($text,true)."</pre>";}
function format_date($data){ return str_replace('+01:00','',$data);}
class app{
	private $config;
	private $acipraws;
	public function __construct(){
		$this->config = include dirname(__FILE__)."/../config/config.php";
	
		$this->acipraws = new AciPraWebservice($this->config["AciPraWebservice"]["wsdl"],array(
			"location" => $this->config["AciPraWebservice"]["location"]
		));
		
		$this->acipraws->setCredential($this->config["AciPraWebservice"]["username"],$this->config["AciPraWebservice"]["password"]);
	
	}
	
	
	
	public function getDatiVisuraTarga($targa = "",$data = "", $tipoVeicolo = ""){
		$DatiRichiesta = array(
			"DataRichiesta" => $data,
			"TipoRichiesta" => "Targa",
			"SerieTarga" => $tipoVeicolo,
			"Targa" => $targa,
		);
		
		$id = md5("visura-targa-".implode($DatiRichiesta));
		
		$cacheFile = dirname(__FILE__)."/../data/".$id.".json";
		
		if(file_exists($cacheFile)){
			$content = file_get_contents($cacheFile);
			return json_decode($content);
		}else{
			
			//$result = json_decode(file_get_contents("visura.json"));
			//return $result;
			$result = $this->acipraws->visuraTargaTelaio($DatiRichiesta);
			if(!$result instanceof Exception && @$result->DatiRisposta->Esito->CodiceEsito == 'VTT000')
				file_put_contents($cacheFile,json_encode($result));
			return $result;
		}
		return false;
	}
	
	function create_csv_file($filename){
		$this->filepath = sys_get_temp_dir()."/".$filename;
		$this->filehandle = fopen($this->filepath,"w");
		fputs($this->filehandle , $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		//testata
		fputcsv($this->filehandle, array(
			"Data Richiesta",
			"Tipo Richiesta",
			"Serie Targa Richiesta",
			"Targa Richiesta",
			"Codice Esito",
			"Descrizione Esito",
			"Targa",
			"Tipo Veicolo",
			"Sigla Tipo Veicolo",
			"Situazione Targa",
			"Telaio",
			"Codice Classe",
			"Classe",
			"Modello",
			"Codice Alimentazione",
			"Alimentazione",
			"Cilindrata",
			"Potenza Kw",
			"Potenza Hp",
			"Codice Carrozzeria",
			"Carrozzeria",
			"Codice Specialità",
			"Specialità",
			"Codice Uso",
			"Uso",
			"Numero posti",
			"Portata",
			"Tara",
			"Peso Complessivo",
			"Numero Assi",
			"Indicatore Veicolo Fuoristrada",
			"Indicatore Veicolo Eco Diesel",
			"Codice Tipo Soggetto",
			"Tipo Soggetto",
			"Cognome Nome Denominazione",
			"Sesso",
			"Tipo Società",
			"Codice Fiscale",
			"Partita IVA",
			"Data Nascita",
			"Comune Nascita",
			"Provincia Nascita",
			"Stato Nascita",
			"Comune Nascita Finanze",
			"Comune Nascita Istat",
			"Indirizzo Residenza",
			"CAP Residenza",
			"Frazione Residenza",
			"Comune Residenza",
			"Provincia Residenza",
			"Stato Residenza",
			"Comune Residenza Finanze",
			"Comune Residenza Istat",
			"Data Prima Immatricolazione",
			"Data Immatricolazione",
			"Data Prima Iscrizone",
			"Data Atto",
			"Data Registrazione Intestazione",
			"Data Scadenza Locazione",
			"Codice Ultima Formalità",
			"Data Ultima Formalità",
			"Numero Progressivo Ultima Formalità",
			"Stato Formalità",
			"Data Perdia Possesso Radiazione",
			"Causale Ultima Formalità",
			
		),";");
		
			
	}
	
	function write_csv_file($values){
		fputcsv($this->filehandle, $values,";");
	}
	function close_csv_file(){
		fclose($this->filehandle);
	}
	
	function get_csv_file_filepath(){
		return $this->filepath;
	}
	
	function mime_type($filepath){
		//elenco dei mime types  
		$mime_types = array(  
			"bmp" 	=> "image/bmp",  
			"exe" 	=> "application/octet-stream",  
			"pdf" 	=> "application/pdf",  
			"html"	=> "text/html",  
			"ico" 	=> "image/x-icon",  
			"jpeg" 	=> "image/jpeg",  
			"jpg" 	=> "image/jpeg",  
			"png" 	=> "image/png",  
			"gif" 	=> "image/gif",  
			"mov" 	=> "video/quicktime",  
			"mp3" 	=> "audio/mpeg",  
			"mp4" 	=> "video/mpeg",  
			"mpeg" 	=> "video/mpeg",  
			"mpg" 	=> "video/mpeg",  
			"txt" 	=> "text/plain",  
			"wav" 	=> "audio/x-wav",  
			"xls" 	=> "application/octet-stream",
			"zip" 	=> "application/zip"
		);
		
		$ext = substr(strrchr($filepath,'.'),1);  
		
		if(isset($mime_types[$ext]))
			return $mime_types[$ext];
		else
			return "application/octet-stream";
	}
	
	public function extension($filepath){
		$vpath = explode(".",$filepath);
		return end($vpath);
	}
	
	public function download($filepath){
		$len = filesize($filepath);
		$ext = self::extension($filepath);
		$filename = basename($filepath);
		$filecontent = file_get_contents($filepath);
		$mimetype = self::mime_type($filepath);
		
		header('Set-Cookie: fileDownload=true; path=/');
		header('Cache-Control: max-age=60, must-revalidate');

		if(preg_match("/MSIE ([0-9].[0-9]{1,2})/",$_SERVER["HTTP_USER_AGENT"])){
			//MICROSOFT IEXPLORER
			header("Content-type: $mimetype");
			header("Content-Length: $len");
			header("Content-disposition: attachment; filename=\"$filename\"");
			header("Expires: 0");
			header("Cache-control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: public");
		}else{
			//MOZILLA FIREFOX
			header("Content-type: $mimetype");
			header("Content-Length: $len");
			header("Content-Disposition: attachment; filename=\"$filename\"");
			header("Expires: 0");
			header("Pragma: no-cache");
		}	
		echo $filecontent;
		exit();
	}
	
}
?>