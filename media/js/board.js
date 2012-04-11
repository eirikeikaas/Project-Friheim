var race = function(){
	function api(){}
	
	api.cH = function(m, a, c){
		return function(){
			$.getJSON({url: '/media/ajax/endpoint.php', data: a, success: function(data){
				
			}})
		}
	}
	
	api.prototype.draw = function(data){
		
		if(data.points !== undefined && data.points.length > 0){
			var ca = document.getElementById("board");
			var co = ca.getContext("2d");
			
			co.beginPath();
			co.moveTo(data.points[0].x, data.points[0].y);
			
			for(var i=1;i<data.points.length;i++){
				co.bezierCurevTo(data.points[i].c[0].x, data.points[i].c[0].y, data.points[i].c[1].x, data.points[i].c[1].y, data.points[i].x, data.points[i].y);
			}
			
			co.lineWidth = 5;
			co.strokeStyle = "white";
			co.stroke();
		}
	}
	
	return {
		api: new api()
	}
}();

{ points: {
	{x: 35, y: 200, c: {
					{x: 12, y: 56},
					{x: 72, y: 90}
				}
		}
	},
	{x: 35, y: 200, c: {
					{x: 12, y: 56},
					{x: 72, y: 90}
				}
		}
	},
}

{ users: {initials: "TK", step: 11}}