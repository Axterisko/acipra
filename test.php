<?php
class Webservice extends SoapClient{

	function __construct($wsdl, $options = array()) {
		parent::__construct($wsdl, $options);
	  }

	function __doRequest($request, $location, $action, $version, $one_way = 0) {
//		echo "<pre>".print_r(func_get_args(),true);
		$location = "https://aci.ancitel.it/acipra-ws-server/ws/";
		return parent::__doRequest($request, $location, $action, $version, $one_way = 0);
	}
}

$client = new Webservice("https://aci.ancitel.it/acipra-ws-server/ws/wsAcipra.wsdl");

$DatiRichiesta = array(
	"DataRichiesta" => "21/11/2016",
	"TipoRichiesta" => "Targa",
	"SerieTarga" => "1",
	"Targa" => "DE905EJ",
);

/*try{
	$result = $client->__soapCall('visura-targa-telaio',array(array(
		"username" => "ufficioverbali.gaiola@vallestura.cn.it",
		"password" => "@verbali2016",
		"username" => "test",
		"password" => "test",
		"DatiRichiesta" => $DatiRichiesta,
		//"canale" => "test",
	)));
}catch(Exception $e){
	$result = $e;
}*/
$result = json_decode(file_get_contents("visura.json"));
//echo "<pre>".print_r($result,true);
$out = fopen('php://output', 'w');
fputcsv($out, array(
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
	
));
if(isset($result->DatiRisposta)){
	
	$rsArr = array(
		$DatiRichiesta["DataRichiesta"],
		$DatiRichiesta["TipoRichiesta"],
		$DatiRichiesta["SerieTarga"],
		$DatiRichiesta["Targa"],
		$result->DatiRisposta->Esito->CodiceEsito,
		$result->DatiRisposta->Esito->DescrizioneEsito,
	);
	
	if(isset($result->DatiRisposta->DatiRecuperati)){
		
		$SiglaTipoVeicolo = @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->SerieTarga->Codice;
		$SiglaTipoVeicolo = (trim($SiglaTipoVeicolo))?$SiglaTipoVeicolo:$DatiRichiesta["SerieTarga"];
		
		$SituazioneTarga = "";
		$SituazioneTarga = @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->TargaEstera;
		$SituazioneTarga .=(trim($SituazioneTarga))?"|":"";
		$SituazioneTarga .= @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->NazionalitaTargaEstera;
		$SituazioneTarga .=(trim($SituazioneTarga))?"|":"";
		$SituazioneTarga .= @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->TargaSpeciale;
		$SituazioneTarga .=(trim($SituazioneTarga))?"|":"";
		$SituazioneTarga .= @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->TargaPrecedente;
		
		$DataPrimaIscrizione = '';
		if(@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita){
			if(preg_match("/PRIMA ISCRIZ/ui",@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Descrizione))
				$DataPrimaIscrizione = @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Data;
		}
		if(@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->PrimaFormalita){
			if(preg_match("/PRIMA ISCRIZ/ui",@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->PrimaFormalita->Descrizione))
				$DataPrimaIscrizione = @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->PrimaFormalita->Data;
		}
		$CausaleUltimaFormalita = "";
		$CausaleUltimaFormalita = @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->CausaleRadiazione;
		$CausaleUltimaFormalita .=(trim($CausaleUltimaFormalita))?"|":"";
		$CausaleUltimaFormalita = @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->CausaleRinnovo;
		$CausaleUltimaFormalita .=(trim($CausaleUltimaFormalita))?"|":"";
		$CausaleUltimaFormalita = @$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->CausaleRinnovo;
		$CausaleUltimaFormalita .=(trim($CausaleUltimaFormalita))?"|":"";
		
		$rsArr = array_merge($rsArr,array(
			$result->DatiRisposta->DatiRecuperati->DatiVeicolo->Targa,
			$result->DatiRisposta->DatiRecuperati->DatiVeicolo->SerieTarga->Descrizione,
			$SiglaTipoVeicolo,
			$SituazioneTarga,
			$result->DatiRisposta->DatiRecuperati->DatiTecnici->Telaio,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Classe->Codice,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Classe->Descrizione,
			trim($result->DatiRisposta->DatiRecuperati->DatiTecnici->ModelloCommerciale->Fabbrica." ".$result->DatiRisposta->DatiRecuperati->DatiTecnici->ModelloCommerciale->Tipo." ".$result->DatiRisposta->DatiRecuperati->DatiTecnici->ModelloCommerciale->Serie),
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Alimentazione->Codice,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Alimentazione->Descrizione,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Cilindrata,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Kilowatt,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Cavallifiscali,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Carrozzeria->Codice,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Carrozzeria->Descrizione,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Specialita->Codice,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Specialita->Descrizione,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Uso->Codice,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Uso->Descrizione,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Posti,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Portata,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Tara,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->PesoComplessivo,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->Assi,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->FlagFuoristrada,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->NormativaAntinquinamento,
			"I",
			"INTESTATARIO",
			trim(@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Denominazione->Cognome." ".@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Denominazione->Nome) ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Denominazione->Sesso ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Denominazione->TipoSocieta ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Denominazione->CodiceFiscale ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Denominazione->PartitaIva ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Nascita->Data ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Nascita->Comune ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Nascita->Provincia ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Nascita->Stato ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Nascita->ComuneNascitaFinanze ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Nascita->ComuneNascitaIstat ,
			trim(@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->Dug." ".@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->Toponimo." ".@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->NumeroCivico) ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->Cap ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->Frazione ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->Comune ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->Provincia ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->Stato ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->ComuneResidenzaFinanze ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Residenza->ComuneResidenzaIstat ,
			@$result->DatiRisposta->DatiRecuperati->DatiTecnici->DataPrimaImmatricolazione ,
			$DataPrimaIscrizione,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Formalita->Atto->Data ,
			@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Formalita->Data ,
			@$result->DatiRisposta->DatiRecuperati->DatiLocazione->DataScadenza ,
			@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Codice,
			@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Data,
			trim(@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Rp->Settore.@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Rp->Progressivo.@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Rp->Controllo),
			@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Descrizione,
			@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->DataPerditaPossesso,
			$CausaleUltimaFormalita,
		));
	}
	
	
	fputcsv($out, $rsArr);
}else{
	fputcsv($out, array(
		$DatiRichiesta["DataRichiesta"],
		$DatiRichiesta["TipoRichiesta"],
		$DatiRichiesta["SerieTarga"],
		$DatiRichiesta["Targa"],
		$result->getCode(),
		$result->getMessage(),
	));
}
fclose($out);
echo "<pre>".print_r($result,true);

?>