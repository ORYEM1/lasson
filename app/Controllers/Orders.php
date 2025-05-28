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
            $data_header[]=array('name'=>'Order Code','sortable'=>false,'db_col_name'=>'order_code');
            $data_header[]=array('name'=>'CUSTOMER NAME','sortable'=>true,'db_col_name'=>'customer_name');
            //$data_header[]=array('name'=>'CUSTOMER EMAIL','sortable'=>false,'db_col_name'=>'customer_email');
            $data_header[]=array('name'=>'CUSTOMER PHONE','sortable'=>true,'db_col_name'=>'customer_phone');
            $data_header[]=array('name'=>'ORDER STATUS','sortable'=>false,'db_col_name'=>'order_status');
            $data_header[]=array('name'=>'DELIVERY ADDRESS','sortable'=>false,'db_col_name'=>'delivery_address');
            $data_header[]=array('name'=>'DELIVERY DATE','sortable'=>true,'db_col_name'=>'delivery_date');
            $data_header[]=array('name'=>'PAYMENT STATUS','sortable'=>true,'db_col_name'=>'payment_status');
            $data_header[]=array('name'=>'ORDERED ON','sortable'=>true,'db_col_name'=>'ordered_on');
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />', 'class' => 'icon_col', 'sortable' => false);
            $vars['data_header']=$data_header;


            //data footer
            $data_footer[]=get_new_link_button(array('url'=>"/orders/new_order",'label'=>'Add New Order','icon'=>'fa fa-plus'));
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

   public function view_order_items($order_id=null)
   {
       if(!user_has_access($this->controller,__FUNCTION__))
       {
           exit('You are not allowed to access this page');
       }
       if(!$order_id)
       {
           exit('Order id not provided');
       }

       $db = \Config\Database::connect();
       $order = $db->table('orders')->where('id',$order_id)->get()->getRowArray();
       if(!$order)
       {
           exit('Order not found');
       }
       $order_items = $db->table('order_items')
       ->where('id',$order_id)
       ->get()->getResultArray();

       $builder = $db->table('order_items');
       $builder ->selectSum('total_price');
       $builder->selectSum('quantity');
       $builder->where('order_code',$order['order_code']);
       $totals = $builder->get()->getRowArray();

       $vars = [
           'page_heading' => 'Order Items for Customer: ' . $order['customer_name'],
           'record' => $order_items,
           'total_price_sum' => $totals->total_price ?? 0,
           'total_quantity_sum' => $totals->quantity ?? 0,
           'username' => $_SESSION['user_data']['first_name'],
           'current_time' => date('Y-m-d H:i:s'),
           'title' => 'Order Items',
           'content_view' => 'orders/view_all_order_items',
           'order' => $order
       ];
       return view('page', $vars);


   }

    public function edit_order($order_id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'orders','where'=>array('order_id'=>$order_id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit order')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array(
                'order_code' =>'required',
                'customer_name' =>'required',
                'customer_phone'=>'required',
                'status'=>'required',
                'product_name'=>'required',
                'ordered_on'=>'required',

                );
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'orders','where'=>array('order_code'=>$this->request->getPost('order_code'))),true);
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The Order Code {$this->request->getPost('order_code')} is already assigned to another wallet.")));
                }
                else if(isset($existing_data[0]['order_id'])&&$existing_data[0]['order_id']!=$order_id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The  order code {$this->request->getPost('order_code')} is already assigned to another order")));
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
                $product_data=array();
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

                $this->base_model->update_data(array('table'=>'orders','where'=>array('order_id'=>$order_id),'data'=>$data),true);
                $this->base_model->update_data(array('table'=>'order_items','where'=>['order_code'=>$order_id,],'product_data'=>$product_data),true);
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
                $data=$this->base_model->get_data(array('table'=>'orders','where'=>array('order_id'=>$order_id)),true);
                $product_data=$this->base_model->get_data(array('table'=>'order_items','where'=>array('order_id'=>$order_id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['order_code']=array('field_type'=>'text_field','label'=>'Order Code','type'=>'text','autofocus'=>'autofocus','required'=>'required','readonly'=>'readonly','value'=>$data['order_code']);
                    $config['customer_name']=array('field_type'=>'text_field','label'=>'Customer Name','required'=>'required','value' => $data['customer_name'] ?? '');
                    $config['customer_phone']=array('field_type'=>'text_field','label'=>'Phone Number','required'=>'required','value' => $data['customer_phone'] ?? '');
                    $config['product_name']=array('field_type'=>'text_field','label'=>'Product','required'=>'required','value' => $product_data['product_name'] ?? '');
                    $config['quantity']=array('field_type'=>'text_field','type'=>'number','label'=>'Quantity','value'=>$product_data['quantity']??'');
                    $config['total_price']=array('field_type'=>'text_field','label'=>'total_amount','required'=>'required','value'=>$product_data['total_price']??'');
                    $config['order_status']=array('field_type'=>'select_field','label'=>'Order Status','required'=>'required','options'=>get_order_status(),'value'=>$data['order_status']??'');

                    $config['ordered_on']=array('field_type'=>'text_field','label'=>'Ordered On','type'=>'date','value'=>$data['ordered_on']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Order';
                    $vars['submit_url']= base_url("orders/edit_order/{$data['order_id']}")&&("order_items/edit_order/{$product_data['order_id']}");
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

    public function new_orders($load_type='')
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
                    if(!in_array($key,['product','quantity','total_amount']))
                    $data[$key] = strlen($value) == 0 ? NULL : $value;
                }
                $order_id = $this->base_model->insert_data('orders', $data);
                $products = $_POST['product'];
                $quantities = $_POST['quantity'];
                $amounts = $_POST['total_amount'];

                foreach($products as $index=>$product)
                {
                    $item=[
                        'order_id' => $order_id,
                        'product_name' => $product,
                        'quantity' => $quantities[$index],
                        'total_amount' => $amounts[$index],
                    ];

                    $this->base_model->insert_data('order_items',$item);
                }

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
                $config['order_code']=array('field_type'=>'text_field','label'=>'Order Code','type'=>'text','autofocus'=>'autofocus','required'=>'required','readonly'=>'readonly','value'=>$order_code);
                $config['customer_name']=array('field_type'=>'text_field','label'=>'Customer Name','required'=>'required','value' => $user_data['customer_name'] ?? '');
               // $config['customer_email']=array('field_type'=>'text_field','label'=>'Customer Email','value' => $user_data['customer_email'] ?? '');
                $config['customer_phone']=array('field_type'=>'text_field','label'=>'Phone Number','required'=>'required','value' => $user_data['phone_number'] ?? '');
                $options=$this->base_model->get_form_options(array('table'=>'products','fields'=>array('product_name'),'product_name'=>'product_name'),'product_name','product_name');
                $config['delivery_address']=array('field_type'=>'select_field','label'=>'Delivery Address','options'=>get_delivery_address(),'value'=>$user_data['delivery_address']??'');
                $config['delivery_date']=array('field_type'=>'text_field','label'=>'Delivery Date','type'=>'date','value'=>$_POST['delivery_date']??'');
                $config['order_status']=array('field_type'=>'select_field','label'=>'Order Status','required'=>'required','options'=>get_order_status(),'value'=>$data['order_status']??'');
               // $config['payment_method']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$_POST['description']??'','cols'=>300,'rows'=>3);
                $options=$this->base_model->get_form_options(array('table'=>'payments','fields'=>array('payment_method'),'payment_method'=>'payment_method'),'payment_method','payment_method');
                $config['payment_method']=array('field_type'=>'select_field','label'=>'Payment Method','required'=>'required','options'=>get_payment_method(),'value'=>$user_data['payment_method']??'');
               // $config['comment']=array('field_type'=>'textarea','label'=>'Comment','value'=>$_POST['comment']??'');

                $config['ordered_on']=array('field_type'=>'text_field','label'=>'Ordered On','type'=>'date','value'=>$_POST['ordered_on']??'');
                //$vars['form_data']=get_form_data($config);
               // $vars['form_title']='New Order';
                $vars['submit_url']= base_url("orders/new_order");
                $vars['content_view']='view_order_items';
                $vars['title']='New Order';

            }
            else
            {
                $vars['products'] = $this->base_model->get_data('products');
                $vars['content_view']='unauthorized';
                $vars['title']=' 404 Unauthorized';
            }
            return view('order_item/view_order_items',$vars);
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
            exit( 'Order not found.');
        }

        // Delete the order
        $this->base_model->delete_data('orders', array(
            'where' => array('order_id' => $order_id)
        ));

        exit('Order deleted successfully.');
    }

    public function cancel_order($order_id = null)
    {
        if (!user_has_access($this->controller, __FUNCTION__))
        {
            exit('You do not have permission to delete orders.');
        }
        if (empty($order_id))
        {
            exit(json_encode(array('status' => 0, 'msg' => 'Invalid order ID.')));
        }
        $order = $this->base_model->get_data(array(
            'table' => 'orders',
            'where' => array('order_id' => $order_id)
        ),true);
        if (empty($order))
        {
            exit(json_encode(array('status' => 0, 'msg' => 'Order not found.')));
        }
        $updated = $this->base_model->cancel_data('orders', array(
            'where' => array('order_id' => $order_id)
        ));
        if($updated)
        {
            exit( 'Order cancelled successfully.');
        }else{
            exit('Order cancelled.');
        }

    }

    public function new_order()
    {
        if ($this->request->getPost('submit')) {
            // Check access
            if (!user_has_access($this->controller, __FUNCTION__)) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Access Denied']);
            }

            // Validation
            $validation = \Config\Services::validation();
            $rules = [
                'order_code' => 'required',
                'customer_name' => 'required',
                'customer_phone' => 'required',
                'order_status' => 'required',
            ];
            $validation->setRules($rules);

            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'status' => 0,
                    'message' => $validation->listErrors()
                ]);
            }

            // Insert into orders table
            $orderData = [
                'order_code' => $this->request->getPost('order_code'),
                'customer_name' => $this->request->getPost('customer_name'),
                'customer_phone' => $this->request->getPost('customer_phone'),
                'order_date' => $this->request->getPost('order_date'),
                'order_status' => $this->request->getPost('order_status'),
                'payment_status' => $this->request->getPost('payment_status'),
                'ordered_on' => $this->request->getPost('ordered_on'),
            ];

            $order_id = $this->base_model->insert_data('orders', $orderData);

            if ($order_id) {
                // Create corresponding transaction automatically
                $transactionData = [
                    'transaction_code' => 'TRX' . strtoupper(uniqid()),
                    'transaction_number' => rand(10000000, 99999999),
                    'transaction_name' => 'Order Payment for ' . $order_code,
                    'transaction_date' => date('Y-m-d H:i:s'),
                    'transaction_status' => $orderData['order_status'],
                    'payment_method' => 'cash', // or from form if needed
                    'order_code' => $order_code, // assuming you have this column
                ];

                $this->base_model->insert_data('transactions', $transactionData);


                if ($order_id) {
                    // Get arrays of product data
                    $product_names = $this->request->getPost('product_name') ?? [];
                    $categories = $this->request->getPost('category') ?? [];
                    $quantities = $this->request->getPost('quantity') ?? [];
                    $unit_prices = $this->request->getPost('unit_price') ?? [];

                    foreach ($product_names as $index => $product_name) {
                        $category = $categories[$index] ?? '';
                        $quantity = (float)($quantities[$index] ?? 0);
                        $unit_price = (float)($unit_prices[$index] ?? 0);
                        $total_price = $quantity * $unit_price;

                        // Check if product exists
                        $existing_product = $this->base_model->get_data([
                            'table' => 'products',
                            'where' => ['product_name' => $product_name]
                        ], true);

                        if (!empty($existing_product)) {
                            $new_quantity = $existing_product['quantity'] + $quantity;
                            $new_total_price = $new_quantity * $unit_price;

                            $this->base_model->update_data([
                                'table' => 'products',
                                'where' => ['product_name' => $product_name],
                                'data' => [
                                    'quantity' => $new_quantity,
                                    'total_price' => $new_total_price
                                ]
                            ]);
                        } else {
                            // Insert new product
                            $product_data = [
                                'order_id' => $order_id,
                                'product_name' => $product_name,
                                'category' => $category,
                                'quantity' => $quantity,
                                'unit_price' => $unit_price,
                                'total_price' => $total_price
                            ];
                            $this->base_model->insert_data('products', $product_data);
                        }

                        // Insert into order_items table
                        $order_item_data = [
                            'order_id' => $order_id,
                            'product_name' => $product_name,
                            'category' => $category,
                            'quantity' => $quantity,
                            'unit_price' => $unit_price,
                            'total_price' => $total_price
                        ];

                        $this->base_model->insert_data('order_items', $order_item_data);
                    }

                    return $this->response->setJSON(['status' => 1, 'message' => 'Order placed successfully']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'message' => 'Failed to place order']);
                }
            } else {
                // Load form
                $order_code = 'ORD' . strtoupper(uniqid());
                $product = $this->base_model->get_data(['table' => 'products']);

                return view('orders/new_order_form', [
                    'order_code' => $order_code,
                    'products' => $product,
                    'order_status' => get_order_status(),
                    'payment_statuses' => get_payment_status(),
                ]);
            }
        }
    }

    public  function view_order($order_id)
    {

        if(!user_has_access($this->controller, __FUNCTION__))
        {
            exit(json_encode(array('status' => 0, 'message' => 'Access Denied')));
        }
        $order = $this->base_model->get_data([
            'table'=>'orders',
            'where'=>['order_id'=>$order_id]
        ],true);
        if(empty($order))
        {
            return $this->response->setJSON([
                'status'=>0,
                'message'=>'Order not found'
            ]);


        }
        $order_items = $this->base_model->get_data([
            'table'=>'order_items',
            'where'=>['order_id'=>$order_id]
        ]);


        $total_quantity_sum = 0;
        $total_price_sum = 0;
        foreach ($order_items as $item) {
            $total_quantity_sum += (float)$item['quantity'];
            $total_price_sum += (float)$item['total_price'];
        }

        $data['user_id'] = $_SESSION['user_data']['id'];
        $username = $_SESSION['user_data']['first_name']; // Get the logged-in user's name
        $current_time = date('Y-m-d H:i:s');

        $vars['username'] = $username; // Pass it to the view
        $vars['current_time'] = $current_time;

        // Current date and time



        return view('orders/view_single_order', [
            'order' => $order,
            'username'=>$username,
            'current_time'=>$current_time,
            'order_items' => $order_items,
            'total_quantity_sum' => $total_quantity_sum,
            'total_price_sum' => $total_price_sum,
            'page_heading' => 'ORDER DETAILS',



        ]);
    }


}