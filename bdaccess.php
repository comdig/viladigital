<?php
	header("Content-Type: application/json");
	
	
	//SELECT count( * ) as total_record FROM student
	
	function conectaDB(){
		if(!mysql_connect("mysql.viladigital.comdig.info","vileiro","comdig")){
			echo "<h2>Erro na conexao com a base dados...</h2>"; 
			die();
		}
	    mysql_select_db("viladigital_01");
	   //echo "BD ok";
	}	
	//if(@$_REQUEST['action']=="connect"){
	//	conectaDB();
	//}
	//=========================================================================
	
	if(@$_REQUEST['action']=="refreshNewsT"){   //twitter
		    
		//get news from feed

        $session = curl_init($_GET['url']);		
				
		curl_setopt($session, CURLOPT_HEADER, false); 	       // Don't return HTTP headers
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);   // Do return the contents of the call
		//curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		//curl_setopt($session, CURLOPT_USERPWD, "ernielinds:toutatis");
		$xml = curl_exec($session); 	                       // Make the call
		header("Content-Type: text/xml"); 	                   // Set the content type appropriately
		 	      
		curl_close($session); // And close the session
		
		echo $xml; 
	}
		
	//=========================================================================
	
	if(@$_REQUEST['action']=="refreshNews"){
		    
		//get news from feed

        $session = curl_init($_GET['url']);		
				
		curl_setopt($session, CURLOPT_HEADER, false); 	       // Don't return HTTP headers
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);   // Do return the contents of the call
		$xml = curl_exec($session); 	                       // Make the call
		header("Content-Type: text/xml"); 	                   // Set the content type appropriately
		 	      
		curl_close($session); // And close the session
	
		//parse feed news
	
		  $doc = new DOMDocument();
		  $doc->loadXML($xml);

		//store new news (add to existing ones)
		
		    conectaDB();
			 $user=mysql_real_escape_string($_REQUEST['usuario']);
			 $cat=mysql_real_escape_string($_REQUEST['cat']);
			 $tip=mysql_real_escape_string($_REQUEST['tip']);
			 $linha=mysql_real_escape_string($_REQUEST['line']);
			 $coluna=mysql_real_escape_string($_REQUEST['column']);
		     $dateRetrieve=date("y-m-d");
			
			 $quantasNews = 0;
			
		  foreach ($doc->getElementsByTagName('item') as $node) {
		    if($tip<4) $itemRSS = '<item><title>'.$node->getElementsByTagName('title')->item(0)->nodeValue.'</title><desc>'.$node->getElementsByTagName('description')->item(0)->nodeValue.'</desc><link>'.$node->getElementsByTagName('link')->item(0)->nodeValue.'</link><date>'.$node->getElementsByTagName('pubDate')->item(0)->nodeValue.'</date><enclo>'.$node->getElementsByTagName('enclosure')->item(0)->nodeValue.'</enclo></item>';
		    else $itemRSS = '<item><title>'.$node->getElementsByTagName('title')->item(0)->nodeValue.'</title><desc>'.$node->getElementsByTagName('description')->item(0)->nodeValue.'</desc><link>'.$node->getElementsByTagName('link')->item(0)->nodeValue.'</link><date>'.$node->getElementsByTagName('pubDate')->item(0)->nodeValue.'</date></item>';	  		  			  
		    
			$hash=sha1($itemRSS);
		
			 //verifica se a noticia ja esta armazenada
		     if(mysql_num_rows(mysql_query("SELECT itemContent FROM newsUsr_$user WHERE hash = '$hash' "))){		
			 }
			 else {   //se nao acou noticia no DB, armazena
			       mysql_query("INSERT INTO newsUsr_$user ( codNews , ehFeedUsuario , codFeed , hash , catFeed , tipoFeed , LIN , COL , Lida , Salva , Avaliada , notaAvaliacao , Compartilhada , itemContent, dataLeitura, dataRetrieve) VALUES (NULL, 0, 36 ,'$hash', $cat , $tip , $linha, $coluna, 0, 0, 0, 0, 0, '$itemRSS' , '$dateRetrieve' , '$dateRetrieve' );"); 
			  
			       $quantasNews++; 
				  }
		   }	
		mysql_close();
		
		echo $xml;  
		
	}

	// ==========================================================================
	// Login & Registro==========================================================
	// ==========================================================================	
	
	if(@$_REQUEST['action']=="login"){
		conectaDB();
		$username=mysql_real_escape_string($_REQUEST['user']);
		$pass=mysql_real_escape_string($_REQUEST['pass']);
		$dateToday=date("y-m-d");
		$hashpass=sha1($pass);	
		
		$retData = array();
		if(mysql_num_rows(mysql_query("SELECT hash FROM usuarios WHERE password = '$hashpass' AND nomeUser = '$username' "))){
		  //usuario ja esta cadastrado
		   $resp= mysql_query("SELECT codUser FROM usuarios WHERE password = '$hashpass' AND nomeUser = '$username'");
		   $row=mysql_fetch_array($resp);		
		   $m=$row['codUser'];
		   $retData[]= "1";  //achou
		   $retData[]= $m;   //codUser
           echo json_encode($retData);
		}
		else {
		   $retData[]= "0"; //nao achou
		   $retData[]= "0";  
           echo json_encode($retData);
		}		
		mysql_close();		
	}

	if(@$_REQUEST['action']=="registerFB"){
		conectaDB();
		$username="";
		$email = "";
		$fb_id=mysql_real_escape_string($_REQUEST['id']);
		$fb_username=mysql_real_escape_string($_REQUEST['user']);
		$fb_name=mysql_real_escape_string($_REQUEST['name']);
		$fb_link=mysql_real_escape_string($_REQUEST['link']);
		$fb_gender=mysql_real_escape_string($_REQUEST['gender']);
		$fb_locale=mysql_real_escape_string($_REQUEST['loc']);

		//$pass=mysql_real_escape_string($_REQUEST['pass']);
		$dateToday=date("y-m-d");
		//$hashpass=sha1($pass);
		$hashpass="";
		$hash=sha1($username.$fb_id);
		
		$retData = array();
		if(mysql_num_rows(mysql_query("SELECT fb_id FROM usuarios WHERE fb_id = '$fb_id'"))){
		  //usuario ja esta cadastrado
		   $resp= mysql_query("SELECT codUser , codMat FROM usuarios WHERE fb_id = '$fb_id'");
		   $row=mysql_fetch_array($resp);		
		   $m=$row['codUser'];
		   $retData[]= "-1"; //indica que user ja estava cadastrado
		   $retData[]= (int)$m;   //codUser
		   $m=$row['codMat'];	
		   $retData[]= $m;   //codMat
           echo json_encode($retData);
		} 
		else {  //cadastra usuario novo
		   mysql_query("INSERT INTO usuarios ( codUser, nomeUser, password, hash, status, codMat, email, fb_name, fb_id, fb_username, fb_link, fb_gender, fb_locale, pontuacao, totLidas, quantFeedsExtras, dataRegistro, dataLastLogin) VALUES (NULL,'$username','$hashpass','$hash', 0, 0,'$email','$fb_name', $fb_id, '$fb_username', '$fb_link', '$fb_gender', '$fb_locale', 0,0,0,'$dateToday','$dateToday');");
		   $resp= mysql_query("SELECT codUser FROM usuarios WHERE hash = '$hash'");
		   $row=mysql_fetch_array($resp);		
		   $m=$row['codUser'];
		   $retData[]= "1";  //OK
		   $retData[]= (int)$m;   //codUser		
           $retData[]= "0";  //codMat		   
		  
    	   $tmpq = "CREATE TABLE IF NOT EXISTS feedsUsuario_".$m." ( codFeed int(11) NOT NULL AUTO_INCREMENT, nomeFeed varchar(80) NOT NULL, codUser int(11) NOT NULL, codCat int(11) NOT NULL, codTip int(11) NOT NULL, url varchar(500) NOT NULL, PRIMARY KEY (codFeed) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
           $tmpq2 = "CREATE TABLE IF NOT EXISTS newsUsr_".$m." ( codNews int(11) NOT NULL AUTO_INCREMENT,ehFeedUsuario int(11) NOT NULL,codFeed int(11) NOT NULL,hash varchar(40) NOT NULL,catFeed int(11) NOT NULL,tipoFeed int(11) NOT NULL,LIN int(11) NOT NULL,COL int(11) NOT NULL,Lida int(1) NOT NULL,Salva int(11) NOT NULL,Avaliada int(11) NOT NULL,notaAvaliacao int(11) NOT NULL,Compartilhada int(11) NOT NULL,itemContent varchar(6000) NOT NULL,dataLeitura date NOT NULL,dataRetrieve date NOT NULL,PRIMARY KEY (codNews) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";		  
		   
		   mysql_query($tmpq);
		   mysql_query($tmpq2);
		   echo json_encode($retData); //retorna codUser para engine		   
		}
		mysql_close();
	}
	
	//deprecated... by now, only fb users
	
	if(@$_REQUEST['action']=="registerOLD"){
		conectaDB();
		$username=mysql_real_escape_string($_REQUEST['username']);
		$email=mysql_real_escape_string($_REQUEST['email']);
		$pass=mysql_real_escape_string($_REQUEST['pass']);
		$dateToday=date("y-m-d");
		$hashpass=sha1($pass);
		$hash=sha1($username.$email); 
		
		$retData = array();
		if(mysql_num_rows(mysql_query("SELECT hash FROM usuarios WHERE hash = '$hash'"))){
		  //usuario ja esta cadastrado
		   $resp= mysql_query("SELECT codUser , codMat FROM usuarios WHERE hash = '$hash'");
		   $row=mysql_fetch_array($resp);		
		   $m=$row['codUser'];
		   $retData[]= "-1"; //erro
		   $retData[]= $m;   //codUser
		   $m=$row['codMat'];	
		   $retData[]= $m;   //codMat
           echo json_encode($retData);
		} 
		else {  //cadastra usuario novo
		   mysql_query("INSERT INTO usuarios ( codUser, nomeUser, password, hash, status, codMat, email, fb_name, fb_id, fb_username, fb_link, fb_gender, fb_locale, pontuacao, totLidas, quantFeedsExtras, dataRegistro, dataLastLogin) VALUES (NULL,'$username','$hashpass','$hash', 0, 0,'$email',NULL, 0, NULL, NULL, NULL, NULL, 0,0,0,'$dateToday','$dateToday');");
		   $resp= mysql_query("SELECT codUser FROM usuarios WHERE hash = '$hash'");
		   $row=mysql_fetch_array($resp);		
		   $m=$row['codUser'];
		   $retData[]= "1";  //OK
		   $retData[]= (int)$m;   //codUser		   
		   $tmpq = "CREATE TABLE IF NOT EXISTS feedsUsuario_".$m." ( codFeed int(11) NOT NULL AUTO_INCREMENT, nomeFeed varchar(80) NOT NULL, codUser int(11) NOT NULL, codCat int(11) NOT NULL, codTip int(11) NOT NULL, url varchar(350) NOT NULL, PRIMARY KEY (codFeed) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
           $tmpq2 = "CREATE TABLE IF NOT EXISTS newsUsr_".$m." ( codNews int(11) NOT NULL AUTO_INCREMENT,ehFeedUsuario int(11) NOT NULL,codFeed int(11) NOT NULL,hash varchar(40) NOT NULL,catFeed int(11) NOT NULL,tipoFeed int(11) NOT NULL,LIN int(11) NOT NULL,COL int(11) NOT NULL,Lida int(1) NOT NULL,Salva int(11) NOT NULL,Avaliada int(11) NOT NULL,notaAvaliacao int(11) NOT NULL,Compartilhada int(11) NOT NULL,itemContent varchar(1024) NOT NULL,dataLeitura date NOT NULL,dataRetrieve date NOT NULL,PRIMARY KEY (codNews) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";		  
		   
		   mysql_query($tmpq);
		   mysql_query($tmpq2);
		   echo json_encode($retData); //retorna codUser para engine		   
		}
		mysql_close();
	}
	
	// ==========================================================================
	// STORE   ==================================================================
	// ==========================================================================
	
	// NEWS
	
	if(@$_REQUEST['action']=="storeNews") {  
			
		conectaDB();
		$user=mysql_real_escape_string($_REQUEST['user']);
		$cat=mysql_real_escape_string($_REQUEST['cat']);
		$tip=mysql_real_escape_string($_REQUEST['tip']);
		$linha=mysql_real_escape_string($_REQUEST['line']);
		$coluna=mysql_real_escape_string($_REQUEST['column']);
		$otitulo=mysql_real_escape_string($_REQUEST['tit']);
		$olink=mysql_real_escape_string($_REQUEST['link']);	
		//$adescricao=mysql_real_escape_string($_REQUEST['desc']);
		//$pubdate=mysql_real_escape_string($_REQUEST['pubd']);		
		//$image=mysql_real_escape_string($_REQUEST['image']);
		
        //$hash=sha1($otitulo.$olink.$adescricao.$pubDate);	
		$hash=sha1($otitulo.$olink);
		$dateRetrieve=date("y-m-d");
		
	    $tmpq = "INSERT INTO newsUsuario_".$user." ( codNews , codUsuario , ehFeedUsuario , codFeed , hash , catFeed , tipoFeed , LIN , COL , Lida , Salva , Avaliada , notaAvaliacao , Compartilhada , itemTitle, itemLink, itemDescription, itemPubDate, dataLeitura, dataRetrieve) VALUES (NULL, ".$user.", '0', '36',".$hash." , ".$cat." , ".$tip." , ".$linha.", ".$coluna.", '0', '0', '0', '0', '0', ".$otitulo.", ".$olink." , 'abcabc' , ".$pubdate.", NULL, ".$dateRetrieve." );";
	    mysql_query($tmpq);
		mysql_close();
	}
	
	// MATRIX
	
	if(@$_REQUEST['action']=="storeMatrix") {  
			
		conectaDB();	
		$matrix=mysql_real_escape_string($_REQUEST['matrix']);
		$user=mysql_real_escape_string($_REQUEST['user']);
		$dateAccess=date("y-m-d");
		
		mysql_query("INSERT INTO mat (codMat, codUser, dateLastAccess,matStr) VALUES (NULL,$user,'$dateAccess','$matrix');");
	    //altera campo codMat da tabela usuarios para apontar para esta nova entrada da tabela mat
		$resp= mysql_query("SELECT codMat FROM mat WHERE codUser = $user");
		$row=mysql_fetch_array($resp);		
		$cm=$row['codMat'];
		$tmpq = "UPDATE usuarios SET codMat = ".$cm." WHERE codUser = ".$user.";";
		mysql_query($tmpq);
		mysql_close();
	}
	if(@$_REQUEST['action']=="updateMatrix") {  
			
		conectaDB();	
		$matrix=mysql_real_escape_string($_REQUEST['matrix']);
		$user=mysql_real_escape_string($_REQUEST['user']);
		
		$retData = array();

		$tmpq = "UPDATE mat SET matStr = '".$matrix."' WHERE codUser = ".$user.";";
		mysql_query($tmpq);	
		
		$retData[]= "1";  //OK
		echo json_encode($retData); 
		
		mysql_close();
	}	
	
	// ==========================================================================
	// RETRIEVE =================================================================
	// ==========================================================================

	// CONFIGURACAO
	
	if(@$_REQUEST['action']=="retrieveConfig") {  

		conectaDB();	
					
		$sqlQuery = "SELECT lib_segunda, lib_terceira, lib_quarta, lib_quinta, lib_sexta, lib_setima, lib_oitava, lib_nona, evol_ed_primeira, evol_ed_segunda FROM configuracao WHERE cod=0";
		$result=mysql_query($sqlQuery);
		$retData = array();
		
		$row=mysql_fetch_array($result); 
		
		 $retData[]=$row[0];
		 $retData[]=$row[1];
		 $retData[]=$row[2];
		 $retData[]=$row[3];	
		 $retData[]=$row[4];
		 $retData[]=$row[5];
		 $retData[]=$row[6];
		 $retData[]=$row[7];
		 $retData[]=$row[8];
		 $retData[]=$row[9];
		 
		 echo json_encode($retData);
		
     mysql_close();
	}
	
	// MATRIX
	
	if(@$_REQUEST['action']=="retrieveMatrix") {  

		conectaDB();	
		$user=mysql_real_escape_string($_REQUEST['user']);
					
		$sqlQuery = "SELECT codMat, codUser, dateLastAccess, matStr FROM mat WHERE codUser=".$user;
		$result=mysql_query($sqlQuery);
		
		while( $row=mysql_fetch_array($result) )
		{
		 $m=$row['matStr'];
		 echo json_encode($m);
		}
     mysql_close();
	}

	// NEWS
	
	if(@$_REQUEST['action']=="retrieveNews") {  
	
		conectaDB();
		$idUser=mysql_real_escape_string($_REQUEST['user']);
		$idLin=mysql_real_escape_string($_REQUEST['lin']);
		$idCol=mysql_real_escape_string($_REQUEST['col']);
					
		$sqlQuery = "SELECT itemContent , codNews, Lida, Compartilhada FROM newsUsr_".$idUser." WHERE LIN=".$idLin." AND COL=".$idCol;
		
		$result=mysql_query($sqlQuery);
		
		$vetNews[] = array();	
			
        $retData = array();
		
		$i=0;
	    
		while( $row=mysql_fetch_array($result) ){
		  $vetNews[$i]=$row['itemContent'];
		  $retData[]=$vetNews[$i];
		  $retData[]=$row['codNews'];
		  $retData[]=$row['Lida'];
		  $retData[]=$row['Compartilhada'];
		  $i++;
		}
		
		//header("Content-Type: text/xml");
	    echo json_encode($retData);
		//echo $retData;
		mysql_close();
	}
	
	// FEEDs urls
	
	if(@$_REQUEST['action']=="retrieveFeeds") {  //get feeds names from DB...
	
		conectaDB();
		
		$idCat=mysql_real_escape_string($_REQUEST['cat']);
		$idTip=mysql_real_escape_string($_REQUEST['tipo']);
		
			
		$sqlQuery = "SELECT nomeFeed, url FROM feeds WHERE codCat=".$idCat." AND codTip=".$idTip;
		
		$result=mysql_query($sqlQuery);
		
		$vetFeeds[] = array();	
			
        $retData = array();
		
		$i=0;
	    
		while( $row=mysql_fetch_array($result) )
		{

			$vetFeed[$i]=$row['url'];
			$retData[]=$vetFeed[$i];
			$i++;

		}
	    echo json_encode($retData);
		
		mysql_close();		
	}
	
	// ==========================================================================
	// USUARIO ==================================================================
	// ==========================================================================
	
	// GET USER DATA  ...........................................................
	
		if(@$_REQUEST['action']=="getUserData"){
		conectaDB();
		$user=mysql_real_escape_string($_REQUEST['user']);
		$campo=mysql_real_escape_string($_REQUEST['campo']);
		
		$retData = array();
		if(mysql_num_rows(mysql_query("SELECT codUser FROM usuarios WHERE codUser = '$user'"))){
		  //usuario ja esta cadastrado
		   //$resp= mysql_query("SELECT '$campo' FROM usuarios WHERE codUser = '$user'");
		   $tmpq="SELECT ".$campo." FROM usuarios WHERE codUser = ".$user.";";
		   $resp= mysql_query($tmpq);
		   $row=mysql_fetch_array($resp);		
		   $retData[]= "1"; //OK
		   $retData[]= $row[0];   
           echo json_encode($retData);
		} 
		else {  //usuario nao existe
		   $retData[]= "0";  //erro
		   echo json_encode($retData); //retorna codUser para engine		   
		}
		mysql_close();
	}
		// SET USER DATA  ...........................................................
	
		if(@$_REQUEST['action']=="setUserData"){
		conectaDB();
		$user=mysql_real_escape_string($_REQUEST['user']);
		$campo=mysql_real_escape_string($_REQUEST['campo']);
		$valor=mysql_real_escape_string($_REQUEST['valor']);
		
		$retData = array();
		if(mysql_num_rows(mysql_query("SELECT codUser FROM usuarios WHERE codUser = '$user'"))){
		  //usuario ja esta cadastrado
		   $tmpq = "UPDATE usuarios SET ".$campo." = ".$valor." WHERE codUser = ".$user.";";
		   mysql_query($tmpq);
		   $retData[]= "1"; //OK
           echo json_encode($retData);
		} 
		else {  //usuario nao existe
		   $retData[]= "0";  //erro
		   echo json_encode($retData); 		   
		}
		mysql_close();
	}
	// ==========================================================================
	// NEWS    ==================================================================
	// ==========================================================================
	
	// GET NEWS DATA  ...........................................................
	
		if(@$_REQUEST['action']=="getNewsData"){
		conectaDB();
		$user=mysql_real_escape_string($_REQUEST['user']);
		$codnews=mysql_real_escape_string($_REQUEST['codnews']);
		$campo=mysql_real_escape_string($_REQUEST['campo']);
		
		$retData = array();
		if(mysql_num_rows(mysql_query("SELECT codNews FROM newsUsr_$user WHERE codNews = '$codnews'"))){
		  //esta news existe
		   $tmpq="SELECT ".$campo." FROM newsUsr_".$user." WHERE codNews = ".$codnews.";";
		   //$resp= mysql_query("SELECT '$campo' FROM newsUsr_$user WHERE codNews = '$codnews'");
		   $resp= mysql_query($tmpq);
		   $row=mysql_fetch_array($resp);		

		   $retData[]= "1"; //OK
		   $retData[]= $row[0];  
           echo json_encode($retData);
		} 
		else {  //esta news nao existe
		   $retData[]= "0";  //erro
		   echo json_encode($retData); //retorna codUser para engine		   
		}
		mysql_close();
	}
		// SET NEWS DATA  ...........................................................
	
		if(@$_REQUEST['action']=="setNewsData"){
		conectaDB();
		$user=mysql_real_escape_string($_REQUEST['user']);
		$codnews=mysql_real_escape_string($_REQUEST['codnews']);		
		$campo=mysql_real_escape_string($_REQUEST['campo']);
		$valor=mysql_real_escape_string($_REQUEST['valor']);
		
		$retData = array();
		if(mysql_num_rows(mysql_query("SELECT codNews FROM newsUsr_$user WHERE codNews = '$codnews'"))){
		  //esta news existe
		   $tmpq = "UPDATE newsUsr_".$user." SET ".$campo." = ".$valor." WHERE codNews = ".$codnews.";";
		   mysql_query($tmpq);
		   $retData[]= "1"; //OK
           echo json_encode($retData);
		} 
		else {  //esta news nao existe
		   $retData[]= "0";  //erro
		   echo json_encode($retData); 		   
		}
		mysql_close();
	}	
?> 