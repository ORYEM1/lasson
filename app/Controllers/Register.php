<?php

namespace App\Controllers;

class Register extends BaseController
{
    public function index()
    {
        if($this->request->getPost('username')&&is_internal_request())
        {
            $validation = \Config\Services::validation();
            $validation_rules=array(
                'firstname' => 'required',
                'lastname' => 'required',
                'gender' => 'required',
                'email' => 'required|valid_email|is_unique[users.email]',
                'phone_number' => 'required|min_length[10]|max_length[10]',
                'physical_address' => 'required',
                'password' => 'required|min_length[8]',
                'confirm_password' => 'required|matches[password]',


            );
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $login=$this->process_login($_POST['username'],$_POST['password']);
                if($login['error'])
                {
                    $vars['error']=$login['error'];
                }
            }
            else
            {
                $vars['error']=$validation->listErrors();
            }
        }
        $vars['title']='Register';
        return view('register/index',$vars);
    }

    private function process_login($username,$password)
    {
        $user_data=$this->base_model->get_data(array('table'=>'users','where'=>array('username'=>$this->request->getPost('username'))),true);
        if(empty($user_data))
        {
            return array('error'=>'Wrong username or password');
        }
        if($user_data['password']!=sha1($this->request->getPost('password')))
        {
            return array('error'=>'Wrong username or password');
        }
        if($user_data['status']!=1)
        {
            return array('error'=>'Your account is not active');
        }
        if(empty($user_data['role']))
        {
            return array('error'=>'Your account has no defined role');
        }
        $role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$user_data['role'])),true);
        if(empty($role_data))
        {
            return array('error'=>'Your account role was not found');
        }
        if($role_data['status']!=1)
        {
            return array('error'=>'Your account role is not active');
        }
        if($user_data['whitelist_ip'])
        {
            if(empty($user_data['allowed_ip_id']))
            {
                return array('error'=>'Your account is not assigned an IP address pool');
            }
            $ip_data=$this->base_model->get_data(array('table'=>'whitelisted_user_ips','where'=>array('id'=>$user_data['allowed_ip_id'])),true);
            if(empty($ip_data))
            {
                return array('error'=>'Your ip address pool is empty');
            }
            $whitelisted_ips=explode(",",$ip_data['pool']);
            if(!in_array($_SERVER['REMOTE_ADDR'],$whitelisted_ips))
            {
                return array('error'=>'Your current IP address is not whitelisted');
            }

        }
        $activity_log=array('user_id'=>$user_data['id'],'date_time'=>date('Y-m-d H:i:s'),'ip_address'=>$_SERVER['REMOTE_ADDR'],'activity'=>'Logged in');
        $this->base_model->insert_data('activity_log',$activity_log);
        $_SESSION['user_data']=$user_data;
        $_SESSION['role']=$role_data['role'];
        $_SESSION['theme']=$user_data['theme'];
        $_SESSION['permissions']=explode(',',$role_data['rights']);
        redirect_user(base_url());
        exit();
    }

}
