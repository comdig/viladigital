
//::::::::::::::::::::::::::::::::::::::::::::::::::::
// funcoes de acesso as tabelas Usuario e newsUsr ::::
//::::::::::::::::::::::::::::::::::::::::::::::::::::

function getUserData(codU, nomeCampo){
	var parms="&user="+codU+"&campo="+nomeCampo;
	var urlBase = "bdaccess.php?action=getUserData"+parms;  	
	
	var retorna;
	
	  $.ajax({
	   url: urlBase,
	   async:false,
	   success: function(d) { if(d[0]==0) retorna= -1;
								else retorna= d[1];
							}	
	});
  return retorna;
}
function setUserData(codU, nomeCampo, valorCampo){
	var parms="&user="+codU+"&campo="+nomeCampo+"&valor="+valorCampo;
	var urlBase = "bdaccess.php?action=setUserData"+parms;  	
	
	var retorna;
	
	  $.ajax({
	   url: urlBase,
	   async:false,
	   success: function(d) { retorna = d[0]; }	
	});
  return retorna;
}



function getNewsData(codU, codN, nomeCampo){
	var parms="&user="+codU+"&codnews="+codN+"&campo="+nomeCampo;
	var urlBase = "bdaccess.php?action=getNewsData"+parms;  	
	
	var retorna;
	
	  $.ajax({
	   url: urlBase,
	   async:false,
	   success: function(d) { 
								if(d[0]==0) retorna = -1;
								else retorna = d[1];
							}	
	});
	
	return retorna;
}
function setNewsData(codU, codN, nomeCampo, valorCampo){
	var parms="&user="+codU+"&codnews="+codN+"&campo="+nomeCampo+"&valor="+valorCampo;
	var urlBase = "bdaccess.php?action=setNewsData"+parms;  	
	
	var retorna;
	
	  $.ajax({
	   url: urlBase,
	   async:false,
	   success: function(d) { retorna= d[0]; }	
	});
	
	return retorna;
}
