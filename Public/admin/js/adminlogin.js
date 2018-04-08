	var userpwd = "";
	var username = "";
	var code    = "";
	var form = false;
	var err=[
		["errUser","用户名为空或格式错误"],
		["errPwd","密码为空或格式错误"],
		["errCode","验证码为空或超出输入范围"]
	];
  $(document).ready(function(){
    $("#verify").click(function(){
        var verifyURL = "./admin.php?a=Verify";
        $("#verify").attr({"src":verifyURL});
    });
	$("#formsubmit").click(function(){
		if(checkUsename(0,'username') && checkUsePwd(1,'userpwd') && checkCode(2,'code')){
			form = true;
		}else{
			return false;
		}
        if(form == true){
        	SubmitUser();
		}
    });
	$("#bodyUp").keydown(function(e){
		if(e.keyCode==13){
			if(checkUsename(0,'username') && checkUsePwd(1,'userpwd') && checkCode(2,'code')){
				form = true;
			}else{
				return false;
			}
	        if(form == true){
	        	SubmitUser();
			}
		}
	});
  });
  //验证用户名输入格式是否正确
	function checkUsename(i,inputId){
		var patrn=/^[a-zA-Z]{1}([a-zA-Z0-9]|[._]){4,19}$/;
		username	= $("#"+inputId).val();
		if (!patrn.test(username)){
			layer.tips(err[i][1], '#'+inputId,{
				tips:4
			});
			form = false;
			return false;
		}else{
			form = true;
			return true;
		}
		
	}
	//验证密码输入格式是否正确
	function checkUsePwd(i,inputId){
		var patrn=/^(\w){6,20}$/;  
		userpwd	= $("#"+inputId).val();
		if (!patrn.test(userpwd)){
			layer.tips(err[i][1], '#'+inputId,{
				tips:4
			});
			form = false;
			return false;
		}else{
			form = true;
			return true;
		}
		
	}
	//验证验证码是否为空
	function checkCode(i,inputId){
		code	= $("#"+inputId).val();
		if(code == "" || code.length > 4){
			layer.tips(err[i][1], '#'+inputId,{
				tips:4
			});
			form = false;
			return false;
		}else{
			form = true;
			return true;
		}
	}
   //登陆信息的提交
   function SubmitUser(){
	   var index = layer.load(2, {
		   time:500
		 });
	   $.ajax({
		   url:"admin.php?a=CheckLogin",
		   type:"post",
		   data:{"username":username,"userpwd":userpwd,"imgcode":code},
		   success:function(msg){
			   layer.close('loading');
			   if(msg ==-1){
				   layer.msg('验证码输入错误..');
			   }else if(msg ==0){
				   layer.msg('用户名不存在..');
			   }else if(msg == -2){
				   layer.msg('密码输入错误..');
			   }else if(msg == 1){
				   layer.msg('登陆成功..',{time:1000},function(){
					  location.href="./admin.php?c=Admin"; 
				   });//
			   }
		   },
		   error:function(){
			   layer.msg('连接服务器超时..');
		   }
	   });
   }