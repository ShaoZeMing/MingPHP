<?php
namespace MingPHP\Core;
use MingPHP\PHPMailer\PHPMailer;
class Email
{
	public  function __construct($title,$content,$to)
	{
		$mail = new PHPMailer();
		$mail -> IsSMTP();						//告诉服务器使用smtp协议发送
		$mail -> SMTPAuth = true;				//开启SMTP授权
		$mail -> Host = 'smtp.163.com';			//告诉我们的服务器使用163的smtp服务器发送
		$mail -> From = 'shao19920426@163.com';	//发送者的邮件地址
		$mail -> FromName = '不懂就问';			//发送邮件的用户昵称
		$mail -> Username = 'shao19920426@163.com';		//登录到邮箱的用户名
		$mail -> Password = 'ming1992';		//第三方登录的授权码，在邮箱里面设置
		//编辑发送的邮件内容
		$mail -> IsHTML(true);					//发送的内容使用html编写
		$mail -> CharSet = 'utf-8';				//设置发送内容的编码
		$mail -> Subject = $title;	//设置邮件的主题、标题
		$mail -> MsgHTML($content);				//发送的邮件内容主体
		//告诉服务器接收人的邮件地址
		$mail -> AddAddress($to);
		//调用send方法，执行发送
		$result = $mail -> Send();
		if($result){
			return true;
		}else{
			return $mail -> ErrorInfo;
		}
		
	}
}