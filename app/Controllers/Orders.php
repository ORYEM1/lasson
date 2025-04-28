<?php
namespace App\Controllers;

class Orders extends RestrictedBaseController
{
    private string $controller;
    public function __construct()
    {
        $this->controller=((new \ReflectionClass($this))->getShortName());
    }
    public function index($status=null)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
           $table='orders';
           $_SESSION["search_{$table}"]=array();
           $_SESSION["search_{$table}_where_in"]=array();
           $_SESSION["search_{$table}_like"]=array();

           if(!empty($status))
           {
               $where['order_status']=ucfirst(strtolower($status));
           }
           if($this->request->getPost('search'))
           {
               $params=array();
               $params['table_name']=$table;
               $params['where']=$where??array();
               $params['where_in']=$where_in??array();
               $params['where_search_fields']=$where_search_fields??array('order_id','customer_name','customer_email','phone_number','product_id','order_number','payment_method','payment_status','quantity','total_amount','order_status','delivery_address','delivery_date','ordered_on');
               $this->set_search_data($params);

           }
           $vars['content_view']='data_table';
           $vars['title']='Orders';
           $vars['page_heading']='Orders';

           //data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'order_id');
            $data_header[]=array('name'=>'Order Number','sortable'=>false,'db_col_name'=>'order_number');
            $data_header[]=array('name'=>'CUSTOMER NAME','sortable'=>true,'db_col_name'=>'customer_name');
            $data_header[]=array('name'=>'CUSTOMER EMAIL','sortable'=>false,'db_col_name'=>'customer_email');
            $data_header[]=array('name'=>'CUSTOMER PHONE','sortable'=>true,'db_col_name'=>'phone_number');
            $data_header[]=array('name'=>'PRODUCT','sortable'=>true,'db_col_name'=>'product');
            $data_header[]=array('name'=>'QUANTITY','sortable'=>false,'db_col_name'=>'quantity');
            $data_header[]=array('name'=>'TOTAL AMOUNT','sortable'=>false,'db_col_name'=>'total_amount');
            $data_header[]=array('name'=>'ORDER STATUS','sortable'=>false,'db_col_name'=>'order_status');
            $data_header[]=array('name'=>'DELIVERY ADDRESS','sortable'=>false,'db_col_name'=>'delivery_address');
            $data_header[]=array('name'=>'DELIVERY DATE','sortable'=>true,'db_col_name'=>'delivery_date');
            $data_header[]=array('name'=>'PAYMENT METHOD','sortable'=>true,'db_col_name'=>'payment_method');
            $data_header[]=array('name'=>'PAYMENT STATUS','sortable'=>true,'db_col_name'=>'payment_status');
            $data_header[]=array('name'=>'ORDERED ON','sortable'=>true,'db_col_name'=>'ordered_on');
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />', 'class' => 'icon_col', 'sortable' => false);
            //$data_header[]=array('name'=>'Comment','sortable'=>false,'db_col_name'=>'comment');
            $vars['data_header']=$data_header;


            //data footer
            $data_footer[]=get_new_link_button(array('url'=>"/orders/new_order",'label'=>'Add New Order','icon'=>'fa fa-plus'));
           // $data_footer[]=get_new_link_button(array('url'=>"/orders/edit_order",'label'=>'Edit Order','icon'=>'fa fa-edit'));
            $data_footer[]=get_new_link_button(array('url'=>"/orders/cancel_order",'label'=>'Cancel Order','icon'=>'fa fa-cancel'));
            $vars['data_footer']=$data_footer;

            //data tables
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_orders'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }

