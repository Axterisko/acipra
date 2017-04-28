<?php require dirname(__FILE__)."/app/app.php";
$app = new app;

if(isset($_GET["download"])){
	$app->download($_GET["download"]);
	exit();
	
}

if(isset($_POST["submit_request"])){

	//printr($_POST);
	//controllo che i campi siano pieni
	$error = false;
	echo "<script>";
	foreach($_POST["targa"] as $id => $targa){
		
		$data = $_POST["data"][$id];
		$tipoVeicolo = $_POST["tipo_veicolo"][$id];
		
		
		if(!trim($targa)){
			$error = true;
			echo "parent.set_error_field(\".row:eq($id) [name='targa[]']\",'Inserisci una targa');";
		}
		if(!trim($data)){
			$error = true;
			echo "parent.set_error_field(\".row:eq($id) [name='data[]']\",'Inserisci una data');";
		}elseif(strtotime($data)> time()){
			$error = true;
			echo "parent.set_error_field(\".row:eq($id) [name='data[]']\",'Inserisci una data passata');";
		}
		
	}
	if($error){
		echo "parent.set_error('Compila correttamente tutti i campi!');";	
		echo "parent.hide_loading();";	
	}
	echo "</script>";
	
	if(!$error){
		
		$app->create_csv_file("visure-targa-".date("YmdHis").".csv");
		
		foreach($_POST["targa"] as $id => $targa){
			
			$data = date("d/m/Y",strtotime($_POST["data"][$id]));
			$tipoVeicolo = $_POST["tipo_veicolo"][$id];
			
			$result = $app->getDatiVisuraTarga($targa,$data,$tipoVeicolo);
			
			$DatiRichiesta = array(
				"DataRichiesta" => $data,
				"TipoRichiesta" => "Targa",
				"SerieTarga" => $tipoVeicolo,
				"Targa" => $targa,
			);
			
			
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
							$DataPrimaIscrizione = format_date(@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Data);
					}
					if(@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->PrimaFormalita){
						if(preg_match("/PRIMA ISCRIZ/ui",@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->PrimaFormalita->Descrizione))
							$DataPrimaIscrizione = format_date(@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->PrimaFormalita->Data);
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
						format_date(@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Intestatario->Nascita->Data) ,
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
						format_date(@$result->DatiRisposta->DatiRecuperati->DatiTecnici->DataPrimaImmatricolazione) ,
						'',
						$DataPrimaIscrizione,
						format_date(@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Formalita->Atto->Data) ,
						format_date(@$result->DatiRisposta->DatiRecuperati->DatiIntestazione->Formalita->Data) ,
						format_date(@$result->DatiRisposta->DatiRecuperati->DatiLocazione->DataScadenza) ,
						@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Codice,
						format_date(@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Data),
						trim(@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Rp->Settore.@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Rp->Progressivo.@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Rp->Controllo),
						@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->Descrizione,
						format_date(@$result->DatiRisposta->DatiRecuperati->DatiVeicolo->UltimaFormalita->DataPerditaPossesso),
						$CausaleUltimaFormalita,
					));
				}
				
				
				$app->write_csv_file($rsArr);
			}else{
				$app->write_csv_file(array(
					$DatiRichiesta["DataRichiesta"],
					$DatiRichiesta["TipoRichiesta"],
					$DatiRichiesta["SerieTarga"],
					$DatiRichiesta["Targa"],
					$result->getCode(),
					$result->getMessage(),
				));
			}

		
		}
		$app->close_csv_file();

		echo '<script>
		parent.set_success("Recupero visure terminato, verrà scaricato automaticamente il file CSV con i dati.");
		//parent.reset_form();
		parent.hide_loading();
		document.location.href = "?download='.$app->get_csv_file_filepath().'";
		</script>';
	}
	
	

	exit();	
}
 ?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>ACI PRA Visura per Targa</title>
<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
<link href="assets/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="assets/css/main.css" rel="stylesheet">
</head>

<body>
<div class="overlay-loading">
    <div class="overlay-wrapper">
        <div class="overlay">
            <i class="fa fa-refresh fa-spin"></i>
        </div>
        <span>Loading...</span>
    </div>
</div>
<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
           <a class="navbar-brand" href="#">ACI PRA Visure per Targa</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">

      <div class="starter-template">
        <h1>Visura per targa</h1>
        <form method="post" enctype="multipart/form-data" target="srv_iframe">
        <div class="row">
        	<div class="form-group col-sm-3">
            	<label>Targa</label>
                <input type="text" class="form-control" name="targa[]" value="">
            </div>
        	<div class="form-group col-sm-4">
            	<label>Data</label>
                <input type="date" class="form-control" name="data[]" value="">
            </div>
        	<div class="form-group col-sm-4">
            	<label>Tipo veicolo</label>
                <select class="form-control" name="tipo_veicolo[]">
                	<option value="1">AUTOVEICOLO</option>
                    <option value="2">RIMORCHIO</option>
                    <option value="4">MOTOVEICOLO</option>
                </select>
            </div>
            <div class="col-sm-1 delete-targa">
            	<a href="#" data-toggle='delete-targa'><i class="fa fa-trash"></i></a>
            </div>
        </div>
        <div class="form-group">
            <a href="#" data-toggle="add-targa">Aggiungi targa</a>
        </div>
        <div class="form-group">
        	<button type="reset" class="btn btn-default">Annulla</button>
        	<button type="submit" class="btn btn-primary">Invia richiesta</button>
        </div>
        	<input type="hidden" name="submit_request" value="1">
        </form>
      </div>

    </div><!-- /.container -->

<iframe name="srv_iframe" id="srv_iframe"></iframe>
<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>
