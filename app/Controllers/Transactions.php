<?php

namespace App\Controllers;

class Transactions extends RestrictedBaseController
{
    private string $controller;
    public function __construct()
    {
        $this->controller = strtolower((new \ReflectionClass($this))->getShortName());
    }
    public function index()
    {
        if (user_has_access($this->controller, __FUNCTION__)) {
            $table = 'transactions';

            // Reset search sessions
            $_SESSION["search_{$table}"] = [];
            $_SESSION["search_{$table}_where_in"] = [];
            $_SESSION["search_{$table}_like"] = [];

            // Handle search
            if ($this->request->getPost('search')) {
                $params = [];
                $params['table_name'] = $table;
                $params['where'] = $where ?? [];
                $params['where_in'] = $where_in ?? [];
                $params['where_search_fields'] = [
                    'id' => 'id',
                    'transaction_code' => 'transaction_code',
                    'transaction_name' => 'transaction_name',
                    'transaction_number' => 'transaction_number'
                ];

                // Search by transaction_date (optional)
                $search_range = [];
                $search_range['from_date_time'] = ['column' => 'transaction_date', 'operator' => '>='];
                $search_range['to_date_time'] = ['column' => 'transaction_date', 'operator' => '<='];
                $params['search_range'] = $search_range;

                $this->set_search_data($params);
            }

            // Setup data table config
            $vars['content_view'] = 'data_table';
            $vars['title'] = 'Transactions';
            $vars['page_heading'] = 'Transactions';

            // Table Headers
            $data_header = [];
            $data_header[] = ['name' => 'ID', 'sortable' => true, 'db_col_name' => 'id'];
            $data_header[] = ['name' => 'Transaction Code', 'sortable' => true, 'db_col_name' => 'transaction_code'];
            $data_header[] = ['name' => 'Name', 'sortable' => true, 'db_col_name' => 'transaction_name'];
            $data_header[] = ['name' => 'Number', 'sortable' => true, 'db_col_name' => 'transaction_number'];
            $data_header[] = ['name' => 'Payment Method', 'sortable' => true, 'db_col_name' => 'payment_method'];
            $data_header[] = ['name' => 'Status', 'sortable' => true, 'db_col_name' => 'transaction_status'];
            $data_header[] = ['name' => 'Date', 'sortable' => true, 'db_col_name' => 'transaction_date'];
            $data_header[] = ['name' => '', 'class' => 'icon_col', 'sortable' => false]; // Edit
            $data_header[] = ['name' => '', 'class' => 'icon_col', 'sortable' => false]; // Delete
            $data_header[] = [
                'name' => '<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />',
                'class' => 'icon_col',
                'sortable' => false
            ];

            $vars['data_header'] = $data_header;

            // DataTable config
            $dt_params = [
                'ajax' => base_url('data_tables/get_data/get_transactions'),
                'bFilter' => true,
                'order_columns' => ['id' => 'desc']
            ];
            $vars['data_tables_config'] = get_dt_config($data_header, $dt_params);

        } else {
            $vars['content_view'] = 'unauthorized';
            $vars['title'] = '401 Unauthorized';
        }

        return view('page', $vars);
    }

