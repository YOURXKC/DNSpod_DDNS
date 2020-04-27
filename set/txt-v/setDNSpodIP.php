<?php
	
	/** 
	*DNSpod API 解析记录修改（txt版）
	* 
	*该脚本只能实现单一功能，即修改指定解析记录
	*使用该脚本前需使用getDNSpodID.php脚本来获取domain_id、record_id
	* 
	* @link https://www.dnspod.cn/docs/index.html
	* @author      Mr_Xiong
	* @version     1.0 20200428
	*/ 
	
	$object = new setDNSpodIP();
	
	//DNSpod DDNS设置类
	class setDNSpodIP
	{
		/**
		*以下列出的为必填参数
		*
		* @var string $log_path               日志文件导出路径
		* @var string $ip_txt_path            历史IP本地存放路径
		* @var string $userAgent              DNSpod接口调用程序验证
		* @var string $datas["login_token"]   DNSpos账户验证Token
		* @var string $datas["domain_id"]	  域名ID
		* @var string $datas["record_id"]	  域名解析记录ID
		*/
		//日志文件导出路径
		private $log_path = '/ddns/set_DNSpod_log.txt';
		//IP存放路径
    	private $ip_txt_path = "/ddns/ip.txt";

		//DNSpod post提交参数
		//组合形式： 程序名称 /版本号 (邮箱); 如：Local ip update /1.0.1 (1023077@189.com)
		private $userAgent = 'Local ip update /1.0.1 (1023077@189.com)';
		//参数
		private $datas = [
                            'login_token' => '123456,efehfuiewhfuiwe34765984378679th', //账号Token
                            'lang' => 'cn', //返回语言
                            'format' => 'json', //返回类型
                            'domain_id' => '83174345', //域名ID
                            'record_id' => '545039461', //记录ID
                            'sub_domain' => 'www', //主机记录
                            'record_type' => 'A', //记录类型
                            'record_line' => '默认', //解析线路
                            'value' => '0.0.0.0', //记录值
                            'ttl' => 600 //解析超时时间
                         ];


		/**  
		* 构造方法，程序从这里开始和结束 
		* 
		* @access public 
		*/  
		public function __construct()
		{
			//1.获取公网IP
			$new_ip = $this->getPublicIp();

			//2.判断IP是否更换
			$ip_data = $this->ifSaveIp($new_ip);
			if($ip_data["bool"])
			{//不更换
				//打印日志（注意：命令行下导出日志路径必须为绝对路径）
				file_put_contents($this->log_path, '('.date('Y-m-d H:i:s',time()).')——[IP已存在]: 新->'.$new_ip.',旧->'.$ip_data["old_ip"]."\n", FILE_APPEND);
				/*****结束程序*****/
				exit;
			}

			//3.更新DNSpod解析记录（并判断是否更新成功）
			$dns_bool = $this->setDNSpod($new_ip);
			if($dns_bool["state"])
			{//成功
				//打印日志（注意：命令行下导出日志路径必须为绝对路径）
				file_put_contents($this->log_path, '('.date('Y-m-d H:i:s',time()).')——[更新DNSpod解析记录成功]: 新->'.$new_ip.',旧->'.$ip_data["old_ip"]."返回码：".$dns_bool["code"]."返回提示：".$dns_bool["message"]."\n", FILE_APPEND);
			}
			else
			{
				//打印日志（注意：命令行下导出日志路径必须为绝对路径）
				file_put_contents($this->log_path, '('.date('Y-m-d H:i:s',time()).')——[更新DNSpod解析记录失败]: 新->'.$new_ip.',旧->'.$ip_data["old_ip"]."返回码：".$dns_bool["code"]."返回提示：".$dns_bool["message"]."\n", FILE_APPEND);
				/*****结束程序*****/
				exit;
			}

			//4.更新本地IP记录(程序结束)
			$up_bool = $this->setDbIp($new_ip);
			if($up_bool)
			{
				//打印日志（注意：命令行下导出日志路径必须为绝对路径）
				file_put_contents($this->log_path, '('.date('Y-m-d H:i:s',time()).')——[更新本地IP记录成功]: 新->'.$new_ip.',旧->'.$ip_data["old_ip"]."\n", FILE_APPEND);
			}
			else
			{
				//打印日志（注意：命令行下导出日志路径必须为绝对路径）
				file_put_contents($this->log_path, '('.date('Y-m-d H:i:s',time()).')——[更新本地IP记录失败]: 新->'.$new_ip.',旧->'.$ip_data["old_ip"]."\n", FILE_APPEND);
			}

		}

		/**  
		* 获取宽带公网IP 
		* 
		* @access private 
		* @return String
		*/  
		private function getPublicIp() 
		{
			//curl方式调用第三方获取公网IP
			$ch = curl_init('http://tool.huixiang360.com/zhanzhang/ipaddress.php');
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        $a  = curl_exec($ch);
	        preg_match('/\[(.*)\]/', $a, $ip);
	        //返回形式 192.168.1.1
	        return $ip[1];
	 	}

	 	/**  
		* 判断IP是否已经更换 
		* 
		* @access private 
		* @global string $ip_txt_path
		* @param string $new_ip 当前公网IP，字符串类型 
		* @return array
		*/ 
	 	private function ifSaveIp($new_ip)
	 	{
	 		//只读方式打开文件
			@$fp = fopen($this->ip_txt_path,'r');//打开文件@忽略一切警告错误
			if($fp)
			{//当文件存在时，才读取内容
				$line = fgets($fp);//返回一行文本，并将文件指针移动到下一行头部
				fclose($fp);//关闭文件
				if($line == $new_ip)
				{
					return ["bool"=>true, "old_ip"=>$line];
				}
				return ["bool"=>false, "old_ip"=>$line];
			}
			else
			{//文件不存在，创建文件并写入初始化内容
			    file_put_contents($this->ip_txt_path, "0.0.0.0");
			    return ["bool"=>false, "old_ip"=>"0.0.0.0"];
			}
	 	}

	 	/**  
		* 更新DNSpod解析地址
		* 
		* @access private 
		* @global array $datas
		* @param string $new_ip 当前公网IP，字符串类型 
		* @return array
		*/ 
	 	private function setDNSpod($new_ip)
	 	{
	 		//修改记录地址
	 		$url = 'https://dnsapi.cn/Record.Modify';
	 		//更新post参数中value值(新IP)
	 		$this->datas["value"] = $new_ip;
	 		//执行记录更新
	 		$res = $this->sendPost($url);
	 		//判断更新结果（返回更新结果数组）
	 		if($res["status"]["code"] == 1)
	 		{
	 			return ["state"=>true,"code"=>$res["status"]["code"],"message"=>$res["status"]["message"]];
	 		}
	 		else
	 		{
	 			return ["state"=>false,"code"=>$res["status"]["code"],"message"=>$res["status"]["message"]];
	 		}
	 	}

	 	/**  
		* 更新本地IP记录 
		* 
		* @access private 
		* @global string $ip_txt_path
		* @param string $new_ip 当前公网IP，字符串类型 
		* @return boolean
		*/ 
	 	private function setDbIp($new_ip)
	 	{
 			$bool = file_put_contents($this->ip_txt_path, $new_ip);
 			return $bool;
	 	}

	 	/**  
		* 发送API post请求 
		* 
		* @access private 
		* @global string $userAgent
		* @global array $datas
		* @param string $uri API请求地址，字符串类型 
		* @return array
		*/ 
	 	private function sendPost($uri) 
	 	{
	        $url = $uri;
	        $post_data = $this->datas;
	        $post_data_str = http_build_query($post_data);

	        $ch = curl_init($url);
	        curl_setopt_array($ch, array(
	            CURLOPT_RETURNTRANSFER => 1,
	            CURLOPT_POSTFIELDS => $post_data_str,
	            CURLOPT_TIMEOUT    => 10,
	            CURLOPT_USERAGENT  => $this->userAgent,
	        ));
	        
	        if(defined('CURLOPT_SSL_VERIFYPEER')) {
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        }
	        //接收返回值
	        $result = curl_exec($ch);

	        if($result === false) {
	            throw new Exception_Request(curl_error($ch), curl_errno($ch));
	        }
	        //关闭curl请求
	        curl_close($ch);
	        //解析成数组
	        $result = json_decode($result,true);
	        
	        //异常数据
	        if(empty($result["status"]["code"]) || $result["status"]["code"] != '1') {
	            throw new Exception($result["status"]["message"], $result["status"]["code"]);
	        }
	         
	        return $result;
	    }
	}
?>