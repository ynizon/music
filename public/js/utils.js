$(document).ready(function() {
	$.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

	//Toutes les dates de cette classe ont un date picker
	//$(".jqdate").datepicker({"helper":"datePickerup","jQueryParams":{"dateFormat":"yyyy-mm-dd"},"options":[]});

	//Efface le warning
	if (readCookie("first_visit") != '1'){
		$('#warning_first').show();
	}

	//Toggle navigation mobile
	$("#toggleNav").click(function(){
		$('#navbarSupportedContent').toggleClass('show')
	});

	//Button back
	$("#backbtn").click(function(){
		window.history.back();
	});

	addShareBtn();
});

/* Formate les chiffres correctement ex:1000->1 000.00 */
function format(num){
    var n = num.toFixed(2).toString(), p = n.indexOf('.');
    return n.replace(/\d(?=(?:\d{3})+(?:\.|$))/g, function($0, i){
        return p<0 || i<p ? ($0+' ') : $0;
    });
}

function encodeMyUrl(s){
	s = s.replace(/\//g,'');
	return encodeURIComponent(s);
}

function homeToGo(){
	if ($('#q2').val() != ""){
		window.location.href='/artist/'+encodeMyUrl($('#q2').val());
	}
}

/* Formate les chiffres correctement ex:1 000->1000.00 */
function numformat(num){
    return num.toString().replace(' ','');
}

//Gestion des autocompletions
$( document ).ready(function() {
	$("#q2").autocomplete({
		source: function( request, response ) {
		$.ajax({
			url: "/ajax/autocomplete?mode=artist",
			dataType: "json",
			data: {term: request.term},
			success: function(data) {
						response($.map(data, function(item) {
						return {
							label: item.label,
							id: item.id
							};
					}));
				}
			});
		},
		minLength: 2,
		select: function(event, ui) {
			window.location.href='/artist/'+encodeMyUrl($('#q2').val());
		}
	});

	$("#q").autocomplete({
		source: function( request, response ) {
		$.ajax({
			url: "/ajax/autocomplete?mode=artist",
			dataType: "json",
			data: {term: request.term},
			success: function(data) {
						response($.map(data, function(item) {
						return {
							label: item.label,
							id: item.id
							};
					}));
				}
			});
		},
		minLength: 2,
		select: function(event, ui) {
			$("#q").removeClass("ui-autocomplete-loading");
			$("#q").val(ui.item.value);
			searchX();
		}
	});

	$("#album").autocomplete({
		source: function( request, response ) {
		$.ajax({
			url: "/ajax/autocomplete?mode=album",
			dataType: "json",
			data: {term: request.term},
			success: function(data) {
				response($.map(data, function(item) {
						return {
							label: item.label,
							id: item.id
						};
					}));
				}
			});
		},
		minLength: 2,
		select: function(event, ui) {
			$("#album").removeClass("ui-autocomplete-loading");
			$("#album").val(ui.item.value);
			searchX();
		}
	});

	$("#title").autocomplete({
		source: function( request, response ) {
		$.ajax({
			url: "/ajax/autocomplete?q="+$("#q").val()+"&mode=title",
			dataType: "json",
			data: {term: request.term},
			success: function(data) {
				response($.map(data, function(item) {
					return {
						label: item.label,
						id: item.id
						};
					}));
				}
			});
		},
		minLength: 2,
		select: function(event, ui) {
			$("#title").removeClass("ui-autocomplete-loading");
			$("#title").val(ui.item.value);
			searchX();
		}
	});
});


/* Chargement des videos */
var sVideoIdOld = '';
var sVideoTitleOld = '';
function loadVideo(sVideoId, sVideoTitle){
	$("#youtube").show();
	$("#block_youtube").css("display","block");
	$("#pub_amazon").hide();
	$("#youtube").show();
	$("#youtube").width($("#block_youtube").width());

	if (sVideoId.indexOf('?')>-1){
		$('#youtube').attr('src','https://www.youtube.com/embed/'+sVideoId +'&autoplay=1&wmode=transparent');
	}else{
		$('#youtube').attr('src','https://www.youtube.com/embed/'+sVideoId +'?autoplay=1&wmode=transparent');
	}

	//$("#download").attr("href","https://ycapi.org/iframe/?f=mp3&v="+sVideoId);
	$('#download').attr('href','/download?id='+sVideoId+'&name='+ sVideoTitle);
	$('#download').css('display','inline');

	//Check IP for display SONOS
    $.ajax({
        url: "/checkipsonos",
    }).done(function(data) {
        eval(data);
    });

	//Verification des licences, pour relancer la video si besoin
	//checkYoutube(sVideoId, sVideoTitle);

	sVideoIdOld = sVideoId;
    sVideoTitleOld = sVideoTitle;
	updateUrl();

	if ($(window).width()<700){
		//Pour les mobiles, on remonte un peu sur la video
		$('html, body').animate({
			scrollTop: ($("#block_youtube").offset().top-55)
		});
	}
}

/* Gestion de la pagination */
var iDiapotr = 0;
var iDiapotral = 0;
var iDiapotryl = 0;
var iDiapotrya = 0;
function pagination(iStep,sClass){
	$(".diapo"+sClass).hide();
	var iDiapo2 = 0;

	switch (sClass){
		case "tr":
			iDiapo = iDiapotr;
			break;
		case "tral":
			iDiapo = iDiapotral;
			break;
		case "trya":
			iDiapo = iDiapotrya;
			break;
		case "tryl":
			iDiapo = iDiapotryl;
			break;
	}

	iDiapo = iDiapo +iStep;
	if (iDiapo<0){
		iDiapo = 0;
	}
	iDiapo2 = iDiapo +5;

	while (iDiapo<iDiapo2){
		$("#"+sClass+"_"+iDiapo).fadeIn("slow");
		iDiapo++;
	}

	$("#next"+sClass).css("visibility","visible");
	if (!$("#"+sClass+"_"+(iDiapo+1)).length){
		$("#next"+sClass).css("visibility","hidden");
	}

	iDiapo = iDiapo2-5;
	if (iDiapo<0){
		iDiapo = 0;
	}

	$("#prev"+sClass).css("visibility","visible");
	if (iDiapo == 0){
		$("#prev"+sClass).css("visibility","hidden");
	}

	switch (sClass){
		case "tr":
			iDiapotr = iDiapo;
			break;
		case "tral":
			iDiapotral = iDiapo;
			break;
		case "trya":
			iDiapotrya = iDiapo;
			break;
		case "tryl":
			iDiapotryl = iDiapo;
			break;
	}
    loadImages();
}

/* Afiche le diaporama */
var refreshIntervalId = '';
var iLargeurImage = 700;
function diaporama(){
	 $("#allpage").css("display","none");
	 $("body").css("background","#000");
	 $("#flickr").css("display","block");
	 if ($(window).width()<700){
		iLargeurImage = $(window).width();
		$(".diaporamaimg").css("width",iLargeurImage);
		$("#flickr").css("width",iLargeurImage);
	 }else{
		iLargeurImage = 700;
		$(".diaporamaimg").css("width",iLargeurImage);
		$("#flickr").css("width",iLargeurImage);
	 }
	 $("#flickr").css('left',($(window).width()-iLargeurImage)/2);
	 refreshIntervalId = setInterval(function(){
			 $(".slideshow ul").animate({marginLeft:-iLargeurImage},800,function(){
				$(this).css({marginLeft:0}).find("li:last").after($(this).find("li:first"));
			 })
	  }, 3500);

	  $(".slideshow ul").unbind("click");
	  $(".slideshow ul").click(function(){
			clearInterval(refreshIntervalId);
			$("#flickr").css("display","none");
			$("#allpage").css("display","");
			$("body").css("background","#fff");
	  });
}

/* Recherche globale */
function searchX(){
	//Reset pagination
	iDiapotr = 0;
	iDiapotral = 0;
	iDiapotryl = 0;
	iDiapotrya = 0;

	//$(".pagination").css("visibility","hidden");

	/*
	$("#artist_name").html('');
	$("#flickr").html('');
	$("#artistes").html('');
	$("#albums_youtube").html('');
	$("#lives_youtube").html('');

	$(".blockvide").css('visibility','hidden');
	*/

	//Ne pas recharger la liste des albums
	//$("#albums").html('');
	$("#block_albums").css('visibility','visible');


	if ($("#q").val() != ""){
		$("#loader").show();
		updateUrl();
		goAjax();
	}else{
		if ($("#title").val() != "" || $("#album").val() != ""){
			alert("Désolé, mais la recherche par album ou titre requiert l'artiste");
		}
	}
}

function updateUrl(){
	var url = "/artist/"+encodeMyUrl($("#q").val());

	if ($("#q").val() != ""){
		if ($("#title").val() != ""){
			$("#albums_youtube_query").attr("placeholder",$("#q").val() + " " +$("#title").val());
			if ($("#album").val() != ""){
				url = url +"/"+encodeMyUrl($("#album").val());
			}else{
				url = url +"/-";
			}
			url = url +"/"+encodeMyUrl($("#title").val());
		}else{
			if ($("#album").val() != ""){
				url = url +"/"+encodeMyUrl($("#album").val());
			}
		}

		if (sVideoIdOld != ''){
			url = url+"?play="+sVideoIdOld;
            if (sVideoTitleOld != ''){
                url = url+"&title="+sVideoTitleOld;
            }
		}

		window.history.pushState($("#q").val(), $("#q").val(), url);
	}
}

//Affiche l album
function showAlbum(){
	$("#album_detail").show();
	$("#block_albums").hide();
}


//Retour album
function backAlbum(){
	$("#album_detail").hide();
	$("#block_albums").show();
}


/* Lance la recherche */
function goAjax(){
	if ($("#q").val() != ''){
		var url = "/ajax/artist/"+encodeMyUrl($("#q").val());
		if ($("#title").val() != ""){
			if ($("#album").val() != ""){
				url = url +"/"+encodeMyUrl($("#album").val());
			}else{
				url = url +"/-";
			}
			url = url +"/"+encodeMyUrl($("#title").val());
		}else{
			if ($("#album").val() != ""){
				url = url +"/"+encodeMyUrl($("#album").val());
			}
		}

		$.ajax({
		  type: "GET",
		  url: url,
		  success: function(msg){
				//alert(msg);
				eval(msg);
		  }
		});
	}else{
		//alert ("Nom de l'artiste obligatoire");
	}


	//Si au bout de 10 secondes la requete ne s est pas termine, alors on relance
	/*
	setTimeout(function(){
		if ($("#loader").css("display") != 'none'){
			goAjax();
		}
	}, 10000);
	*/
}

/* Verifie si la video est sous licence et envoie donc liframe de deblocage */
function checkYoutube(sVideoId, sVideoTitle){
	var sUrl = "/ajax/checkyoutube";

	$.ajax({
	  type: "GET",
	  url: sUrl,
	  data: "video_id="+sVideoId,
	  success: function(msg){
			//alert(msg);
			if(msg=="true"){
				loadVideo(sVideoId, sVideoTitle);
			}
	  }
	});
}

//Detecter les fleches
document.onkeydown = checkKey;
function checkKey(e) {
	switch (e.keyCode ) {
		case 27:
			//esc
			//Fermer diaporama
			if (refreshIntervalId != ''){
				clearInterval(refreshIntervalId);
			}
			$("#flickr").css("display","none");
			$("#allpage").css("display","");
			$("body").css("background","#fff");
			break;

		case 37:
		   // left arrow
		   if ($("#prevtral").css("visibility") != "hidden"){
				pagination(-5,'tral');
			}
			if ($("#prevtr").css("visibility") != "hidden"){
				pagination(-5,'tr');
			}
			if ($("#prevtryl").css("visibility") != "hidden"){
				pagination(-5,'tryl');
			}
			if ($("#prevtrya").css("visibility") != "hidden"){
				pagination(-5,'trya');
			}
			break;

		case 39:
			// right arrow
			if ($("#nexttral").css("visibility") != "hidden"){
				pagination(5,'tral');
			}
			if ($("#nexttr").css("visibility") != "hidden"){
				pagination(5,'tr');
			}
			if ($("#nexttryl").css("visibility") != "hidden"){
				pagination(5,'tryl');
			}
			if ($("#nexttrya").css("visibility") != "hidden"){
				pagination(5,'trya');
			}
			break;
	}
}

/* Recherche modifiee par le input album/live */
function searchFor(sDivId,oInput){
	var sUrl = "/ajax/keyword";
	$.ajax({
	  type: "POST",
	  url: sUrl,
	  data: "div_id="+sDivId+"&keywords="+oInput.value,
	  success: function(msg){
		eval(msg);
	  }
	});
}

//Recharge les images si marche pas
function imageRefresh(img, timeout) {
    setTimeout(function() {
     var d = new Date;
     var http = img.src;
     if (http.indexOf("&d=") != -1) { http = http.split("&d=")[0]; }

     img.src = http + '&d=' + d.getTime();
    }, timeout);
  }

//Charge les images en differe
function loadImages() {
    var imgs = document.getElementsByTagName('img');
    for (var i = 0; i < imgs.length; i++) {
        if (imgs[i].getAttribute('data-src') && imgs[i].style.display != 'none') {
            imgs[i].setAttribute('src', imgs[i].getAttribute('data-src'));
			imgs[i].removeAttribute('data-src');
        }
    }
}

//Gestion des cookies
function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

function addShareBtn(){
	shareLink("#btnfacebook",'https://www.facebook.com/sharer/sharer.php?u=',encodeURIComponent($("#btn_url").val()),'Venez+voir+'+encodeURIComponent($("#btn_artist").val()));
	shareLink("#btntwitter",'https://twitter.com/share?url=',encodeURIComponent($("#btn_url").val()),'Venez+voir+'+encodeURIComponent($("#btn_artist").val()));
}

function shareLink(oIcon, url_network, url,text){
	if (sVideoIdOld != ''){
		url = url+"?play="+sVideoIdOld;
        if (sVideoTitleOld != ''){
            url = url+"&title="+sVideoTitleOld;
        }
	}
	$(oIcon).attr("href",url_network+url+"&text="+text);
}

function lookForArtist(sArtist){
	$('#title').val('');
	$('#album').val('');
	$('#q').val(sArtist);
	sVideoIdOld='';
    sVideoTitleOld='';
	searchX();
}

function lookForAlbum(sArtist, sAlbum){
	$('#albums_youtube_query').val(sArtist+" " +sAlbum);
	$('#title').val('');
	$('#q').val(sArtist);
	$('#album').val(sAlbum);
	searchX();
}

function getQueryParams(qs) {
    qs = qs.split('+').join(' ');

    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
    }

    return params;
}
