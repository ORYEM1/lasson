<?php

namespace App\Libraries;

class Permission
{
    private \App\Models\BaseModel $base_model;
    function __construct()
    {
        $this->base_model = new \App\Models\BaseModel();
    }

    public function refresh_permissions()
    {
        if(empty($_SESSION['user_data']['role']))
        {
            redirect_user(base_url('login'));
        }
        $role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$_SESSION['user_data']['role']),'use_cache'=>true),true);
        if(empty($role_data))
        {
            redirect_user(base_url('login'));
        }
        $_SESSION['permissions']=explode(',',$role_data['rights']);
        return true;
    }

}