    public function view_transaction($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'transaction_code', 'transacting_number', 'amount', 'payment_method', 'transaction_date', 'transaction_status', 'orders.order_number',  "CONCAT(users.first_name,' ',users.last_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=transactions.created_by','type'=>'left');
            $join[]=array('table'=>'orders','on'=>'orders.id=transactions.order_id','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'transactions','join'=>$join,'fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='transactions/view_transactions';
                $vars['title']='Transactions';
                $vars['page_heading']='Transactions';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_account($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'transactions','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit account')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('transacting_number' =>'required','transaction_code' =>'required','order_id' =>'required','transaction_status' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'transactions','where'=>array('transacting_number'=>$this->request->getPost('transacting_number'),'order_id'=>$this->request->getPost('order_id'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The account number {$this->request->getPost('account_number')} is already added for this library")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The account number {$this->request->getPost('account_number')} is already added for this library")));
                }
                $data=array();
                foreach($_POST as $key=>$value)
                {
                    if(empty($value)&&$value!=0)
                    {
                        $data[$key]=null;
                    }
                    else
                    {
                        if(is_array($value))
                        {
                            $data[$key]=implode(',',$value);
                        }
                        else
                        {
                            $data[$key]=$value;
                        }
                    }
                }

                $this->base_model->update_data(array('table'=>'transactions','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Account updated successfully")));
            }
            else
            {
                exit(json_encode(array('status'=>0,'msg'=>$validation->listErrors())));
            }

        }
        else
        {
            if(user_has_access($this->controller,__FUNCTION__))
            {
                $data=$this->base_model->get_data(array('table'=>'transactions','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['transaction_code']=array('field_type'=>'text_field','label'=>'Transaction Code','type'=>'text','value'=>$data['transaction_code']);
                    $config['transaction_number']=array('field_type'=>'text_field','label'=>'Account Number','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['account_number']??'');
                    $config['account_name']=array('field_type'=>'text_field','label'=>'Account Name','type'=>'text','required'=>'required','value'=>$data['account_name']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'order'=>'readable_name'),'id','readable_name');
                    $config['library_id']=array('field_type'=>'select_field','label'=>'Library','options'=>$options,'required'=>'required','value'=>$data['library_id']??'');
                    $config['purpose']=array('field_type'=>'checklist','label'=>'Purpose','options'=>get_transaction_types(),'required'=>'required','value'=>$data['purpose']??'');
                    $config['status']=array('field_type'=>'select_field','label'=>'Account Status','options'=>get_statuses_array(),'required'=>'required','value'=>$data['status']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Account';
                    $vars['submit_url']= base_url("transaction_accounts/edit_account/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit Account';
                }
            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']='401 Unauthorized';
            }
            return view($vars['content_view'],$vars);
        }
    }

    public function new_transaction()
    {
        if ($this->request->getPost('submit')) {
            // Check permission first
            if (!user_has_access($this->controller, __FUNCTION__)) {
                exit(json_encode(['status' => 0, 'message' => 'You are not allowed to create transactions']));
            }

            $validation = \Config\Services::validation();
            $rules = [
                'transaction_code'   => 'required',
                'transaction_number' => 'required',
                'order_code'         => 'required'
            ];
            $validation->setRules($rules);

            if (!$validation->withRequest($this->request)->run()) {
                exit(json_encode(['status' => 0, 'message' => $validation->getErrors()]));
            }

            $order_code = $this->request->getPost('order_code');

            // Get the order details
            $order = $this->base_model->get_data([
                'table' => 'orders',
                'where' => ['order_code' => $order_code]
            ], true);

            if (!$order) {
                exit(json_encode(['status' => 0, 'message' => 'No order found for this code']));
            }

            // Get order status and use it for transaction_status
            $order_status = $order['order_status']; // assumes column exists in 'orders' table

            $transactionData = [
                'transaction_code'   => $this->request->getPost('transaction_code'),
                'transaction_number' => $this->request->getPost('transaction_number'),
                'transaction_name'   => $this->request->getPost('transaction_name'),
                'transaction_date'   => $this->request->getPost('transaction_date'),
                'transaction_status' => $order_status, // set from order
                'payment_method'     => $this->request->getPost('payment_method'),
                'order_code'         => $order_code // optional, for traceability
            ];

            $inserted = $this->base_model->insert_data('transactions', $transactionData);

            if ($inserted) {
                exit(json_encode(['status' => 1, 'message' => 'Transaction created successfully']));
            } else {
                exit(json_encode(['status' => 0, 'message' => 'Unable to create transaction']));
            }
        } else {
            $transaction_code = 'TRX' . strtoupper(uniqid());
            $product = $this->base_model->get_data([
                'table' => 'products',
            ]);
            return view('transactions/new_transaction_form', [
                'transaction_code'   => $transaction_code,
                'product'            => $product,
                'payment_statuses'   => get_payment_status(),
            ]);
        }
    }



}