<?php 


class Web_Tools {
	
	//configuracion de meses para fechas
	var $mesesShort =  array("01"=>"Ene", "02"=>"Feb", "03"=>"Mar", "04"=>"Abr", "05"=>"May", "06"=>"Jun", "07"=>"Jul", "08"=>"Ago", "09"=>"Sep", "10"=>"Oct", "11"=>"Nov", "12"=>"Dic");
	var $mesesFull =  array("01"=>"Enero", "02"=>"Febrero", "03"=>"Marzo", "04"=>"Abril", "05"=>"Mayo", "06"=>"Junio", "07"=>"Julio", "08"=>"Agosto", "09"=>"Septiembre", "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre");
	var $dias = array("Lunes","Martes","Miercoles","Jueves","Viernes","Sabado", "Domingo");

	var $msg_invalid_acces = "Usted ha accedido de forma incorrecta";
	var $msg_inusual_code = "Hemos detectado codigo potencialmente peligroso y ha sido bloqueado.";
	var $msg_inusual_activity = "Hemos detectado actividad inusual, hemos bloqueado el acceso";

    var $mailAdmin = "";
    var $mailVoluntarios = "";
    var $mailSuscripcion = "";  
    var $mailContactos = ""; 
	var $mailPQRS = ""; 
	
		
	/**** ********************************** ***/
	// ENVIA REPORTE DE ERRORES A UN LOGS DE ERRORES
	function report_Log($archivo, $pagina, $accion, $error){
	   date_default_timezone_set("America/Bogota");
	   $fp = fopen("../logs/".$archivo, "a+");
	   fwrite($fp, "PAGINA = ".$pagina." -- ACCION = ".$accion." -- FECHA = ".date("F j, Y, H:i a")."\nERROR = ".$error."\n------ \n"); 
	   fclose($fp);	
	}	

	/**** ********************************** ***/
	// ELIMINA FISICAMENTE UN ARCHIVO
	function Delete_File($imagenurl){
	   if ($imagenurl!="" && $imagenurl!=null){
		  if(file_exists($imagenurl)){
			if(unlink($imagenurl))
			   return true;
			else
			   return false;
		  }
	   }
	}

	/**** ********************************** ***/
	// GEBNERA 2 NUMEROS ALEATORIOS PARA CAPTCHA
	function recaptcha_items(){
	   $number1 = mt_rand(1,30);
	   $number2 = mt_rand(1,30);
	   $aleatorio = $number1." ".$number2;
	   return($aleatorio);
	}
	
	/**** *************************************************** ***/
	// GENERA AUTORIZACION SI TIPO USUARIO CONICIDE CON PERMISO
	function IsAuthorized($RolUserAuthor, $RolUserActive ){
	   //$UsAuthor es el ROL permitido segun modulo o accion
	   //$varTipouser es el ROL del usuario activo	
	   if($RolUserActive==$RolUserAuthor )
		  return true;
	   else
		  return false;
	}

	/**** ************************************************************* ***/
	/* CONTROLAR INYECCION SQL DEVUELVE FALSO O VERDADERO SEGUN SE EVALUE */  	
	function rastrear_unusual_code($valgetpost){

		//Secuencias inválidas
		$filtrar = array("select"," from ", "delete ","drop ","create ", "insert ", "user ", " * ", "'", "--","/*", "*/", "xp_", "where", " join ", " or ", " and ", " = ", "=", "like", ";", "null", "'", "%", "replace( ", "alter table", "create table", "create procedure", "create function", "exec ", "sp_", "declare ", "trusted=100", "char(", "0x");

		$datominus = strtolower($valgetpost);		
		$sw = 0;
		foreach ($filtrar as $valores){
			$encontrar = strpos($datominus, $valores );
			if($encontrar !== false)   $sw = 1;
		}
		if ($sw==0)	{ return true; }
		else { return false; }
		
	}
	
	/**** ****************************************************** ***/
	//FUNCION QUE TOMA LA FECHA Y LA DEVUELVE EN DISTINTOS FORMATOS
	function format_date($fecha, $formato, $separador){
		date_default_timezone_set("America/Bogota");

		$mifecha=explode($separador,$fecha);
		
		if($formato == "DATE_LARGE") // Ej: 23 DE JUNIO DE 2016
		   $new_fecha = $mifecha[2]." de ".$this->mesesFull[$mifecha[1]]." de ".$mifecha[0]; 
		
		//if($formato == "DATE_DAY_LARGE") // Ej: MARTES 23 DE JUNIO DE 2017
		  // $new_fecha = $dias[(date("N",strtotime($fecha)))-1].", ".$mifecha[2]." de ".$this->mesesFull[$mifecha[1]]." de ".$mifecha[0];
		
		if($formato == "DATE_SHORT") // Ej: 23 OCT 2017
		   $new_fecha = $mifecha[2]." de ".$this->mesesShort[$mifecha[1]]." de ".$mifecha[0]; 

		if($formato == "DATE_MINI") // Ej: 23 OCT 2017
		   $new_fecha = $mifecha[2]."/". strtoupper($this->mesesShort[$mifecha[1]])."/".$mifecha[0]; 
		
		return $new_fecha;
	
	}//fin format_date
	
	/**** ****************************************************** ***/
	// GENERA UNA SECUENCIIA ALEATORIA DE NUMERO Y LETRAS - TOTAL DE CARACTERES DEFINIDOS POR LE USUARIO
	function ticketAletorio($numChars) {  
	  $chars = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
	  $code = '';
	  $i = 0;
	  while ($i < $numChars) { 
		 $code .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
		 $i++;
	  }
	  return $code;
	}

	/**** ****************************************************** ***/
	// FUNCION QUE PERMITE ENVIAR EMAILS CON COPIAS
	function enviarMail($de_email, $de_nombre, $para, $asunto, $mensaje)	{	
	   //para_preEmail
	   $headers = "MIME-Version: 1.0\r\n"; // version
	   $headers .= "Content-type: text/html; charset=utf-8\r\n"; // definicion de codificacion de caracteres					   
	   $headers .= "From: ".$de_nombre." <".$de_email.">\r\n";	//dirección del remitente	FORMATO -->  Web Voluntarios <voluntarios@bomberoscienaga.org>
	   $headers .= "Return-path: ".$this->mailAdmin."\r\n"; //ruta del retorno 	   		   
	   //$headers .= "Cc: \r\n"; 	//copia	   	   	   	   
	   
	   try{
		   if (mail($para, $asunto, $mensaje, $headers)){		 // envio de correo a METROAGUA  		 
			   return true;
		   }else{
			   return false;
		   }
	   }catch(Exception $e){
	       return $e->getCode().":".$e->getMessage(); 
	   }

	}//FIN ENVIAR MENSAJES
		
}

//$ob = new Web_Tools();
//$ob->report_errors("registrousuario.php", "Eliminar", "No pudo");


?>