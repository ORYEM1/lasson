<?php

namespace App\Controllers;

class Roles extends RestrictedBaseController
{
    private string $controller;
    public function __construct()
    {
        $this->controller = strtolower((new \ReflectionClass($this))->getShortName());
    }
    public function index()
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $table='user_roles';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            if($this->request->getPost('search'))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id');
                $params['like_search_fields']=array('role'=>'role');
                $params['where_in_search_fields']=array('status'=>'status','role_type'=>'role_type');
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='User Roles';
            $vars['page_heading']='User Roles';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Role','sortable'=>false);
            $data_header[]=array('name'=>'Role Type','sortable'=>false);
            $data_header[]=array('name'=>'Status','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
           // $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>"/roles/new_role",'label'=>'New Role','icon'=>'fa fa-plus'));
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_roles'),'bFilter'=>true,'order_columns'=>array('Role'=>'asc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);


        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_role($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id','creator_id','role','role_type','status','rights','comment','users.first_name','users.last_names');
            $join[]=array('table'=>'users','on'=>'users.id=user_roles.creator_id','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'user_roles','fields'=>$fields,'join'=>$join,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['content_view']='roles/view_role';
                $vars['title']='User Role';
                $vars['page_heading']='User Role';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_role($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit role')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('role' =>'required','status' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_roles=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('role'=>$this->request->getPost('role'))));
                if(count($existing_roles)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The role name {$this->request->getPost('role')} is already assigned to another role")));
                }
                else if(isset($existing_roles[0]['id'])&&$existing_roles[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The role name {$this->request->getPost('role')} is already assigned to another role")));
                }
                    
                if($this->request->getPost('rights'))
                {
                    $_POST['rights']=implode(',',$this->request->getPost('rights'));
                }
                else 
                {
                    $_POST['rights']=null;
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
                
                $this->base_model->update_data(array('table'=>'user_roles','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Role updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['role']=array('field_type'=>'text_field','label'=>'Role','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['role']??'');
                    $config['role_type']=array('field_type'=>'select_field','label'=>'Role Type','required'=>'required','options'=>array('Admin','Basic','Normal'),'value'=>$data['role_type']??'');
                    $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_statuses_array(),'value'=>$data['status']??'');
                    $config['rights']=array('field_type'=>'checklist','label'=>'Rights','options'=>get_permissions(),'value'=>$data['rights']??'');
                    $config['comment']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$data['comment']??'','cols'=>300,'rows'=>3);
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit User Role';
                    $vars['submit_url']= base_url("roles/edit_role/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit User Role';
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
    public function new_role()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            unset($_POST['base_role']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add a role')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('role' =>'required','status' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('role'=>$this->request->getPost('role'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The role name {$this->request->getPost('role')} is already assigned to another role")));
                }

                if($this->request->getPost('rights'))
                {
                    $_POST['rights']=implode(',',$this->request->getPost('rights'));
                }
                else
                {
                    $_POST['rights']=null;
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
                $data['creator_id']=$_SESSION['user_data']['id'];
                $id=$this->base_model->insert_data('user_roles',$data);
                exit(json_encode(array('status'=>1,'msg'=>"User Role created successfully. ID:{$id}")));
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
                $config=array();
                $config['role']=array('field_type'=>'text_field','label'=>'Role','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['role']??'');
                $config['role_type']=array('field_type'=>'select_field','label'=>'Role Type','required'=>'required','options'=>array('Admin','Basic','Normal'),'value'=>$_POST['role_type']??'');
                $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['status']??'');
                $options=$this->base_model->get_form_options(array('table'=>'user_roles','order'=>array('role'=>'asc')),'id','role');
                $config['base_role']=array('field_type'=>'select_field','label'=>'Base Role','id'=>'base_role','options'=>$options,'class'=>'select linked_checklist','data-linked_id'=>'rights','data-linked_name'=>'rights');
                $config['rights']=array('field_type'=>'checklist','label'=>'Rights','options'=>get_permissions(),'attributes'=>array('id'=>'rights','data-name'=>'rights','data-source'=>base_url('rpc/get_base_role_checklist')),'value'=>$_POST['rights']??'');
                $config['comment']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$_POST['comment']??'','cols'=>300,'rows'=>3);
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Role';
                $vars['submit_url']= base_url("roles/new_role");
                $vars['content_view']='form';
                $vars['title']='New Role';
            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']='401 Unauthorized';
            }
            return view($vars['content_view'],$vars);
        }
    }

}