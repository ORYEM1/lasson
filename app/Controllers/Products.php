<?php
namespace App\Controllers;



class Products extends RestrictedBaseController
{
    private string $controller;


    public function __construct()
    {
        $this->controller=strtolower((new \ReflectionClass($this))->getShortName());
    }
    public function index()
    {
        if(user_has_access($this->controller, __FUNCTION__))
        {
            $table='products';
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
            $vars['title']='Products';
            $vars['page_heading']='Products';


            //data header
            $data_header=array();
            $data_header[]=array("name"=>"ID",'sortable'=>true, 'db_col_name'=>"id");
            $data_header[]=array('name'=>'STOCK CODE' ,'sortable'=>true,'db_col_name'=>'stock_code');
            $data_header[]=array('name'=>'PRODUCT NAME','sortable'=>true,'db_col_name'=>'product_name');
            $data_header[]=array('name'=>'CATEGORY','sortable'=>true,'db_col_name'=>'category');
            $data_header[]=array('name'=>'QUANTITY','sortable'=>false,'db_col_name'=>'quantity');
            $data_header[]=array('name'=>'UNIT PRICE','sortable'=>false,'db_col_name'=>'unit_price');
            $data_header[]=array('name'=>'TOTAL PRICE','sortable'=>false,'db_col_name'=>'total_price');
            $data_header[]=array('name'=>'BRAND','sortable'=>true,'db_col_name'=>'brand');
            $data_header[]=array('name'=>'SIZE','sortable'=>false,'db_col_name'=>'size');
            $data_header[]=array('name'=>'STATUS','sortable'=>true,'db_col_name'=>'status');
            $data_header[]=array('name'=>'DESCRIPTION','sortable'=>false,'db_col_name'=>'description');

            $data_header[] = array('name'=>'', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //data footer
            $data_footer=array();
            $data_footer[]=get_new_link_button(array('url'=>"/products/new_product",'label'=>'New Product','icon'=>'fa fa-plus'));
            $vars['data_footer']=$data_footer;

            //data table
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_products'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

        }else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page', $vars);
    }
    public function view_product($product_id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('product_id','stock_code','stock_type','product_name','quantity','unit_price','total_price','supplier_name','receiver_name','payment_status','stock_date','remarks','status','created_at');
            //$join[]=array('table'=>'users','condition'=>'user_id','join'=>'left');
            $join = array('table' => 'stocks', 'condition' => 'stocks.id = products.product_name', 'type' => 'left');
            //$join[]=array('table'=>'order_item','condition'=>'order_number','join'=>'left');
            $join[]=array('table'=>'payments','condition'=>'payments.payment_method=orders.payment_method','type'=>'left');
            $product_data=$this->base_model->get_data(array('table'=>'products','fields'=>$fields,'join'=>$join,'where'=>array('product_id'=>$product_id)),true);
            if(empty($product_data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['page_heading']='View Product';
                $vars['record']=$product_data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='products/view_product';
                $vars['title']='Product Details';

            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'], $vars);
    }
    public function edit_product($product_id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'products','where'=>array('product_id'=>$product_id)),true);
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
                'product_name'=>'required',
                'supplier_name'=>'required'


            );
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'products','where'=>array('stock_code'=>$this->request->getPost('stock_code'))),true);
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The stock code {$this->request->getPost('stock_code')} is already assigned to another stock.")));
                }
                else if(isset($existing_data[0]['stock_id'])&&$existing_data[0]['product_id']!=$product_id)
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

                $this->base_model->update_data(array('table'=>'products','where'=>array('product_id'=>$product_id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Product updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'products','where'=>array('product_id'=>$product_id)),true);
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
                    // $config['product_id']=array('field_type'=>'text_field','label'=>'Product ID','required'=>'required','value' => $data['product_id'] ?? '');
                    $config['product_name']=array('field_type'=>'text_field','label'=>'Product Name','required'=>'required','value' => $data['product_name'] ?? '');
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
                    $vars['submit_url']= base_url("products/edit_product");
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

    public function new_product()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'message'=>'Access Denied')));
            }
            $validation =\Config\Services::validation();
            $validation_rules=array(
                'product_name'=>'required',
                'unit_price'=>'required',


                );
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {




               $data=array();
               foreach ($_POST as $key=>$value)
               {
                   if(strlen($value)==0)
                   {
                       $data[$key]=NULL;
                   }
                   else
                   {
                       $data[$key]=$value;
                   }
               }

              // $data['image']=$uploadPath.$imageName;
              // $data['user_id']=$_SESSION['user_id']['id'];
              // $data['date_time_created']=date('Y-m-d H:i:s');
               $id=$this->base_model->insert_data('products',$data);
               exit('Product added successfully');

            }
            else
            {
            exit(json_encode(array('status'=>0,'message'=>$validation->listErrors())));
            }
        }


            if(user_has_access($this->controller,__FUNCTION__))
            {
                $config=array();
                $config['product_name']=array('field_type'=>'text_field','label'=>'Product Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['product_name']??'');
                $config['unit_price']=array('field_type'=>'text_field','label'=>'price','required'=>'required','value'=>$_POST['unit_price']??'');
                $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_product_status(),'value'=>$_POST['status']??'');
                $config['size']=array('field_type'=>'text_field','label'=>'size','value'=>$_POST['size']??'');
                //$config['stock']=array('field_type'=>'text_field','label'=>'Stock','required'=>'required','type'=>'number','value'=>$_POST['stock']??'');
                $config['category']=array('field_type'=>'text_field','label'=>'Category','required'=>'required','value'=>$_POST['category']??'');
                $config['brand']=array('field_type'=>'text_field','label'=>'Brand','value'=>$_POST['brand']??'');
                $config['description']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$_POST['description']??'','cols'=>300,'rows'=>3);
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Product';
                $vars['submit_url']= base_url("products/new_product");
                $vars['content_view']='form';
                $vars['title']='New Product';

            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']=' 404 Unauthorized';
            }
            return view($vars['content_view'],$vars);

    }


    public function add_to_order($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);

            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit("You don't  have permission to reset password");

            }
            $validation=\Config\Services::validation();
            $validation_rules=array(
                'product_name'=>'required',
                'category'=>'required',
                'quantity'=>'required',
                'unit_price'=>'required',
                );
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                if($this->request->getPost('product_name')!=$this->request->getPost('product_name'))
                {
                    exit(json_encode(array('status'=>0,'message'=>"Please enter product name")));
                }

                $product_data=$this->base_model->get_data(array('table'=>'products','where'=>array('id'=>$id)),true);
                if(!$product_data)
                {
                    exit (json_encode(array('status' => 0, 'message' => "Product not found"), JSON_UNESCAPED_UNICODE));
                }

                $available_quantity=(float)$product_data['quantity'];
                $requested_quantity=(float)$this->request->getPost('quantity');

                if($requested_quantity<=0)
                {
                    exit(json_encode(array('status' => 0, 'message' => "Please enter valid quantity")));
                }

                if($requested_quantity>$available_quantity)
                {
                    exit(json_encode(array('status' => 0, 'message' => "Please enter quantity lower than your requested quantity")));
                }

                $quantity = (float)$this->request->getPost('quantity');
                $unit_price = (float)$this->request->getPost('unit_price');
                $total_price = $quantity * $unit_price;
                $existing_product=$this->base_model->get_data(array('table'=>'order_items','where'=>array('product_name'=>$this->request->getPost('product_name'))),true);
                if (!empty($existing_product)) {
                    $new_quantity = $existing_product['quantity']+ $quantity;
                    $new_total_price = $new_quantity * $unit_price;

                    $this->base_model->update_data(array(
                        'table' => 'order_items',
                        'where' => array('product_name' => $this->request->getPost('product_name')),
                        'data' => array(
                            'quantity' => $new_quantity,
                            'total_price' => $new_total_price
                        )
                    ));
                } else {


                    $order_data = [
                        'product_name' => $this->request->getPost('product_name'),
                        'category' => $this->request->getPost('category'),
                        'quantity' => $quantity,
                        'unit_price' => $this->request->getPost('unit_price'),
                        'total_price' => $total_price,

                    ];
                    $this->base_model->insert_data('order_items', $order_data);
                }
                $db=\Config\Database::connect();
                $builder = $db->table('order_items');
                $builder->selectSum('total_price');
                $query = $builder->get();
                $result = $query->getRow();
                $total_order_price = $result->total_price ?? 0;

                $new_quantity = $available_quantity - $requested_quantity;

                if ($new_quantity <= 0) {
                    $this->base_model->delete_data('products', array(
                        'where' => array('id' => $id)
                    ));
                }
                else{
                $this->base_model->update_data([
                    'table' => 'products',
                    'where' => ['id' => $id],
                    'data' => ['quantity' => $new_quantity]
                ]);

            }
                exit(json_encode(array(
                    'status' => 1,
                    'message' => 'Product added successfully',
                    'total_order_price' => $total_order_price
                )));

            }
            else
            {
                exit(json_encode(array('status'=>0,'message'=>\Config\Services::validation()->listErrors())));
            }
        }
        else
        {
            if(user_has_access($this->controller,__FUNCTION__))
            {
                $query_params=array('table'=>'products','where'=>array('id'=>$id));
                $product_data=$this->base_model->get_data($query_params,true);


                if(empty($product_data))
                {
                    $vars['content_view'] = 'not_found';
                    $vars['title'] = '404 Not Found';
                }

                else
                {

                    $price=$product_data['price']?? 0;
                    $quantity=$_POST['quantity']??0;
                    $total_Price=$quantity*$price;
                    $config=array();

                    $config['product_name']=array('field_type'=>'text_field','label'=>'Product Name','required'=>'required','autofocus'=>'autofocus','readonly'=>'readonly','value'=>$product_data['product_name']??'');
                    $config['category']=array('field_type'=>'text_field','label'=>'Category','required'=>'required','autofocus'=>'autofocus','readonly'=>'readonly','value'=>$product_data['category']??'');
                    $config['quantity']=array('field_type'=>'text_field','label'=>'Quantity','required'=>'required','autofocus'=>'autofocus','value'=>$_POST['quantity']??'');
                    $config['unit_price']=array('field_type'=>'text_field','label'=>'Unit Price','required'=>'required','autofocus'=>'autofocus','readonly'=>'readonly','value'=>$product_data['unit_price']??'');
                    //$config['total_price']=array('field_type'=>'text_field','label'=>'Total Price','required'=>'required','autofocus'=>'autofocus','value'=>'','id'=>'total_price');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Add '.$product_data['product_name'].'To Order Items';
                    $vars['submit_url']=base_url("/products/add_to_order/$id");
                    $vars['content_view']='form1';
                    $vars['title']='Add to Order Items';
                }
            }
            else
            {
                $vars['content_view'] = 'unauthorized';
                $vars['title'] = '401 Unauthorized';
            }
            return view($vars['content_view'], $vars);
        }
    }

    public function delete_product($id = 0)
    {
        if (!user_has_access($this->controller, __FUNCTION__)) {
            exit(json_encode(array('status' => 0, 'msg' => 'You do not have permission to delete orders.')));
        }

        if (empty($id)) {
            exit(json_encode(array('status' => 0, 'msg' => 'Invalid order ID.')));
        }

        $data = $this->base_model->get_data(array(
            'table' => 'products',
            'where' => array('id' => $id)
        ), true);

        if (empty($id)) {
            exit(json_encode(array('status' => 0, 'msg' => 'Product not found.')));
        }

        // Delete the order
        $this->base_model->delete_data('products', array(
            'where' => array('id' => $id)
        ));

        exit(json_encode(array('Product deleted successfully.')));
    }

    public function order_items($id = 0)
    {

        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            {
                if (!user_has_access($this->controller, __FUNCTION__))
                {
                    exit(json_encode(array('status' => 0, 'msg' => 'You do not have permission to add orders.')));
                }
                $validation =\Config\Services::validation();
                $validation_rules=array(
                    'product_name'=>'required',
                    'quantity'=>'required',
                    'unit_price'=>'required',
                );
                $validation->setRules($validation_rules);
                if($validation->withRequest($this->request)->run())
                {
                    $data=array();
                    foreach ($_POST as $key=>$value)
                    {
                        if(strlen($value)==0)
                        {
                            $data[$key]=NULL;
                        }
                        else
                        {
                            $data[$key]=$value;
                        }

                    }
                    if($_POST['unit_price']!==$data['unit_price'])
                    {
                        json_encode(array('status' => 0, 'msg' => 'That price is not valid.'));
                    }
                    if($_POST['quantity']!==$data['quantity'])
                    {
                        json_encode(array('status' => 0, 'msg' => 'That quantity is not valid.'));
                    }
                    $total_price=$data['unit_price']*$data['quantity'];
                        //print_r($total_price);exit;
                    $id=$this->base_model->insert_data('order_items',$data);

                    $db = \Config\Database::connect();
                    $builder = $db->table('order_items');
                    $builder->selectSum('total_price');

                    $query = $builder->get();
                    $result = $query->getRow();
                    $total_order_price = $result->total_price ?? 0;

                    // Return success with total
                    exit(json_encode([
                        'status' => 1,
                        'msg' => 'Product added successfully to Order.',
                        'total_order_price' => number_format($total_order_price, 2)
                    ]));
                    //exit(json_encode(array('status'=>1,'msg'=>'Product added successfully to Order.')));
                }
                else
                {
                    if(user_has_access($this->controller, __FUNCTION__))
                    {
                        $config=array();
                        $config['product_name']=array('field_type'=>'text_field','label'=>'Product Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['product_name']??'');
                        $config['quantity']=array('field_type'=>'text_field','label'=>'Quantity','required'=>'required','value'=>$_POST['quantity']??'');
                        $config['unit_price']=array('field_type'=>'text_field','label'=>'Unit Price','required'=>'required','value'=>$_POST['unit_price']??'');
                        $config['total_price']=array('field_type'=>'text_field','label'=>'Total Price','required'=>'required','value'=>$_POST['total_price']??'');
                        $vars['form_data']=get_form_data($config);
                        $vars['form_title']='New Product';
                        $vars['submit_url']= base_url("products/order_items");
                        $vars['content_view']='form1';
                        $vars['title']='Order';

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
    }

}