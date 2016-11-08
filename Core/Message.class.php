<?php
namespace MingPHP\Core;
use MingPHP\Core\MSM\REST;
class Message
{
	//主帐号,对应开官网发者主账号下的 ACCOUNT SID
	private $accountSid= '8aaf070856fa00960156feb020c7061b';
	
	//主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
	private $accountToken= '64c5bd53b1724a77b11b91465f02bfb7';
	
	//应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
	//在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
	private $appId='8aaf070856fa00960156feb0210b061f';
	
	//请求地址
	//沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
	//生产环境（用户应用上线使用）：app.cloopen.com
	private $serverIP='app.cloopen.com';
	
	
	//请求端口，生产环境和沙盒环境一致
	private $serverPort='8883';
	
	//REST版本号，在官网文档REST介绍中获得。
	private $softVersion='2013-12-26';
	
	/**
	  * 发送模板短信
	  * @param to 手机号码集合,用英文逗号分开
	  * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
	  * @param $tempId 模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID
	  */       
	public function sendTemplateSMS($to,$datas,$tempId=1)
	{
	     // 初始化REST SDK
	     
	     $rest = new REST($this->serverIP,$this->serverPort,$this->softVersion);
	     $rest->setAccount($this->accountSid,$this->accountToken);
	     $rest->setAppId($this->appId);
	    
	     // 发送模板短信
	     //echo "Sending TemplateSMS to $to <br/>";
	     $result = $rest->sendTemplateSMS($to,$datas,$tempId);
	     if($result == NULL ) {
	         //echo "result error!";
//	         break;
	     }
	     if($result->statusCode!=0) {
	         echo "error code :" . $result->statusCode . "<br>";
	         echo "error msg :" . $result->statusMsg . "<br>";
	         //TODO 添加错误处理逻辑
	         return false;
	     }else{
	         //echo "Sendind TemplateSMS success!<br/>";
	         // 获取返回信息
	         $smsmessage = $result->TemplateSMS;
	        // echo "dateCreated:".$smsmessage->dateCreated."<br/>";
	        // echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
	         //TODO 添加成功处理逻辑
	         return true;
	     }
	}
}