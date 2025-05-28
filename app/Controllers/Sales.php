<?php
namespace App\Controllers;
class Sales extends RestrictedBaseController
{
    private string $controller;

    public function __construct()
    {
        $this->controller=((new \ReflectionClass($this))->getShortName());

    }

    public function index()
    {
        if(user_has_access($this->controller, __FUNCTION__))
        {
            $table='transactions';
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
            $vars['title']='SALES';
            $vars['page_heading']='SALES';


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
            $data_footer[]=get_new_link_button(array('url'=>"/sales/today",'label'=>'TODAY','icon'=>'fa fa-calendar'));
            $data_footer[]=get_new_link_button(array('url'=>"/sales/month",'label'=>'MONTH','icon'=>'fa fa-calendar'));
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
}