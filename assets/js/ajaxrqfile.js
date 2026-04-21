function submitpopupformtoemail()
{
	var form_data = new FormData();
	var popnameid="", popemailid="", popuptxt_mobileno="", popprogramid="", validornot="no", ln=0;
	
	popnameid = document.getElementById("popnameid").value;
	popemailid = document.getElementById("popemailid").value;
	popuptxt_mobileno = document.getElementById("popuptxt_mobileno").value;
	popprogramid = document.getElementById("popprogramid").value;
	
	ln=popnameid.length;
	if(ln>2)
	{
		document.getElementById("namealertid").style.display = "none";
	}
	else
	{
		document.getElementById("namealertid").style.display = "block";
	}
	
	ln=popemailid.length;
	if(ln>5)
	{
		document.getElementById("emailalertid").style.display = "none";	
	}
	else
	{
		document.getElementById("emailalertid").style.display = "block";
	}
	
	ln=popuptxt_mobileno.length;
	if(ln>9)
	{
		document.getElementById("mobilealertid").style.display = "none";
	}
	else
	{
		document.getElementById("mobilealertid").style.display = "block";
	}
	
	ln=popprogramid.length;
	if(ln>2)
	{
		document.getElementById("programalertid").style.display = "none";
	}
	else
	{
		document.getElementById("programalertid").style.display = "block";
	}
	
	
	
	
	ln=popnameid.length;
	if(ln>2)
	{
		ln=popemailid.length;
		if(ln>5)
		{
			ln=popuptxt_mobileno.length;
			if(ln>9)
			{
				ln=popprogramid.length;
				if(ln>2)
				{
					validornot="yes";
				}
				else
				{
				}
			}
			else
			{
			}
		}
		else
		{
		}
	}
	else
	{
	}
	
	
	if(validornot=="yes")
	{
				form_data.append("popnameid", popnameid);
				form_data.append("popemailid", popemailid);
				form_data.append("popuptxt_mobileno", popuptxt_mobileno);
				form_data.append("popprogramid", popprogramid);
				
				$.ajax({
					enctype: 'multipart/form-data',
					url:"api/sendformemailrq.php",
					method:"POST",
					data: form_data,
					contentType: false,
					cache: false,
					processData: false,
					beforeSend:function(){
							 
						document.getElementById("popupformidforsubmit").style.display = "none";
						document.getElementById("thanksofpopupid").style.display = "block";
					},   
					success:function(data)
					{
						//$('#uploaded_image').html(data);
						a=0;
						if(data=="done")
						{
							alert("thanks Successfully");
						}
						else
						{
							alert(data);
						}
								
								//document.getElementById("loadingid").style.display = "none";
								
					}
				});
	}
	else
	{
	}
				
}