<?php
namespace App\Controllers;


class Stocks extends RestrictedBaseController
{

    private string $controller;
    public function __construct()
    {
        $this->controller = strtolower((new \ReflectionClass($this))->getShortName());
    }

    public function index()
    {
        if(user_has_access($this->controller, __FUNCTION__))
        {
            $table='stocks';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            if($this->request->getGet('search'))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id');
                $params['where_in_search_fields']=array('id'=>'id');
                $this->set_search_data($params);
            }

            $vars['content_view']='data_table';
            $vars['title']='Stocks';
            $vars['page_heading']='Stocks';


            //data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'stock_id');

            $data_header[]=array('name'=>'STOCK CODE','sortable'=>false,'db_col_name'=>'stock_code');
            $data_header[]=array('name'=>'STOCK TYPE','sortable'=>true,'db_col_name'=>'stock_type');
            $data_header[]=array('name'=>'PRODUCT ID','sortable'=>true,'db_col_name'=>'product_id');
            $data_header[]=array('name'=>'QUANTITY','sortable'=>false,'db_col_name'=>'quantity');
            $data_header[]=array('name'=>'UNIT PRICE','sortable'=>false,'db_col_name'=>'unit_price');
            $data_header[]=array('name'=>'TOTAL COST','sortable'=>false,'db_col_name'=>'total_cost');
            $data_header[]=array('name'=>'SUPPLIER NAME','sortable'=>true,'db_col_name'=>'supplier_name');
            $data_header[]=array('name'=>'RECEIVER NAME','sortable'=>false,'db_col_name'=>'receiver_name');
            //$data_header[]=array('name'=>'REFERENCE NUMBER','sortable'=>false,'db_col_name'=>'reference_number');
            $data_header[]=array('name'=>'STOCK DATE','sortable'=>false,'db_col_name'=>'stock_date');
            $data_header[]=array('name'=>'STATUS','sortable'=>true,'db_col_name'=>'status');
            $data_header[]=array('name'=>'PAYMENT STATUS','sortable'=>false,'db_col_name'=>'payment_status');
            $data_header[]=array('name'=>'ORDERED ON','sortable'=>false,'db_col_name'=>'created_at');
            //$data_header[]=array('name'=>'REMARKS','sortable'=>true,'db_col_name'=>'remarks');
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //data footer
            $data_footer=array();
            $data_footer[]=get_new_link_button(array('url'=>"/stocks/new_stock",'label'=>'New stock','icon'=>'fa fa-plus'));
            $vars['data_footer']=$data_footer;

            //data table
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_stocks'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

        }else
        {
            $vars['conten_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page', $vars);
    }
    public function view_stock($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('stock_id','stock_code','stock_type','product_id','quantity','unit_price','total_price','supplier_name','receiver_name','payment_status','stock_date','remarks','status','created_at');
            //$join[]=array('table'=>'users','condition'=>'user_id','join'=>'left');
            $join = array('table' => 'products', 'condition' => 'products.id = stocks.product_id', 'type' => 'left');
            //$join[]=array('table'=>'order_item','condition'=>'order_number','join'=>'left');
            $join[]=array('table'=>'payments','condition'=>'payments.payment_method=orders.payment_method','type'=>'left');
            $stock_data=$this->base_model->get_data(array('table'=>'stocks','fields'=>$fields,'join'=>$join,'where'=>array('stock_id'=>$id)),true);
            if(empty($order_data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['page_heading']='View Stock';
                $vars['record']=$stock_data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='stocks/view_stock';
                $vars['title']='Stock Details';

            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'], $vars);
    }
    public function edit_stock($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'stocks','where'=>array('stock_id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit stock')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array(
                'stock_code' =>'required',
                'stock_type' =>'required',
                'quantity'=>'required',
                'status'=>'required',
                'product_id'=>'required',
                'supplier_name'=>'required'


            );
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'stocks','where'=>array('stock_code'=>$this->request->getPost('stock_code'))),true);
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The stock code {$this->request->getPost('stock_code')} is already assigned to another stock.")));
                }
                else if(isset($existing_data[0]['stock_id'])&&$existing_data[0]['stock_id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The  stock code {$this->request->getPost('stock_code')} is already assigned to another stock")));
                }


                $data=array();
                foreach($_POST as $key=>$value)
                {
                    if(strlen($value)==0)
                    {
                        $data[$key]=null;
                    }
                    else
                    {
                        $data[$key]=$value;
                    }
                }

                $this->base_model->update_data(array('table'=>'stocks','where'=>array('stock_id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Stock updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'stocks','where'=>array('stock_id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();

                    // $order_code = 'ORD' . strtoupper(uniqid());
                    $config['stock_code']=array('field_type'=>'text_field','label'=>'Stock Code','type'=>'text','autofocus'=>'autofocus','required'=>'required','readonly'=>'readonly','value'=>$data['stock_code']);
                    $config['stock_type']=array('field_type'=>'select_field','label'=>'Stock Type','required'=>'required','options'=>get_stock_type(),'value' => $data['stock_type'] ?? '');
                    $config['product_id']=array('field_type'=>'text_field','label'=>'Product ID','required'=>'required','value' => $data['product_id'] ?? '');
                    $config['quantity']=array('field_type'=>'text_field','label'=>'Quantity','required'=>'required','value' => $data['quantity'] ?? '');
                    //$options=$this->base_model->get_form_options(array('table'=>'products','fields'=>array('product_name'),'product_name'=>'product_name'),'product_name','product_name');
                    $config['unit_price']=array('field_type'=>'text_field','label'=>'Unit Price','required'=>'required','value'=>$data['unit_price']??'');
                    $config['total_price']=array('field_type'=>'text_field','type'=>'Total Price','label'=>'Quantity','value'=>$data['total_price']??'');
                    $config['supplier_name']=array('field_type'=>'text_field','label'=>'Supplier Name','required'=>'required','value'=>$data['supplier_name']??'');
                    $config['receiver_name']=array('field_type'=>'text_field','label'=>'Receiver Name','required'=>'required','value'=>$data['receiver_name']??'');
                    $config['stock_date']=array('field_type'=>'text_field','label'=>'Stock Date','type'=>'date','value'=>$data['stock_date']??'');
                    $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_order_status(),'value'=>$data['status']??'');
                    // $config['payment_method']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$_POST['description']??'','cols'=>300,'rows'=>3);
                    //$options=$this->base_model->get_form_options(array('table'=>'payments','fields'=>array('payment_method'),'payment_method'=>'payment_method'),'payment_method','payment_method');
                    $config['payment_status']=array('field_type'=>'select_field','label'=>'Payment Status','required'=>'required','options'=>get_payment_status(),'value'=>$data['payment_status']??'');
                    // $config['comment']=array('field_type'=>'textarea','label'=>'Comment','value'=>$_POST['comment']??'');
                    //$config['updated_at']=array('field_type'=>'text_field','label'=>'Updated On','type'=>'date','value'=>$data['updated_at']??'');
                    $config['created_at']=array('field_type'=>'text_field','label'=>'Ordered On','type'=>'date','value'=>$data['created_at']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Stock';
                    $vars['submit_url']= base_url("stocks/edit_stock");
                    $vars['content_view']='form';
                    $vars['title']='Edit Stock';

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

    public function new_stock($load_type='')
    {
        if ($this->request->getPost('submit')) {

            unset($_POST['submit']);

            if (!user_has_access($this->controller, __FUNCTION__)) {
                exit(json_encode(['status' => 0, 'message' => 'Access Denied']));
            }
            $validation = \Config\Services::validation();
            $validation_rules = [
                'stock_code' =>'required',
                'stock_type' =>'required',
                'quantity'=>'required',
                'status'=>'required',
                'product_id'=>'required',
                'supplier_name'=>'required'
            ];
            $validation->setRules($validation_rules);

            if ($validation->withRequest($this->request)->run()) {

                $data = [];
                foreach ($_POST as $key => $value) {
                    $data[$key] = strlen($value) == 0 ? NULL : $value;
                }
                $id = $this->base_model->insert_data('stocks', $data);

                exit(json_encode(['status' => 1, 'msg' => 'Stock submitted successfully']));
            } else {
                exit(json_encode(['status' => 0, 'message' => $validation->listErrors()]));
            }

        }
        else
        {
            if(user_has_access($this->controller,__FUNCTION__))
            {

                $user_id = $_SESSION['user_data']['id'];
                $user_data = $this->base_model->get_data('users', array('id' => $user_id));


                //test


                $config=array();

                $stock_code = 'STK' . strtoupper(uniqid());
                $config['stock_code']=array('field_type'=>'text_field','label'=>'Stock Code','type'=>'text','autofocus'=>'autofocus','required'=>'required','readonly'=>'readonly','value'=>$stock_code);
                $config['stock_type']=array('field_type'=>'select_field','label'=>'Stock Type','required'=>'required','options'=>get_stock_type(),'value' => $_POST['stock_type'] ?? '');
                $config['product_id']=array('field_type'=>'text_field','label'=>'Product ID','required'=>'required','value' => $_POST['product_id'] ?? '');
                $config['quantity']=array('field_type'=>'text_field','type'=>'number','label'=>'Quantity','required'=>'required','value' => $_POST['quantity'] ?? '');
                //$options=$this->base_model->get_form_options(array('table'=>'products','fields'=>array('product_name'),'product_name'=>'product_name'),'product_name','product_name');
                $config['unit_price']=array('field_type'=>'text_field','type'=>'number','label'=>'Unit Price','required'=>'required','value'=>$_POST['unit_price']??'');
                $config['total_price']=array('field_type'=>'text_field','type'=>'number','label'=>'Total Price','value'=>$_POST['total_price']??'');
                $config['supplier_name']=array('field_type'=>'text_field','label'=>'Supplier Name','required'=>'required','value'=>$_POST['supplier_name']??'');
                $config['receiver_name']=array('field_type'=>'text_field','label'=>'Receiver Name','required'=>'required','value'=>$_POST['receiver_name']??'');
                $config['stock_date']=array('field_type'=>'text_field','label'=>'Stock Date','type'=>'date','value'=>$_POST['stock_date']??'');

                $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_order_status(),'value'=>$_POST['status']??'');
                // $config['payment_method']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$_POST['description']??'','cols'=>300,'rows'=>3);
                //$options=$this->base_model->get_form_options(array('table'=>'payments','fields'=>array('payment_method'),'payment_method'=>'payment_method'),'payment_method','payment_method');
                $config['payment_status']=array('field_type'=>'select_field','label'=>'Payment Status','required'=>'required','options'=>get_payment_status(),'value'=>$_POST['payment_status']??'');
                // $config['comment']=array('field_type'=>'textarea','label'=>'Comment','value'=>$_POST['comment']??'');
                //$config['updated_at']=array('field_type'=>'text_field','label'=>'Updated On','type'=>'date','value'=>$_POST['updated_at']??'');
                $config['created_at']=array('field_type'=>'text_field','label'=>'Ordered On','type'=>'date','value'=>$_POST['created_at']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Stock';
                $vars['submit_url']= base_url("stocks/new_stock");
                $vars['content_view']='form';
                $vars['title']='New Stock';

            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']=' 404 Unauthorized';
            }
            return view($vars['content_view'],$vars);
        }
    }
}