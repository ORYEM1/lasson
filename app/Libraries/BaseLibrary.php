<?php
namespace App\Libraries;
class BaseLibrary
{
    protected \App\Models\BaseModel $base_model;
    public function __construct()
    {
        $this->base_model = new \App\Models\BaseModel();
    }
    function multi_curl_request($data, $headers=array(), $params=array())
    {
        //print_r($data); exit;
        // array of curl handles
        $curly = array();
        // data to be returned
        $result = array();
        // multi handle
        $mh = curl_multi_init();

        // loop through $data and create curl handles
        // then add them to the multi-handle
        foreach ($data as $id => $d)
        {
            if(isset($d['url']))
            {
                $url=$d['url'];
            }
            else if(isset($params['url']))
            {
                $url=$params['url'];
            }
            else
            {
                continue;
            }

            if(isset($d['headers']))
            {
                $headers=$d['headers'];
            }

            //print_r($headers); exit;

            $curly[$id] = curl_init();

            curl_setopt($curly[$id], CURLOPT_URL,$url);
            curl_setopt($curly[$id], CURLOPT_HEADER,0);
            curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curly[$id], CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt($curly[$id], CURLOPT_CONNECTTIMEOUT,15);
            curl_setopt($curly[$id],CURLOPT_TIMEOUT,15);
            curl_setopt($curly[$id],CURLOPT_HTTPHEADER,$headers);
            curl_setopt($curly[$id],CURLINFO_HEADER_OUT,true);

            // Using POST
            if(isset($d['post_fields']))
            {
                curl_setopt($curly[$id], CURLOPT_POST, 1);
                curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post_fields']);
            }
            // extra options?
            if (isset($params['options'])&& !empty($params['options']))
            {
                curl_setopt_array($curly[$id], $params['options']);
            }

            curl_multi_add_handle($mh, $curly[$id]);
        }

        // execute the handles
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while($running > 0);


        // get content and remove handles
        foreach($curly as $id => $c)
        {
            $result[$id]['content'] = curl_multi_getcontent($c);
            $result[$id]['info']=curl_getinfo($c);
            curl_multi_remove_handle($mh, $c);
        }

        // all done
        curl_multi_close($mh);

        return $result;
    }
    public function send_ipn($transaction_type,$transaction_ids)
    {
        $time=time();
        $ipn_log=array();
        $transaction_updates=array();
        $posted=0;
        if($transaction_type=='collection')
        {
            $table='collection_requests';
        }
        else if($transaction_type=='disbursement')
        {
            $table='disbursement_requests';
        }
        else
        {
            return("Invalid transaction type");
        }
        if(!is_array($transaction_ids))
        {
            $transaction_ids=array($transaction_ids);
        }
        $fields=array("{$table}.id","{$table}.timestamp","{$table}.sender_account","{$table}.secondary_account","{$table}.app_id","{$table}.initiated_by","{$table}.notify_retry_time","{$table}.recipient_account","{$table}.amount","{$table}.transaction_status","{$table}.wallet_ref","{$table}.ext_app_ref","{$table}.notify_url","{$table}.user_msg","{$table}.comment","{$table}.payer_ref","apps.log_ipn_requests","{$table}.wallet","{$table}.currency",'apps.default_notify_url',"apps.use_callback_gateway",'apps.callback_gateway');
        $join=array(array('apps',"{$table}.app_id=apps.id"));
        $transactions=$this->base_model->get_data(array('table'=>$table,'fields'=>$fields,'join'=>$join,'where_in'=>array("{$table}.id"=>$transaction_ids)));
        //exit($this->base_model->db->lastQuery);
        if(empty($transactions))
        {
            return("No transactions found");
        }
        //Prepare HTTP request data;
        $data=array();
        foreach($transactions as $transaction)
        {
            if($transaction['transaction_status']=='PENDING')
            {
                continue;
            }
            if(empty($transaction['notify_url']))
            {
                if(!empty($transaction['default_notify_url']))
                {
                    $transaction['notify_url']=$transaction['default_notify_url'];
                }
                else
                {
                    continue;
                }
            }
            $request_parameters=array();
            $request_parameters['transaction_type']=$transaction_type;
            $request_parameters['initiated_by']=$transaction['initiated_by'];
            $request_parameters['transaction_status']=$transaction['transaction_status'];
            $request_parameters['status_comment']=$transaction['comment'];
            $request_parameters['user_message']=$transaction['user_msg'];
            $request_parameters['amount']=$transaction['amount'];
            $request_parameters['transaction_time']=$transaction['timestamp'];
            $request_parameters['payer_account']=$transaction['sender_account'];
            if($transaction_type=='collection'&&isset($transaction['secondary_account'])&&strlen($transaction['secondary_account'])>=12)
            {
                $request_parameters['payer_account']=$transaction['secondary_account'];
            }
            $request_parameters['payee_account']=$transaction['recipient_account'];
            $request_parameters['internal_transaction_id']=$transaction['id'];
            $request_parameters['wallet_transaction_id']=$transaction['wallet_ref'];
            $request_parameters['app_transaction_id']=$transaction['ext_app_ref'];
            $request_parameters['payer_ref']=$transaction['payer_ref'];
            $request_parameters['wallet']=$transaction['wallet'];
            $request_parameters['currency']=$transaction['currency'];
            $url=$transaction['notify_url'];
            $headers=array("Content-Type: application/json");
            if($transaction['use_callback_gateway'] && !empty($transaction['callback_gateway']))
            {
                $headers[]="Destination-Url:{$url}";
                $url=$transaction['callback_gateway'];
            }
            $json=json_encode($request_parameters);
            $data[]=array('post_fields'=>$json,'transaction'=>$transaction,'url'=>$url,'headers'=>$headers);
            ++$posted;
        }
        if(empty($data)) return('No Transactions posted');

        $responses=$this->multi_curl_request($data);
        //print_r($responses); exit;
        foreach($responses as $key=>$response)
        {
            $transaction=$data[$key]['transaction'];
            $update=array('id'=>$transaction['id'],'notify_url'=>$transaction['notify_url']);
            $response_data=json_decode($response['content'],true);
            if(isset($response_data['status']))
            {
                $update['notification_status']=1;
                $update['notify_retry_time']=null;
                $transaction_updates[]=$update;
            }
            else if($transaction['initiated_by']=='app')
            {
                $update['notification_status']=3;
                $update['notify_retry_time']=null;
                $transaction_updates[]=$update;
            }
            else
            {
                if($time-strtotime($transaction['timestamp'])>=86400)
                {
                    $update['notification_status']=3;
                    $update['notify_retry_time']=null;
                    $transaction_updates[]=$update;
                }
                else
                {
                    if(is_null($transaction['notify_retry_time']))
                    {
                        $update['notification_status']=2;
                        $update['notify_retry_time']=date('Y-m-d H:i:s',$time+900);
                        $transaction_updates[]=$update;
                    }
                    else
                    {
                        $update['notification_status']=2;
                        $update['notify_retry_time']=date('Y-m-d H:i:s',$time+1800);
                        $transaction_updates[]=$update;
                    }

                }
            }
            if($transaction['log_ipn_requests']==1)
            {
                $ipn_log[]=array('timestamp'=>date('Y-m-d H:i:s'),'function'=>$transaction_type,'request_id'=>$transaction['id'],'app_id'=>$transaction['app_id'],'request'=>$data[$key]['post_fields'],'response'=>$response['content']);
            }
        }
        if(!empty($transaction_updates))
        {
            $this->base_model->update_data_batch($table,$transaction_updates);
        }
        if(!empty($ipn_log))
        {
            $this->base_model->insert_data_batch('ipn_request_log',$ipn_log);
        }
        return("{$posted} transactions posted");
    }
    protected function get_transaction_parameters($library_id)
    {
        $data=$this->base_model->get_data(array('table'=>'transaction_parameters','where'=>array('library_id'=>$library_id),'use_cache'=>true));
        $names=array_column($data,'name');
        return array_combine($names,$data);
    }
    protected function get_library_data($library)
    {
        return $this->base_model->get_data(array('table'=>'wallet_libraries','where'=>array('library'=>$library)),true);
    }
    protected function create_log($params)
    {
        if($params['log_type']=='database')
        {
            $this->base_model->insert_data_batch('transaction_request_log',$params['data']);
        }
        elseif($params['log_type']=='file')
        {
            $str='';
            foreach ($params['data'] as $row)
            {
                $entry="\n-------------------------------------------------------------------------------------------\n";
                foreach ($row as $key => $value)
                {
                    $entry.="{$key}: {$value}\n";
                }
                $str.=$entry;
            }
            $log_file=$params['log_file'].date('Ymd').'.log';
            $file_path=WRITEPATH.'logs/'.$log_file;
            file_put_contents($file_path, $str.PHP_EOL , FILE_APPEND | LOCK_EX);
        }
    }
    public function get_wallet_data_by_prefix($network_prefix)
    {
        $prefix_data=$this->base_model->get_data(array('table'=>'mobile_network_prefixes','where'=>array('prefix'=>$network_prefix),'use_cache'=>true),true);
        if(empty($prefix_data)) return array();
        return $this->base_model->get_data(array('table'=>'wallets','where'=>array('id'=>$prefix_data['wallet_id']),'use_cache'=>true),true);
    }
    public function get_wallet_data($wallet_id)
    {
        return $this->base_model->get_data(array('table'=>'wallets','where'=>array('id'=>$wallet_id),'use_cache'=>true),true);
    }
    public function is_must_log($account_number)
    {
        $logged_number_data=$this->base_model->get_data(array('table'=>'logged_numbers','where'=>array('number'=>$account_number)),true);
        if(!empty($logged_number_data))
        {
            return true;
        }
        return false;
    }
}
?>