        return view('page', $vars);
    }

    public function view_order($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('order_id','product','order_number','customer_name','customer_email','phone_number','order_status','payment_method','order_status','payment_status','quantity','total_amount','delivery_address','delivery_date','ordered_on');
            //$join[]=array('table'=>'users','condition'=>'user_id','join'=>'left');
            $join[]=array('table'=>'products','condition'=>'products.id=orders.product','type'=>'left');
            //$join[]=array('table'=>'order_item','condition'=>'order_number','join'=>'left');
            $join[]=array('table'=>'payments','condition'=>'payments.payment_method=orders.payment_method','type'=>'left');
            $order_data=$this->base_model->get_data(array('table'=>'orders','fields'=>$fields,'join'=>$join,'where'=>array('order_id'=>$id)),true);
            if(empty($order_data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['page_heading']='View Order';
                $vars['record']=$order_data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='orders/view_order';
                $vars['title']='Order Details';

            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'], $vars);
    }
    public function edit_order($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'orders','where'=>array('order_id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit order')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array(
                'order_number' =>'required',
                'customer_name' =>'required',
                'phone_number'=>'required',
                'status'=>'required',
                'product'=>'required',
                'payment_method'=>'required',
                'ordered'=>'required',

                );
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'orders','where'=>array('order_number'=>$this->request->getPost('order_number'))),true);
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The Order Number {$this->request->getPost('order_number')} is already assigned to another wallet.")));
                }
                else if(isset($existing_data[0]['order_id'])&&$existing_data[0]['order_id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The  order number {$this->request->getPost('order_number')} is already assigned to another order")));
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

                $this->base_model->update_data(array('table'=>'orders','where'=>array('order_id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Order updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'orders','where'=>array('order_id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();

                   // $order_code = 'ORD' . strtoupper(uniqid());
                    $config['order_number']=array('field_type'=>'text_field','label'=>'Order Code','type'=>'text','autofocus'=>'autofocus','required'=>'required','readonly'=>'readonly','value'=>$data['order_number']);
                    $config['customer_name']=array('field_type'=>'text_field','label'=>'Customer Name','required'=>'required','value' => $data['customer_name'] ?? '');
                    $config['customer_email']=array('field_type'=>'text_field','label'=>'Customer Email','value' => $data['customer_email'] ?? '');
                    $config['phone_number']=array('field_type'=>'text_field','label'=>'Phone Number','required'=>'required','value' => $data['phone_number'] ?? '');
                    //$options=$this->base_model->get_form_options(array('table'=>'products','fields'=>array('product_name'),'product_name'=>'product_name'),'product_name','product_name');
                    $config['product']=array('field_type'=>'text_field','label'=>'Product','required'=>'required','value'=>$data['product']??'');
                    $config['quantity']=array('field_type'=>'text_field','type'=>'number','label'=>'Quantity','value'=>$data['quantity']??'');
                    $config['total_amount']=array('field_type'=>'text_field','label'=>'total_amount','required'=>'required','value'=>$data['total_amount']??'');
                    $config['delivery_address']=array('field_type'=>'select_field','label'=>'Payment Method','options'=>get_delivery_address(),'value'=>$data['delivery_address']??'');
                    $config['delivery_date']=array('field_type'=>'text_field','label'=>'Delivery Date','type'=>'date','value'=>$data['delivery_date']??'');
                    $config['order_status']=array('field_type'=>'select_field','label'=>'Order Status','required'=>'required','options'=>get_order_status(),'value'=>$data['order_status']??'');
                    // $config['payment_method']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$_POST['description']??'','cols'=>300,'rows'=>3);
                    $options=$this->base_model->get_form_options(array('table'=>'payments','fields'=>array('payment_method'),'payment_method'=>'payment_method'),'payment_method','payment_method');
                    $config['payment_method']=array('field_type'=>'select_field','label'=>'Payment Method','required'=>'required','options'=>get_payment_method(),'value'=>$data['payment_method']??'');
                    // $config['comment']=array('field_type'=>'textarea','label'=>'Comment','value'=>$_POST['comment']??'');

                    $config['ordered_on']=array('field_type'=>'text_field','label'=>'Ordered On','type'=>'date','value'=>$data['ordered_on']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Order';
                    $vars['submit_url']= base_url("orders/edit_order");
                    $vars['content_view']='form';
                    $vars['title']='Edit Order';

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


    public function new_order($load_type='')
    {
        if ($this->request->getPost('submit')) {

            unset($_POST['submit']);

            if (!user_has_access($this->controller, __FUNCTION__)) {
                exit(json_encode(['status' => 0, 'message' => 'Access Denied']));
            }
            $validation = \Config\Services::validation();
            $validation_rules = [
                'order_number'    => 'required',
                'customer_name'   => 'required',
                'phone_number'    => 'required',
                'payment_method'  => 'required',
            ];
            $validation->setRules($validation_rules);

            if ($validation->withRequest($this->request)->run()) {

                $data = [];
                foreach ($_POST as $key => $value) {
                    $data[$key] = strlen($value) == 0 ? NULL : $value;
                }
                $id = $this->base_model->insert_data('orders', $data);

                exit(json_encode(['status' => 1, 'msg' => 'Order placed successfully']));
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

                $order_code = 'ORD' . strtoupper(uniqid());
                $config['order_number']=array('field_type'=>'text_field','label'=>'Order Code','type'=>'text','autofocus'=>'autofocus','required'=>'required','readonly'=>'readonly','value'=>$order_code);
                $config['customer_name']=array('field_type'=>'text_field','label'=>'Customer Name','required'=>'required','value' => $user_data['customer_name'] ?? '');
                $config['customer_email']=array('field_type'=>'text_field','label'=>'Customer Email','value' => $user_data['customer_email'] ?? '');
                $config['phone_number']=array('field_type'=>'text_field','label'=>'Phone Number','required'=>'required','value' => $user_data['phone_number'] ?? '');
                $options=$this->base_model->get_form_options(array('table'=>'products','fields'=>array('product_name'),'product_name'=>'product_name'),'product_name','product_name');
                $config['product']=array('field_type'=>'select_field','label'=>'Product','options'=>$options,'required'=>'required','value'=>$data['product']??'');
                $config['quantity']=array('field_type'=>'text_field','type'=>'number','label'=>'Quantity','value'=>$_POST['quantity']??'');
                $config['total_amount']=array('field_type'=>'text_field','label'=>'total_amount','required'=>'required','value'=>$_POST['total_amount']??'');
                $config['delivery_address']=array('field_type'=>'select_field','label'=>'Delivery Address','options'=>get_delivery_address(),'value'=>$user_data['delivery_address']??'');
                $config['delivery_date']=array('field_type'=>'text_field','label'=>'Delivery Date','type'=>'date','value'=>$_POST['delivery_date']??'');
                $config['order_status']=array('field_type'=>'select_field','label'=>'Order Status','required'=>'required','options'=>get_order_status(),'value'=>$data['order_status']??'');
               // $config['payment_method']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$_POST['description']??'','cols'=>300,'rows'=>3);
                $options=$this->base_model->get_form_options(array('table'=>'payments','fields'=>array('payment_method'),'payment_method'=>'payment_method'),'payment_method','payment_method');
                $config['payment_method']=array('field_type'=>'select_field','label'=>'Payment Method','required'=>'required','options'=>get_payment_method(),'value'=>$user_data['payment_method']??'');
               // $config['comment']=array('field_type'=>'textarea','label'=>'Comment','value'=>$_POST['comment']??'');

                $config['ordered_on']=array('field_type'=>'text_field','label'=>'Ordered On','type'=>'date','value'=>$_POST['ordered_on']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Order';
                $vars['submit_url']= base_url("orders/new_order");
                $vars['content_view']='form';
                $vars['title']='New Order';

            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']=' 404 Unauthorized';
            }
            return view($vars['content_view'],$vars);
        }
    }

    public function delete_order($order_id = 0)
    {
        if (!user_has_access($this->controller, __FUNCTION__)) {
            exit(json_encode(array('status' => 0, 'msg' => 'You do not have permission to delete orders.')));
        }

        if (empty($order_id)) {
            exit(json_encode(array('status' => 0, 'msg' => 'Invalid order ID.')));
        }

        $order = $this->base_model->get_data(array(
            'table' => 'orders',
            'where' => array('order_id' => $order_id)
        ), true);

        if (empty($order)) {
            exit(json_encode(array('status' => 0, 'msg' => 'Order not found.')));
        }

        // Delete the order
        $this->base_model->delete_data('orders', array(
            'where' => array('order_id' => $order_id)
        ));

        exit(json_encode(array('status' => 1, 'msg' => 'Order deleted successfully.')));
    }


}