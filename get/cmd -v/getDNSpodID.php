<?php

	$object = new getDNSpodID();

	class getDNSpodID
	{
		//静态变量(修改这两个变量即可)
		//组合形式： 程序名称 /版本号 (邮箱); 如：Local ip update /1.0.1 (1023077@189.com)
		private $userAgent = 'Local ip update /1.0.1 (1023077@189.com)';
		//您的DNSpod login_token,格式：API ID,API Token,如：123456,efehfuiewhfuiwe34765984378679
		private $login_token = '123456,efehfuiewhfuiwe34765984378679th';

		//构造方法
		public function __construct()
		{
			//获取域名列表	
			$domain_list = $this->getDomainID();
			if($domain_list)
			{
				for($i=0; $i<count($domain_list); $i++)
				{
					$record_list = $this->getRecordID($domain_list[$i]["id"]);
					echo "域名ID(domain_id)：" . $domain_list[$i]["id"] . "\n";
					echo "域名名称：" . $domain_list[$i]["name"] . "\n";
					if($record_list <= 0)
					{
						echo "\n" . "该域名无解析记录" . "\n";
					}
					else if($record_list == 1)
					{
						echo "\n" . "记录ID(record_id)：" . $record_list[0]["id"] . "\n";
						echo "域名前缀：" . $record_list[0]["name"] . "\n";
						echo "解析地址：" . $record_list[0]["value"] . "\n";
					}
					else
					{
						for($j=0; $j<count($record_list); $j++)
						{
							echo "\n" . "记录ID(record_id)：" . $record_list[$j]["id"] . "\n";
							echo "域名前缀：" . $record_list[$j]["name"] . "\n";
							echo "解析地址：" . $record_list[$j]["value"] . "\n";
						}
					}

					echo "\n\n\n";
				}
				return;
			}
			else
			{
				echo "域名列表为空";
				return;
			}
		}

		//获取域名ID
		private function getDomainID()
		{
			//域名列表请求地址
			$domain_url = 'https://dnsapi.cn/Domain.List';
			$query_domain = [
								"login_token" => $this->login_token,
								"lang" => 'cn',
								"format" => 'json'
							];
			$res_domain = $this->sendPost($domain_url,$query_domain);
			if(count($res_domain) <= 0)
			{
				return false;
			}

			$domains = $res_domain["domains"];
			for($i=0; $i<count($domains); $i++)
			{
				$domain_id[$i]["id"] = $domains[$i]["id"];
				$domain_id[$i]["name"] = $domains[$i]["name"];
			}

			return $domain_id;
		}

		//获取解析记录ID
		private function getRecordID($domain_id)
		{
			//域名解析记录列表请求地址
			$record_url = 'https://dnsapi.cn/Record.List';
			$query_record = [
								"login_token" => $this->login_token,
								"domain_id" => $domain_id,
								"lang" => 'cn',
								"format" => 'json'
							];
			$res_record = $this->sendPost($record_url,$query_record);
			$records = $res_record["records"];
			for($i=0; $i<count($records); $i++)
			{
				$record_id[$i]["id"] = $records[$i]["id"];
				$record_id[$i]["name"] = $records[$i]["name"];
				$record_id[$i]["value"] = $records[$i]["value"];
			}

			return $record_id;
		}

		//发送post请求（返回值已从json对象解析成数组）
	 	private function sendPost($uri,$datas) 
	 	{
	        $url = $uri;
	        $post_data = $datas;
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