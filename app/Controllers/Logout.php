<?php

namespace App\Controllers;
class Logout extends BaseController
{
    public function index()
    {
        if(isset($_SESSION['user_data']))
        {
            $activity_log=array('user_id'=>$_SESSION['user_data']['id'],'date_time'=>date('Y-m-d H:i:s'),'ip_address'=>$_SERVER['REMOTE_ADDR'],'activity'=>'Logged out');
            $this->base_model->insert_data('activity_log',$activity_log);
            unset($_SESSION['user_data']);
        }
        unset($_SESSION['permissions']);
        unset($_SESSION['role']);
        redirect_user(base_url('login'));
    }
}
