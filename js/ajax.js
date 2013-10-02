
jQuery(document).ready( function($) {

/* ========== Ajax functions for setting admin, block  of users and deletion of devices ==============*/

	$(".admin").click(function(){
			var val= $(this).attr("checked");
			var id=this.id; 
			var admin;
			if(val == 'checked') {admin='yes';} else {admin='no';}
				$("#result").load("php/adminajax.php",{id:id,admin:admin});
				return true;
		}
	);

	$(".blockeduser").click(function(){
			var val= $(this).attr("checked");
			var id=this.id; 
			var blocked; 
			if(val == 'checked') {blocked='yes';} else {blocked='no';}
				$("#result").load("php/blockedajax.php",{id:id,blocked:blocked});
				return true;
		}
	);

	$(".add").live('click',function(){
			var ip=$("#ip").val();
			var port=$("#port").val(); 
			var community=$("#community").val(); 
			var interfaces=$("#interfaces").val()
			$(".tableDiv").load("php/adddeviceajax.php",{ip:ip,port:port,community:community,interfaces:interfaces});
			return false;
		}
		
	);
	
	$(".delete").live('click',function(){
			var id=this.id;
			$(".tableDiv").load("php/removedeviceajax.php",{id:id});
			return false;
		}	
	);
		
});
