<?php

namespace App\Controllers;

use function PHPUnit\Framework\lessThanOrEqual;

class Users extends RestrictedBaseController
{
    private string $controller;

    public function __construct()
    {
        $this->controller = strtolower((new \ReflectionClass($this))->getShortName());
    }

    public function index()
    {
        if (user_has_access($this->controller, __FUNCTION__)) {
            $table = 'users';
            $_SESSION["search_{$table}"] = array();
            $_SESSION["search_{$table}_where_in"] = array();
            $_SESSION["search_{$table}_like"] = array();
            if ($this->request->getPost('search')) {
                $params = array();
                $params['table_name'] = $table;
                $params['where'] = $where ?? array();
                $params['where_in'] = $where_in ?? array();
                $params['where_search_fields'] = array('id' => 'id', 'username' => 'username');
                $params['like_search_fields'] = array('date_created' => 'users.date_time_created', 'phone_number' => 'users.phone_number');
                $params['where_in_search_fields'] = array('status' => 'users.status', 'role' => 'users.role', 'gender' => 'users.gender');
                $search_range = array();
                $search_range['from_date'] = array('column' => 'date_time_created', 'operator' => '>=');
                $search_range['to_date'] = array('column' => 'date_time_created', 'operator' => '<=');
                $params['search_range'] = $search_range;
                $this->set_search_data($params);
            }
            $vars['content_view'] = 'data_table';
            $vars['title'] = 'Users';
            $vars['page_heading'] = 'Users';


            //Data header
            //============================================================================================
            $data_header[] = array('name' => 'ID', 'sortable' => true, 'db_col_name' => 'id');
            $data_header[] = array('name' => 'FIRST NAME', 'sortable' => true, 'db_col_name' => 'first_name');
            $data_header[] = array('name' => 'LAST NAMES', 'sortable' => true, 'db_col_name' => 'last_names');
            $data_header[] = array('name' => 'ROLE', 'sortable' => false);
            $data_header[] = array('name' => 'EMAIL', 'sortable' => false);
            $data_header[] = array('name' => 'USERNAME', 'sortable' => false);
            $data_header[] = array('name' => 'PHONE NUMBER', 'sortable' => false);
            $data_header[] = array('name' => 'STATUS', 'sortable' => false);
            $data_header[] = array('name' => 'GENDER', 'sortable' => true, 'db_col_name' => 'gender');
            $data_header[] = array('name' => 'DATE CREATED', 'sortable' => true, 'db_col_name' => 'date_time_created');
            $data_header[] = array('name' => 'CREATED BY', 'sortable' => true, 'db_col_name' => 'created_by');
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '', 'class' => 'icon_col', 'sortable' => false);
            $data_header[] = array('name' => '<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />', 'class' => 'icon_col', 'sortable' => false);
            $vars['data_header'] = $data_header;

            //Data Footer
            //============================================================================================

            $data_footer[] = get_new_link_button(array('url' => '/users/new_user', 'label' => 'New User', 'icon' => 'fa fa-plus'));
            $vars['data_footer'] = $data_footer;

            //Data tables options
            //============================================================================================
            $dt_params = array('ajax' => '/data_tables/get_data/get_users', 'bFilter' => true, 'order_columns' => array('ID' => 'desc'));
            $vars['data_tables_config'] = get_dt_config($data_header, $dt_params);

        } else {
            $vars['content_view'] = 'unauthorized';
            $vars['title'] = '401 Unauthorized';
        }
        return view('page', $vars);
    }

    public function edit_user($id = 0)
    {
       if($this->request->getPost('submit'))
       {
           unset($_POST['submit']);
           if(!user_has_access($this->controller, __FUNCTION__))
           {
               exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page.")));

           }
           $validation = \Config\Services::validation();
           $validation_rules=array('first_name'=>'required','last_name'=>'required','username'=>'required','password'=>'required','role'=>'required');
           $validation->setRules($validation_rules);
           if($validation->withRequest($this->request)->run())
           {
               $existing_users=$this->base_model->get_data(array('table'=>'users', 'where'=>array('username'=>$this->request->getPost('username'))));
               if(count($existing_users)>1)
               {
                   exit(json_encode(array('status' => 'error', 'message' => "Username already exists.")));
               }
               else if(isset($existing_users[0]['id']) && $existing_users[0]['id']==$id)
               {
                   exit(json_encode(array('status' => 'error', 'message' => "Username already exists.")));
               }

               $db_user_data=$this->base_model->get_data(array('table'=>'users', 'where'=>array('id'=>$id)));
               if(!user_has_permission('change user role')&&$db_user_data['role']!=$this->request->getPost('role'))
               {
                   exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page.")));
               }
               $user_role_data=$this->base_model->get_data(array('table'=>'user_roles', 'where'=>array('id'=>$db_user_data['role'])));
               if(strtolower($user_role_data['role'])==$this->request->getPost('role'))
               {
                   exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page.")));
               }
               if(isset($user_role_data['role_type'])&& strtolower($user_role_data['role_type'])!='admin')
               {
                   $assigned_role_id=$this->request->getPost('role');
                   if(empty($assigned_role_id))
                   {
                       exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page.")));
                   }
                   $assigned_role=$this->base_model->get_data(array('table'=>'roles', 'where'=>array('id'=>$assigned_role_id)),true);
                   if(empty($assigned_role))
                   {
                       exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page.")));
                   }
                   if(strtolower($user_role_data['role'])==$this->request->getPost('role'))
                   {
                       exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page.")));
                   }
               }
               $user_data=array();
               foreach($_POST as $key=>$value)
               {
                   if(empty($value)&&$value!=0)
                   {
                       $user_data[$key]=null;
                   }
                   else if(is_array($value))
                   {
                       $user_data[$key]=implode(',',$value);
                   }
                   else

                   {
                       $user_data[$key]=$value;
                   }
               }
               $this->base_model->update_data(array('table'=>'users', 'where'=>array('id'=>$id)),array('data'=>$user_data),true);
               exit(json_encode(array('status' => 'User updated successfully.')));

           }
           else
           {
           exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page.")));
           }
       }
       else
       {
           if(user_has_access($this->controller, __FUNCTION__))
           {
               $user_data=$this->base_model->get_data(array('table'=>'users', 'where'=>array('id'=>$id)),true);

               if(empty($user_data))
               {
                   $vars['content_view'] = 'not_found';
                   $vars['title'] = '404 Not Found';
               }
               else
               {
                   $config=array();
                   $config['first_name']=array('field_type'=>'text_field','label'=>'First Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$user_data['first_name']??'');
                   $config['last_name']=array('field_type','text_field','label'=>'Last Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$user_data['last_name']??'');
                   $config['gender']=array('field_type'=>'select_field','label'=>'Gender','options'=>get_genders_array(),'value'=>$user_data['gender']??'');
                   $config['phone_number']=array('field_type'=>'text_field','label'=>'Phone Number','type'=>'text','value'=>$user_data['phone_number']??'');
                   $config['email']=array('field_type'=>'text_field','label'=>'Email','type'=>'email','value'=>$user_data['email']??'');
                   $config['username']=array('field_type'=>'text_field','label'=>'Username','required'=>'required','type'=>'text','value'=>$user_data['username']??'');
                   $options=$this->base_model->get_form_options(array('table'=>'user_roles','order'=>array('role'=>'asc')),'id','role');
                   $config['role']=array('field_type'=>'select_field','label'=>'Role','options'=>$options,'value'=>$user_data['role']??'');
                   $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_statuses_array(),'value'=>$user_data['status']??'');
                   $vars['form_data']=get_form_data($config);
                   $vars['form_title']='Edit User';
                   $vars['submit_url']= "/users/edit_user/{$user_data['id']}";
                   $vars['content_view']='form';
                   $vars['title']='Edit User';
               }
           }

           else
           {
               $vars['content_view'] = 'unauthorized';
               $vars['title'] = '401 Unauthorized';
           }
           return view($vars['content_view'],$vars);
       }

    }
    public function view_user($id=0)
    {
        if (user_has_access($this->controller, __FUNCTION__))
        {
            $fields=array('id','first_name','last_names','role','email','username','phone_number','status','gender','date_time_created','user_roles.role','user_roles.role_type','created_by');
            $join[]=array('table'=>'user_roles','condition'=>'users.role=user_roles.id','type'=>'left');
            $join[]=array('table'=>'users cb','condition'=>'users.created_by=cb.id','type'=>'left');
            $user_data=$this->base_model->get_data(array('table'=>'users','fields'=>$fields,'join'=>$join,'where'=>array('users.id'=>$id)),true);
            if(empty($user_data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['page_heading']= $user_data['first_name'].' '.$user_data['last_names'];
                $vars['record']=$user_data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='users/view_user';
                $vars['title']='User Data';
            }

        }
        else
        {
          $vars['content_view'] = 'unauthorized';
          $vars['title'] = '401 Unauthorized';
        }
        return view($vars['content_view'], $vars);

    }

    public function new_user($load_type='')
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page")));

            }
            $validation=\Config\Services::validation();
            $validation_rules=array('first_name'=>'required','last_names'=>'required','email'=>'required','phone_number'=>'required','password'=>'required|min_length[8]','role'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_users=$this->base_model->get_data(array('table'=>'users','where'=>array('username'=>$this->request->getPost('username'))));
                if(!empty($existing_users))
                {
                    exit(json_encode(array('status' => 'error', 'message' => "This username already exists")));
                }
                $user_role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$_SESSION['user_data']['role'])),true);
                if(isset($user_role_data['role_type'])&&strtolower($user_role_data['role_type'])=='basic')
                {
                    exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page")));
                }
                if(isset($user_role_data['role_type'])&&strtolower($user_role_data['role_type'])!='admin')
                {
                    $assigned_role_id=$this->request->getPost('role');
                    if(empty($assigned_role_id))
                    {
                        exit(json_encode(array('status' => 'error', 'message' => "You must assign role to this user")));
                    }
                    $assigned_role=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$assigned_role_id)),true);
                    if(empty($assigned_role))
                    {
                        exit(json_encode(array('status' => 'error', 'message' => "You must assign role to this user")));
                    }
                    if(strtolower($assigned_role['role_type'])=='admin')
                    {
                        exit(json_encode(array('status' => 'error', 'message' => "You don't have access to this page")));
                    }
                }
                $user_data=array();
                foreach($_POST as $key=>$value)
                {
                    if(empty($value)&&$value!=0)
                    {
                        $user_data[$key]=null;
                    }
                    else if(is_array($value))
                    {
                        $user_data[$key]=implode(",",$value);
                    }
                    else
                    {
                        $user_data[$key]=$value;
                    }
                }
                $user_data['password']=sha1($user_data['password']);
                $date=date('Y-m-d h:i:s');
                $time=date('Y-m-d h:i:s');
                $user_data['date_time_created']=$date.' '.$time;
                $user_data['created_by']=$_SESSION['user_data']['id'];
                $id=$this->base_model->insert_data('users',$user_data);
                exit(json_encode(array('status' => 1, 'message' => "User created successfully")));
            }
            else
            {
                exit(json_encode(array('status' => 'error', 'message' => \Config\Services::validation()->listErrors())));
            }
        }
        else
        {
            if(user_has_access($this->controller,__FUNCTION__))
            {
                $config=array();
                $config['first_name']=array('field_type'=>'text_field','label'=>'First Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['first_name']??'');
                $config['last_names']=array('field_type'=>'text_field','label'=>'Last Names','type'=>'text','required'=>'required','value'=>$_POST['last_names']??'');
                $config['gender']=array('field_type'=>'select_field','label'=>'Gender','required'=>'required','options'=>get_genders_array(),'value'=>$_POST['gender']??'');
                $config['phone_number']=array('field_type'=>'text_field','label'=>'Phone Number','type'=>'text','value'=>$_POST['phone_number']??'');
                $config['email']=array('field_type'=>'text_field','label'=>'Email','type'=>'email','value'=>$_POST['email']??'');
                $config['username']=array('field_type'=>'text_field','label'=>'Username','required'=>'required','type'=>'text','value'=>$_POST['username']??'');
                $config['password']=array('field_type'=>'password_field','label'=>'Password','required'=>'required','type'=>'password','value'=>$_POST['password']??'','minlength'=>'8');
                $options=$this->base_model->get_form_options(array('table'=>'user_roles','order'=>array('role'=>'asc')),'id','role');
                $config['role']=array('field_type'=>'select_field','label'=>'Role','options'=>$options,'value'=>$_POST['role']??'');
                $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['status']??'');

                $config['comment']=array('field_type'=>'textarea','label'=>'Comments','type'=>'text','value'=>$_POST['comment']??'','cols'=>300,'rows'=>3);
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New User';
                $vars['submit_url']="/users/new_user";
                $vars['content_view']='form';
                $vars['title']='New User';

            }
            else
            {
                $vars['content_view'] = 'unauthorized';
                $vars['title'] = '401 Unauthorized';
            }
            return view($vars['content_view'], $vars);
        }


    }
    public function reset_password($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);

            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'message'=>"You don't  have permission to reset password")));

            }
            $validation=\Config\Services::validation();
            $validation_rules=array('password'=>'required|min_length[8]','password_confirm'=>'required|matches[password]');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                if($this->request->getPost('password')!=$this->request->getPost('password_confirm'))
                {
                    exit(json_encode(array('status'=>0,'message'=>"Passwords do not match")));
                }
                $db_user_data=$this->base_model->get_data(array('table'=>'users','where'=>array('id'=>$id)),true);
                $user_role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$_SESSION['user_data']['role'])),true);
                $db_user_role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$db_user_data['role']),true),true);
                if(strtolower($db_user_role_data['role_type'])=='admin'&& strtolower($user_role_data['role_type'])!='admin')
                {
                    exit(json_encode(array('status'=>0,'message'=>"Your role type is not admin")));
                }
                $password=sha1($this->request->getPost('password'));
                $user_data=array('password'=>$password);

                $this->base_model->update_data(array('table'=>'users','where'=>array('id'=>$id),'data'=>$user_data),true);
                exit(json_encode(array('status'=>1,'message'=>"Password reset successfully")));
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
                $query_params=array('table'=>'users','where'=>array('id'=>$id));
                $user_data=$this->base_model->get_data($query_params,true);

                if(empty($user_data))
                {
                    $vars['content_view'] = 'not_found';
                    $vars['title'] = '404 Not Found';
                }

                else
                {
                    $config=array();
                    $config['password']=array('field_type'=>'password_field','label'=>'New Password','required'=>'required');
                    $config['password_confirm']=array('field_type'=>'password_field','label'=>'Retype Password','required'=>'required');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Reset Password for '.$user_data['first_name'].' '.$user_data['last_names'];
                    $vars['submit_url']=base_url("/users/reset_password/{$user_data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Reset Password';


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
    public function change_password()

    {
        if($this->request->getPost('submit'))
        {
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'message'=>"You don't have permission to reset password")));
            }
            unset($_POST['submit']);
            $validation=\Config\Services::validation();
            $validation_rules=array('old_password'=>'required|min_length[8]','confirm_password'=>'required|min_length[8]');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                if(sha1($this->request->getPost('old_password'))!=$_SESSION['user_data']['password'])
                {
                    exit(json_encode(array('status'=>0,'message'=>"Incorrect old password")));
                }
                if($this->request->getPost('password')!=$this->request->getPost('confirm_password'))
                {
                    exit(json_encode(array('status'=>0,'message'=>"Passwords do not match")));
                }
                $password=sha1($this->request->getPost('password'));
                $user_data=array('password'=>$password);
                $this->base_model->update_data(array('table'=>'users','where'=>array('id'=>$_SESSION['user_data']['id'])), $user_data);
                exit(json_encode(array('status'=>1,'message'=>"Password reset successfully")));
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
                $config=array();
                $config['old_password']=array('field_type'=>'password_field','label'=>'Old Password','required'=>'required','value'=>$_POST['password']??'');
                $config['password']=array('field_type'=>'password_confirm','label'=>'New Password','required'=>'required','value'=>$_POST['password']??'','minlength'=>'8');
                $config['password_confirm']=array('field_type'=>'password_confirm','label'=>'Confirm Password','required'=>'required','value'=>$_POST['password']??'','minlength'=>'8');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='change Password';
                $vars['submit_url']=base_url("users/change_password");
                $vars['content_view']='form';
                $vars['title']='Change Password';
            }
            else
            {
                $vars['content_view'] = 'unauthorized';
                $vars['title'] = '401 Unauthorized';
            }
            return view($vars['content_view'], $vars);

        }
    }
    public function delete_user($id = 0)
    {
        if (!user_has_access($this->controller, __FUNCTION__)) {
            exit(json_encode(array('status' => 0, 'msg' => 'You do not have permission to delete orders.')));
        }

        if (empty($id)) {
            exit(json_encode(array('status' => 0, 'msg' => 'Invalid User ID.')));
        }

        $user = $this->base_model->get_data(array(
            'table' => 'users',
            'where' => array('id' => $id)
        ), true);

        if (empty($user)) {
            exit(json_encode(array('status' => 0, 'msg' => 'User not found.')));
        }

        // Delete the order
        $this->base_model->delete_data('users', array(
            'where' => array('id' => $id)
        ));

        exit(json_encode(array( 'User deleted successfully.')));
    }

}