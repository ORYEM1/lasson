<?php
function controller_permissions()
{
    //Users
    $permissions['users']['index']='view users';
    $permissions['users']['view_user']='view users';
    $permissions['users']['edit_user']='edit users';
    $permissions['users']['new_user']='add users';
    $permissions['users']['reset_password']='reset user password';
    //sales
    $permissions['sales']['index']='view sales';
    $permissions['sales']['view_sale']='edit sale';


    //orders
    $permissions['orders']['delete_order']='delete order';
    $permissions['orders']['cancel_order']='cancel order';
    $permissions['orders']['edit_order']='edit order';
    $permissions['orders']['new_order']='add order';
    $permissions['orders']['view_order']='view order';


    //stocks
    $permissions['stocks']['new_stock']='add stock';
    $permissions['stocks']['delete_stock']='delete stock';
    $permissions['stocks']['edit_stock']='edit stock';
    $permissions['stocks']['view']='view stocks';

    //transactions
    $permissions['transactions']['view_transactions']='view transactions';
    $permissions['transactions']['delete_transactions']='delete transactions';



    //Roles
    $permissions['roles']['index']='view user roles';
    $permissions['roles']['view_role']='view user roles';
    $permissions['roles']['edit_role']='edit user roles';
    $permissions['roles']['new_role']='add user role';

    //Edited data log
    $permissions['edited_data_log']['index']='view edited data log';
    $permissions['edited_data_log']['view_log']='view edited data log';

    //Activity data log
    $permissions['activity_log']['index']='view activity log';
    $permissions['activity_log']['view_log']='view activity log';



    //Logged Numbers
    $permissions['logged_numbers']['index']='view logged numbers';
    $permissions['logged_numbers']['view_logged_number']='view logged numbers';
    $permissions['logged_numbers']['edit_logged_number']='edit logged number';
    $permissions['logged_numbers']['new_logged_number']='add logged number';




    return $permissions;
}
function other_permissions()
{
    $permissions[]='change user role';
    $permissions[]='assign admin role';
    return $permissions;
}
function get_method_permissions($controller,$method)
{
    $controller=strtolower($controller);
    $method=strtolower($method);
    $permissions=controller_permissions();
    if(isset($permissions[$controller][$method]))
    {
        return $permissions[$controller][$method];
    }
    return array();
}
function get_permissions()
{
    $controller_permissions=controller_permissions();
    $permissions=other_permissions();

    foreach ($controller_permissions as $controller=>$method_permissions)
    {
        foreach ($method_permissions as $method_permission)
        {
            if(!is_array($method_permissions))
            {
                $permissions[]=$method_permissions;
                continue;
            }
            foreach ($method_permissions as $method_permission)
            {
                $permissions[]=$method_permission;
            }
        }
    }
    $permissions=array_unique($permissions);
    sort($permissions);
    return $permissions;
}
function user_has_access($controller,$method): bool
{
    $permissions=get_method_permissions($controller,$method);
    if(empty($permissions))
    {
        return true;
    }
    return user_has_permission($permissions);
}
function user_has_permission($permission)
{
    if(!isset($_SESSION['permissions'])) return false;
    if(!is_array($permission))
    {
        $permission=array($permission);
    }
    foreach($permission as $p)
    {
        if(in_array($p,$_SESSION['permissions'])) return true;
    }
    return false;
}
