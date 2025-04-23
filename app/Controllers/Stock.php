<?php
namespace App\Controllers;


class Stock extends RestrictedBaseController
{

    private string $controller;
    public function __construct()
    {
        $this->controller = strotolower((new \ReflectionClass($this))->getShortName());
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

            $vars['conten_view']='data_table';
            $vars['title']='Stocks';
            $vars['page_heading']='Stocks';


            //data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'PRODUCT ID','sortable'=>true,'db_col_name'=>'product_id');
            $data_header[]=array('name'=>'QUANTITY','sortable'=>false,'db_col_name'=>'quantity');
            $data_header[]=array('name'=>'UNIT','sortable'=>false,'db_col_name'=>'unit');
            $data_header[]=array('name'=>'TOTAL COST','sortable'=>false,'db_col_name'=>'total_cost');
            $data_header[]=array('name'=>'RECEIVED DATE','sortable'=>false,'db_col_name'=>'received_date');
            $vars['data_header']=$data_header;

            //data footer
            $data_footer=array();
            $data_footer[]=get_new_link_button(array('url'=>"/stock/new_stock",'lable'=>'New stock','icon'=>'fa fa-plus'));
            $vars['data_footer']=$data_footer;

            //data table
            $dt_params=array('ajax'=>base_url('data_table/get_data/get_stock'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

        }else
        {
            $vars['conten_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page', $vars);
    }
}