oldVerdict = "";
total = 0;

$(document).ready(function(){
	old = parseFloat($('#correctionvalue > span').html());
	
	$("#newpswd").keyup(function(){
		res = testPassword($(this).val());
		theclass = ($(this).val().length == 0)?"none":res.attrclass;
		theverdict = ($(this).val().length == 0)?"":res.verdict;
		$("#pswdstrength").html(theverdict).attr("class",theclass);
		oldVerdict = res.attrclass;
	});
	
	$("#refreshbudget").click(function(){
		if(confirm("Er du sikker på at du vil generere månedsbudsjettet på nytt?")){
			$("#budgeteditor").html("");
			
			total = parseFloat($("#total").val().split(' ').join(''));
			month = Math.floor(total/12);
			
			months = ['Januar', 'Februar', 'Mars', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Desember'];
			
			for(var i=0;i<12;i++){
				$("#budgeteditor").append('<div><label for="'+months[i]+'">'+months[i]+'</label><input name="'+months[i]+'" id="'+months[i]+'" type="text" value="'+month+'"><div class="clear"></div></div>');
			}
		}
	});
	
	$("#budgeteditor > div > input").live('keyup',function(){
		collected = 0;
		
		$("#budgeteditor > div > input").each(function(){
			num = parseFloat($(this).val().split(' ').join(''));
			collected += (isNaN(num))?0:num;
		});
		
		total = collected;
		$("#total").val(total);
	});
	
	$('#correction').live('keyup',function(){
		corr = parseFloat($('#correction').val());
		old = (isNaN(old))?0:old;
		corr = (isNaN(corr))?0:corr;
		newval = old+corr;
		$('#correctionvalue > span').html(newval);
	});
	
	$('#historyselect').change(function(){
		var xval = $(this).val().split('_');
		console.log(xval);
		$.ajax('/login/?p=users&a=edit&id='+xval[0]+'&month='+xval[1]+'&getmonth').done(function(data){
			$('#historyshow').html(data);
		});
	});
});