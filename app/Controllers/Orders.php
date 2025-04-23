<?php
namespace App\Controllers;

class Orders extends RestrictedBaseController
{
    private string $controller;
    public function __construct()
    {
        $this->controller=((new \ReflectionClass($this))->getShortName());
    }
    public function index()
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
           $table='orders';
           $_SESSION["search_{$table}"]=array();
           $_SESSION["search_{$table}_where_in"]=array();
           $_SESSION["search_{$table}_like"]=array();
           if($this->request->getPost('search'))
           {
               $params=array();
               $params['table_name']=$table;
               $params['where']=$where??array();
               $params['where_in']=$where_in??array();
               $params['where_search_fields']=$where_search_fields??array('id','user_id','product_id','order_number','payment_method','payment_status','quantity','total_price','delivery_address','comment','delivery_date','ordered_on');
               $this->set_search_data($params);

           }
           $vars['content_view']='data_table';
           $vars['title']='Orders';
           $vars['page_heading']='Orders';

           //data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Order Number','sortable'=>true);
            $data_header[]=array('name'=>'CUSTOMER NAME','sortable'=>true,'db_col_name'=>'username');
            $data_header[]=array('name'=>'CUSTOMER EMAIL','sortable'=>false,'db_col_name'=>'email');
            $data_header[]=array('name'=>'CUSTOMER PHONE','sortable'=>true,'db_col_name'=>'phone');
            $data_header[]=array('name'=>'Product','sortable'=>true,'db_col_name'=>'product_name');
            $data_header[]=array('name'=>'Total Price','sortable'=>false,'db_col_name'=>'total_price');
            $data_header[]=array('name'=>'Delivery Address','sortable'=>false,'db_col_name'=>'delivery_address');
            $data_header[]=array('name'=>'Delivery Date','sortable'=>true,'db_col_name'=>'delivery_date');
            $data_header[]=array('name'=>'payment_method','sortable'=>true,'db_col_name'=>'payment_method');
            $data_header[]=array('name'=>'Payment Status','sortable'=>true,'db_col_name'=>'payment_status');
            $data_header[]=array('name'=>'Quantity','sortable'=>false,'db_col_name'=>'quantity');
            $data_header[]=array('name'=>'Ordered On','sortable'=>true,'db_col_name'=>'ordered_on');
            $data_header[]=array('name'=>'Comment','sortable'=>false,'db_col_name'=>'comment');
            $vars['data_header']=$data_header;


            //data footer
            $data_footer[]=get_new_link_button(array('url'=>"/orders/new_order",'label'=>'Add New Order','icon'=>'fa fa-plus'));
            $vars['data_footer']=$data_footer;

            //data tables
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_order'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
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
            $fields=array('id','user_id','product_id','order_number','payment_method','payment_status','quantity','total_price','delivery_address','comment','delivery_date','ordered_on');
            $join[]=array('table'=>'users','condition'=>'user_id','join'=>'left');
            $join[]=array('table'=>'products','condition'=>'product_id','join'=>'left');
            $join[]=array('table'=>'order_item','condition'=>'order_number','join'=>'left');
            $join[]=array('table'=>'payment','condition'=>'payment_id','join'=>'left');
            $order_data=$this->base_model->get_data(array('table'=>'order','fields'=>$fields,'join'=>$join,'where'=>array('order.id'=>$id)),true);
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
}