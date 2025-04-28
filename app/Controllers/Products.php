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
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $table = 'products';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();

            if($this->request->getPost("search"))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id');
                $params['like']=$like??array();

                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Products';
            $vars['page_heading']='Products';



            //the Header
            $data_header=array();
            $data_header[]=array("name"=>"ID","sortable"=>true,'db_col_name'=>'id');
            $data_header[]=array("name"=>"NAME","sortable"=>true,"db_col_name"=>"product_name");
            $data_header[]=array("name"=>"PRICE","sortable"=>true,"db_col_name"=>"price");
            $data_header[]=array("name"=>"COLOR","sortable"=>false,"db_col_name"=>"color");
            $data_header[]=array("name"=>"SIZE","sortable"=>false,"db_col_name"=>"size");
            $data_header[]=array("name"=>"DESCRIPTION","sortable"=>false,"db_col_name"=>"description");
            $data_header[]=array("name"=>"STATUS","sortable"=>false,"db_col_name"=>"status");
            $data_header[]=array("name"=>"BRAND","sortable"=>true,"db_col_name"=>"brand");
            $data_header[]=array("name"=>"CATEGORY","sortable"=>true,"db_col_name"=>"category_id");
            $data_header[]=array("name"=>"DATE UPLOADED","sortable"=>true,"db_col_name"=>"created_at");
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;


            //data footer
            $data_footer=array();
            $data_footer[]=get_new_link_button(array('url'=>"/products/new_product",'label'=>'New Product','icon'=>'fa fa-plus'));
            $vars['data_footer']=$data_footer;

            //Data tables options

            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_products'),'bFilter'=>true,'order_column'=>array('id','asc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);


        }
        else{
            $vars['content_view']='unauthorized';
            $vars['title']='Unauthorized';
        }
        return view('page', $vars);
    }
    public function view_product($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id','product_name','price','color','size','category','description','brand','status','created_at','created_by');
            //$join[]=array('table'=>'categories','condition'=>'categories_id=products.category_id','type'=>'left');
            $product_data = $this->base_model->get_data(array('table'=>'products','fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($product_data))
            {
                $vars['content_view']='not_found';
                $vars['title']='Not found';
            }
            else
            {


                $vars['record']=$product_data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='products/view_product';
                $vars['title']='Product Details';
                $vars['page_heading']='Product Details';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='404 Unauthorized';
        }
        return view( $vars['content_view'] ,$vars);
    }
    public function edit_product($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'products','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You are not authorized to access this page')));
            }
            $validation=\Config\Services::validation();

            $validation_rules=array('product_name'=>'required','price'=>'required','description'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'products','where'=>array('product_name'=>$this->request->getPost('product_name'))),true);
               /* if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The product name {$this->request->getPost('product_name')} is already in the database")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The product name {$this->request->getPost('product_name')} is exist in the database")));
                }*/
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

                $this->base_model->update_data(array('table'=>'products','where'=>array('id'=>$id),'data',$data),true);
                exit(json_encode(array('status'=>1,'msg'=>'Product successfully updated!')));
            }
            else
            {
                exit(json_encode(array('status'=>0,'msg'=>$validation->listErrors())));
            }
            
            
        }else
        {
            if(user_has_access($this->controller,__FUNCTION__))
            {
                $data=$this->base_model->get_data(array('table'=>'products','where'=>array('id'=>$id)),true);

                //print_r($data);exit;
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']=' 404 Not found';
                }
                else
                {
                    $config=array();
                    $config['product_name']=array('field_type'=>'text_field','label'=>'Product Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['product_name']??'');
                    $config['price']=array('field_type'=>'text_field','label'=>'price','required'=>'required','value'=>$data['price']??'');
                    $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_product_status(),'value'=>$data['status']??'');
                    $config['size']=array('field_type'=>'text_field','label'=>'size','required'=>'required','value'=>$data['size']??'');
                    $config['color']=array('field_type'=>'text_field','label'=>'color','value'=>$data['color']??'');
                    $config['stock']=array('field_type'=>'number_field','label'=>'Stock','required'=>'required','value'=>$data['stock']??'');
                    $config['category']=array('field_type'=>'text_field','label'=>'Category','required'=>'required','value'=>$data['category']??'');
                    $config['brand']=array('field_type'=>'text_field','label'=>'Brand','value'=>$data['brand']??'');
                    $config['updated_at']=array('field_type'=>'text_field','label'=>'Updated At','value'=>$data['updated_at']??'');
                    $config['description']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$data['description']??'','cols'=>300,'rows'=>3);
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Product';
                    $vars['submit_url']= ("/products/edit_product/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit Product';
                }
            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']=' 404 Unauthorized';
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
                'price'=>'required',
                'category'=>'required',
                'size'=>'required',
                'image'=>'required',);
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $image = $this->request->getFile('image');
                if($image->isValid() && !$image->hasMoved())
                {
                    $uploadPath = WRITEPATH.'uploads/products/';
                    $imageName = $image->getRandomName();
                    $image->move($uploadPath,$imageName);
                }



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

               $data['image']=$uploadPath.$imageName;
              // $data['user_id']=$_SESSION['user_id']['id'];
              // $data['date_time_created']=date('Y-m-d H:i:s');
               $id=$this->base_model->insert_data('products',$data);
               exit(json_encode(array('status'=>1,'msg'=>'Product added successfully')));

            }
            else
            {
            exit(json_encode(array('status'=>0,'message'=>$validation->listErrors())));
            }
        }
        else
        {
            if(user_has_access($this->controller,__FUNCTION__))
            {
                $config=array();
                $config['product_name']=array('field_type'=>'text_field','label'=>'Product Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['product_name']??'');
                $config['price']=array('field_type'=>'text_field','label'=>'price','required'=>'required','value'=>$_POST['price']??'');
                $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['status']??'');
                $config['size']=array('field_type'=>'text_field','label'=>'size','required'=>'required','value'=>$_POST['size']??'');
                $config['color']=array('field_type'=>'text_field','label'=>'color','required'=>'required','value'=>$_POST['color']??'');
                $config['stock']=array('field_type'=>'text_field','label'=>'Stock','required'=>'required','type'=>'number','value'=>$_POST['stock']??'');
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
    }

    public function set_search_data($params)
    {

    }
}