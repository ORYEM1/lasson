<?php
namespace App\Controllers;

class Order_items extends RestrictedBaseController
{
    private string $controller;
    public function __construct()
    {
        $this->controller=((new \ReflectionClass($this))->getShortName());
    }
    public function index()
    {
      /* if (!user_has_access($this->controller, __FUNCTION__)) {
            return view('unauthorized', ['title' => '401 Unauthorized']);
        }*/


        $db = \Config\Database::connect();
        $builder = $db->table('order_items');

            if(user_has_access($this->controller,__FUNCTION__))
            {
                $table='order_items';
                $_SESSION["search_{$table}"]=array();
                $_SESSION["search_{$table}_where_in"]=array();
                $_SESSION["search_{$table}_like"]=array();

                if($this->request->getPost('search'))
                {
                    $params=array();
                    $params['table_name']=$table;
                    $params['where']=$where??array();
                    $params['where_in']=$where_in??array();
                    $params['where_search_fields']=$where_search_fields??array('product_name');
                    $this->set_search_data($params);

                }

                $total_price_sum = $builder->selectSum('total_price')->get()->getRow()->total_price ?? 0;
                $builder_quantity = $db->table('order_items');
                $total_quantity_sum = $builder_quantity->selectSum('quantity')->get()->getRow()->quantity ?? 0;

                $vars['total_price_sum'] = $total_price_sum;
                $vars['total_quantity_sum'] = $total_quantity_sum;



                $vars['content_view']='data_table';
                $vars['title']='Order Items';
                $vars['page_heading']='Order Items';

                //data header
                $data_header=array();
                $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
                $data_header[]=array('name'=>'PRODUCT NAME','sortable'=>false,'db_col_name'=>'product_name');
                $data_header[]=array('name'=>'CATEGORY','sortable'=>false,'db_col_name'=>'category');
                $data_header[]=array('name'=>'QUANTITY','sortable'=>true,'db_col_name'=>'quantity');
                $data_header[]=array('name'=>'UNIT PRICE','sortable'=>false,'db_col_name'=>'unit_price');
                $data_header[]=array('name'=>'TOTAL AMOUNT','sortable'=>true,'db_col_name'=>'total_price');

                $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
                $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);

                //$data_header[]=array('name'=>'Comment','sortable'=>false,'db_col_name'=>'comment');
                $vars['data_header']=$data_header;


                //data footer
                //$data_footer[]=get_new_link_button(array('url'=>"/order_items/add_product",'label'=>'Add New Product','icon'=>'fa fa-plus'));

                $data_footer[]=get_new_link_button(array('url'=>"/order_items/view_order_items",'style'=>'align_item:right','label'=>'view order','icon'=>'fa fa-add'));
                $vars['data_footer']=$data_footer;

                //data tables
                $dt_params=array('ajax'=>base_url('data_tables/get_data/get_order_items'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
                $vars['data_tables_config']=get_dt_config($data_header,$dt_params);
            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']='401 Unauthorized';
            }

        return view('page', $vars);
    }
    public function view_order_items()
    {
        if(user_has_access($this->controller, __FUNCTION__))
        {


            $fields = array('id', 'product_name', 'category', 'quantity', 'unit_price', 'total_price');
            $order_data = $this->base_model->get_data(array(
                'table' => 'order_items',
                'fields' => $fields
            ));

            // Calculate summary
            $db = \Config\Database::connect();
            //$builder = $db->table('orders');
            $builder = $db->table('order_items');

            $total_price_sum = $builder->selectSum('total_price')->get()->getRow()->total_price ?? 0;
            $total_quantity_sum = $builder->selectSum('quantity')->get()->getRow()->quantity ?? 0;

            $data = [];
            foreach ($_POST as $key => $value) {
                $value = trim($value);
                $data[$key] = strlen($value) === 0 ? null : $value;
            }
            $data['user_id'] = $_SESSION['user_data']['id'];
            $username = $_SESSION['user_data']['first_name']; // Get the logged-in user's name
            $current_time = date('Y-m-d H:i:s');

            $vars['username'] = $username; // Pass it to the view
            $vars['current_time'] = $current_time;



            // Current date and time


            $vars['page_heading'] = 'All Order Items';
            $vars['record'] = $order_data;
            $vars['total_price_sum'] = $total_price_sum;
            $vars['total_quantity_sum'] = $total_quantity_sum;
            $vars['content_view'] = 'order_item/view_all_order_items';
            $vars['title'] = 'Order Items List';
        }
        else
        {
            $vars['content_view'] = 'unauthorized';
            $vars['title'] = '401 Unauthorized';
        }

        return view($vars['content_view'], $vars);
    }
    public function edit_order_item($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'order_items','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit('You do not have permission to edit order_items');
            }

            $validation = \Config\Services::validation();
            $validation_rules=array(
                'product_name' =>'required',
                'quantity' =>'required',
                'unit_price'=>'required',
                'total_price'=>'required',


            );
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
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

                $this->base_model->update_data(array('table'=>'order_items','where'=>array('id'=>$id),'data'=>$data),true);
                exit("Order updated successfully");
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
                $data=$this->base_model->get_data(array('table'=>'order_items','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['product_name']=array('field_type'=>'text_field','label'=>'Order Code','type'=>'text','autofocus'=>'autofocus','required'=>'required','readonly'=>'readonly','value'=>$data['order_number']);
                    $config['quantity']=array('field_type'=>'text_field','label'=>'Customer Name','required'=>'required','value' => $data['customer_name'] ?? '');
                    $config['unit_price']=array('field_type'=>'text_field','label'=>'Customer Email','value' => $data['customer_email'] ?? '');
                    $config['total_price']=array('field_type'=>'text_field','label'=>'Phone Number','required'=>'required','value' => $data['phone_number'] ?? '');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Order_items';
                    $vars['submit_url']= base_url("order_items/edit_order_items/$id");
                    $vars['content_view']='form';
                    $vars['title']='Edit Order Items';

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


    public function delete_order_item($id=0)
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit('You do not have permission to delete order_items');
        }
        if(empty($id))
        {
            exit(json_encode(array('status'=>0,'msg'=>'Order id is required')));
        }
        $order_item = $this->base_model->get_data(array(
            'table' => 'order_items',
            'where' => array('id' => $id)
        ), true);
        if(empty($order_item))
        {
            exit(json_encode(array('status'=>0,'msg'=>'Order id is required')));
        }
        $this->base_model->delete_data('order_items',array(
            'where' => array('id' => $id)
        ));
        exit('Order Item deleted successfully');

    }

}
