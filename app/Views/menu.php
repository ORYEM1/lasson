<!--Beginning of nav-->
<nav id="menu">
    <div id='cssmenu'>
        <ul>
            <?php
            $menu=array();
            $menu['Home']=array('href'=>'/','icon_class'=>'fa fa-home');

            $menu['Stock']=array('href'=>'#','class'=>'has-sub','icon_class'=>'fa fa-cubes');
            $menu['Stock']['submenu']['Overall Stock']=array('href'=>'/products');
            $menu['Stock']['submenu']['New Stock']=array('href'=>'/new_stock');



            $menu['Products']=array('href'=>'#','class'=>'has-sub');
            $menu['Products']['submenu']['Categories']['submenu']['Men']['submenu']['Shoes']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Men']['submenu']['Wears']=array('href'=>'/wears');
            $menu['Products']['submenu']['Categories']['submenu']['Men']['submenu']['Shoes']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Men']['submenu']['Shoes']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Ladies']['submenu']['Shoes']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Ladies']['submenu']['Wears']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Ladies']['submenu']['Shoes']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Ladies']['submenu']['Shoes']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Children']['submenu']['Shoes']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Children']['submenu']['Wears']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Children']['submenu']['Shoes']=array('href'=>'/shoes');
            $menu['Products']['submenu']['Categories']['submenu']['Children']['submenu']['Shoes']=array('href'=>'/shoes');

            //sales
            $menu['Sales']=array('href'=>'#','class'=>'has-sub','icon_class'=>'fa fa-bar-chart');
            $menu['Sales']['submenu']['Report']['submenu']['Daily']=array('href'=>'/daily');
            $menu['Sales']['submenu']['Report']['submenu']['Weekly']=array('href'=>'/weekly');
            $menu['Sales']['submenu']['Report']['submenu']['Monthly']=array('href'=>'/monthly');
            $menu['Sales']['submenu']['Report']['submenu']['Annually']=array('href'=>'/annually');


            $menu['Orders']=array('href'=>'#','class'=>'has-sub');
            $menu['Orders']['submenu']['Active Orders']=array('href'=>'/active_orders');
            $menu['Orders']['submenu']['Finished Orders']=array('href'=>'/finished_orders');
            $menu['Orders']['submenu']['Cancelled Orders']=array('href'=>'/cancelled_orders');
            $menu['Orders']['submenu']['Pending Orders']=array('href'=>'/pending_orders');

            $menu['Admin Menu']=array('href'=>'#','class'=>'has-sub');
            $menu['Admin Menu']['submenu']['Users']=array('href'=>'/users');
            $menu['Admin Menu']['submenu']['User Roles']=array('href'=>'/roles');
            $menu['Admin Menu']['submenu']['Staffs']=array('href'=>'/staffs');
            $menu['Admin Menu']['submenu']['Audit Log']=array('href'=>'#','class'=>'has-sub');
            $menu['Admin Menu']['submenu']['Audit Log']['submenu']['User Activity Log']=array('href'=>'/activity_log');
            $menu['Admin Menu']['submenu']['Audit Log']['submenu']['Edited Data Log']=array('href'=>'/edited_data_log');

            $menu['My Account']=array('href'=>'#','class'=>'has-sub','icon_class'=>'fa fa-user','id'=>'my_account');
            $menu['My Account']['submenu']["Logged in as {$_SESSION['user_data']['first_name']}"]=array('href'=>'#');
            $menu['My Account']['submenu']['Change Password']=array('href'=>'/users/change_password','class'=>'open_modal');
            //$menu['My Account']['submenu']['Reset Password']=array('href'=>'/users/reset_password','class'=>'open_modal');
            $menu['My Account']['submenu']['Logout']=array('href'=>'/logout');
            echo generate_menu($menu);
            ?>
        </ul>
    </div>
</nav>
<!--End of nav-->
