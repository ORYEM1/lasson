<?php

namespace App\Controllers;

class Login extends BaseController
{
    public function index()
    {
        if($this->request->getPost('username')&&is_internal_request())
        {
            $validation = \Config\Services::validation();
            $validation_rules=array('username' => 'required','password' => 'required');
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
        $vars['title']='Login';
        return view('login/index',$vars);
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

        $activity_log=array('user_id'=>$user_data['id'],'date_time'=>date('Y-m-d H:i:s'),'ip_address'=>$_SERVER['REMOTE_ADDR'],'activity'=>'Logged in');
        $this->base_model->insert_data('activity_log',$activity_log);
        $_SESSION['user_data']=$user_data;
        $_SESSION['role']=$role_data['role'];

        $_SESSION['permissions']=explode(',',$role_data['rights']);
        redirect_user(base_url());
        exit();
    }

}
