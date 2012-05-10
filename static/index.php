<?php

function routeHandler($route, $app){
	switch($route){
		case "":
			echo "DEFAAAAAAAAULT! :)";
			break;
		case "something":
			echo "SOMETHIIIIIING!! :D";
			break;
		case "something/ekstra":
			echo "<pre>SOMETHIIIIIING SPECIAL!!
			
^^^^^^^^^^^^^
|    - -    |
8====O.O====8
|     -     |
\___________/
     | |
</pre>";
			break;
		default:
			echo "FOUR-O-FOUR! PAGE NOT FOUUUUND! :O";
	}
}

$settings = System::loadEndpoint("Settings", "SE", true);

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?= $settings->get('title') ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		<meta name="viewport" content="user-scalable=0, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
		<link rel="stylesheet" href="static/media/css/style.css" />
		<!--[if lt IE 9]><link rel="stylesheet" href="static/media/css/ielt9.css"><![endif]-->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script type="text/javascript">
			var dataLoop = false;
			var rendered = false;
			
			var page = 1;
			var board = {};
			var stops = {};
			var user = {};
			var dept = {};
			var offset = {x: -12, y: -35, z: 0, multix: 3, multiy: 2};
			var topz = 150;
			var dist = [];
			var lastData = {};
			var statcycle = [	{data: {segment: 'list', month: 'total'},name: 'Totalt'},
								{data: {segment: 'list', month: 1}, name: 'Januar'},
								{data: {segment: 'list', month: 2}, name: 'Februar'},
								{data: {segment: 'list', month: 3}, name: 'Mars'},
								{data: {segment: 'list', month: 4}, name: 'April'},
								{data: {segment: 'list', month: 5}, name: 'Mai'},
								{data: {segment: 'list', month: 6}, name: 'Juni'},
								{data: {segment: 'list', month: 7}, name: 'Juli'},
								{data: {segment: 'list', month: 8}, name: 'August'},
								{data: {segment: 'list', month: 9}, name: 'September'},
								{data: {segment: 'list', month: 10}, name: 'Oktober'},
								{data: {segment: 'list', month: 11}, name: 'November'},
								{data: {segment: 'list', month: 12}, name: 'Desember'}
							];
			var scp = (new Date()).getMonth();
			var rl = false;
			var failtimes = 0;
			var failtimermx = 1;
			
			function onFail(){
				if(rl===false){
					$('#msg').slideDown('slow');
					rl = setInterval('up()',1500);
				}
				failtimes++;
				if(failtimes>5){
					clearInterval(rl);
					failtimes = 0;
					failtimermx++;
					rl = setInterval('up()',1500*failtimermx);
				}
			}
			
			function onSuccess(){
				$('#msg').slideUp('slow');
				wrl = rl;
				clearInterval(rl);
				rl = false;
				failtimes = 0;
				failtimermx = 1;
				if(wrl!==false){data(lastData);}
			}
			
			function up(){
				$.ajax({url: 'media/ajax/endpoint.php', data: {segment: "dummy"}, dataType: 'json', error: onFail, complete: function(d, s){ if(s === "parsererror"){ onSuccess(); };}});
			}
			
			function data(a){ // Get data
				if(rl===false){
					lastData = a;
				}
				$.getJSON('media/ajax/endpoint.php', a, function(data){
					onSuccess();
					switch(a.segment){
						case "board":
							board = data;
							renderBoard();
							break;
						case "stops":
							stops = data;
						case "list":
							user = data;
							renderStats();
							break;
						case "dept":
							dept = data;
							break;
					}
				}).error(onFail);
			}
			
			function displayLoop(mode, data){ // 
				switch(mode){
					case "hl-player":
						$('.player').css('opacity','0.3');
						$('.player').css('opacity','0.3'); // IE
						
						$('#'+data.user).css('opacity','0.3');
						break;
					case "all":
						$('.player').css('opacity','1');
						break;
					
				}
			}
			
			function renderStats(){ // Render Sidebar
				$('#list1, #list2').html("");
				var len = user.length;
				var list = 1;
				for(var i=0;i<len;i++){
					list = (i>20)?2:1;
					percent = Math.round(parseFloat(user[i][3])/5)*5;
					percent = (isNaN(percent))?"0":percent;
					percentName = percent+"%";
					percentBar = (percent>125)?125:percent;
					$('#list'+list).append('<li id="player'+user[i][0]+'" class="p'+percentBar+' '+user[i][2]+'"><span><div>'+percentName+'</div><span>'+(i+1)+".</span>"+user[i][1]+'</span><div class="percent"></div><div class="percent percentdouble"><div class="inner"><div>'+percentName+'</div><span>'+(i+1)+".</span>"+user[i][1]+'</div></div></li>');
					if(i==0){
						$('#list2').append('<li id="player'+user[i][0]+'" class="p'+percentBar+' '+user[i][2]+'"><span><div>'+percentName+'</div><span>'+(i+1)+".</span>"+user[i][1]+'</span><div class="percent"></div><div class="percent percentdouble"><div class="inner"><div>'+percentName+'</div><span>'+(i+1)+".</span>"+user[i][1]+'</div></div></li>');
					}
				}
			}	
			
			function renderBoard(){ // Render Board
				dist = [];
				var len = board.length;
				for(i=0;i<len;i++){
					dist[board[i][4]] = (dist[board[i][4]]===undefined)?0:dist[board[i][4]]+1;
					multiplex = offset.multix*dist[board[i][4]];
					multipley = offset.multiy*dist[board[i][4]];
					multipley = (!(multipley%2))?multipley:-(multipley);
					lead = (i==len-1)?"leader":"";
					if(!rendered){
						$('#board #players').append('<div id="er'+board[i][0]+'" class="player '+board[i][10]+' '+board[i][2]+' '+lead+'">'+board[i][2]+'</div>');
					}
					$('#er'+board[i][0]).css('left',(parseFloat(board[i][5])+offset.x+multiplex)+'px').css('top',(parseFloat(board[i][6])+offset.y+multipley)+'px').css('z-index',150-(parseFloat(board[i][7])+offset.z));
				}
				
				rendered = true;
			}
			
			function cycle(){
				data({segment: 'list', month: statcycle[scp].data.month});
				$("#dswitch span").html(statcycle[scp].name);
			}
			
			$(document).ready(function(){
				data({segment: 'board'});
				data({segment: 'list', month: (new Date()).getMonth()+1});
				$("#dswitch span").html(['Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember'][(new Date()).getMonth()]);
				dataLoop = setInterval("data({segment: 'board'})",120000);
				max = (new Date()).getMonth()+1;
				out = false;
				$('#userbtn').click(function(){
					if(out){
						$('#sidebar').animate({right: '-=330px'},250);
						out = false;
					}else{
						$('#sidebar').animate({right: '+=330px'},250);
						out = true;
					}
				});
				
				function page1(){
					$('#page2').removeClass('active');
					$('#page1').addClass('active');
					$('#list2').hide();
					$('#list1').show();
					page = 2;
				}
				
				function page2(){
					$('#page1').removeClass('active');
					$('#page2').addClass('active');
					$('#list1').hide();
					$('#list2').show();
					page = 1;
				}
				
				$('#page1').click(page1);
				$('#page2').click(page2);
				
				$('#players > div').live('mouseover',function(){
					$('.hovering').removeClass('hovering');
					$('#play'+$(this).attr('id')).addClass('hovering');
					ison = $('#play'+$(this).attr('id')).parent().attr('id').substr(-1,1);
					eval('page'+ison+'()');
				});
				$('#players > div').live('mouseout',function(){
					$('.hovering').removeClass('hovering');
					
				});
				$('#prev').click(function(){
					if(rl===false){
						page1();
						scp = (--scp==-1)?max:scp;
						cycle();
					}
				});
				$('#next').click(function(){
					if(rl===false){
						page1();
						scp = (++scp==max+1)?0:scp;
						cycle();
					}
				});
				$('#sidebar ul li').live('mouseover',function(){
					var obj = $('#'+$(this).attr('id').substr(4));
					obj.attr('oldz',obj.css('z-index'));
					obj.css('z-index',1000);
					obj.addClass('hover');
				});
				$('#sidebar ul li').live('mouseout',function(){
					var obj = $('#'+$(this).attr('id').substr(4));
					obj.css('z-index',obj.attr('oldz'));
					obj.removeClass('hover');
				});
			});
		</script>
	</head>
	<body>
	
		<div id="container">
			<div id="board">
				<a id="register" href="/login?p=new">Registrer nye verdier &raquo;</a>
				<div id="msg">Det oppstod en feil ved henting av nye data. Prøver igjen.</div>
				<div id="players"></div>
			</div>
			<div id="sidebar">
				<div id="userbtn"></div>
				<h4 id="dswitch"><div id="prev"></div><span></span><div id="next"></div></h4>
				<ul id="list1" class="list">
				</ul>
				<ul id="list2" class="list">
				</ul>
				<ul id="pages">
					<li id="page1" class="active">1</li>
					<li id="page2">2</li>
				</ul>
			</div>
		</div>
	<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-29772183-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

	</script>
	</body>
</html>
