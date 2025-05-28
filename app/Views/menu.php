<!--Beginning of nav-->
<nav id="menu">
    <div id='cssmenu'>
        <ul>
            <?php
            $menu=array();
            $menu['Home']=array('href'=>'/','icon_class'=>'fa fa-home');


            //stocks
            $menu['Stocks']=array('href'=>'#','class'=>'has-sub','icon_class'=>'fa fa-cubes');
            $menu['Stocks']['submenu']['Stocks']=array('href'=>'/stocks');
            $menu['Stocks']['submenu']['New Stock']=array('href'=>'/new_stock');



            $menu['Products']=array('href'=>'#','class'=>'has-sub');
            $menu['Products']['submenu']['All Products']=array('href'=>'/products');
            $menu['Products']['submenu']['add_to_order']=array('href'=>'/add_to_order');

            //sales
            $menu['Sales']=array('href'=>'#','class'=>'has-sub','icon_class'=>'fa fa-bar-chart');
            $menu['Sales']['submenu']['Daily']=array('href'=>'/daily');
            $menu['Sales']['submenu']['Weekly']=array('href'=>'/weekly');
            $menu['Sales']['submenu']['Monthly']=array('href'=>'/monthly');
            //$menu['Sales']['submenu']['Report']['submenu']['Annually']=array('href'=>'/annually');

            //orders
            $menu['Orders']=array('href'=>'#','class'=>'has-sub','icon_class'=>'fa fa-shopping-cart');
            $menu['Orders']['submenu']['Orders']=array('href'=>'/orders');
            $menu['Orders']['submenu']['Finished Orders']=array('href'=>'/orders/status/finished');
            $menu['Orders']['submenu']['Cancelled Orders']=array('href'=>'/orders/status/cancelled');
            $menu['Orders']['submenu']['Pending Orders']=array('href'=>'/orders/status/pending');

            $menu['Order Items']=array('href'=>'#','class'=>'has-sub','icon_class'=>'fa fa-shopping-cart');
            $menu['Order Items']['submenu']['Order Items']=array('href'=>'/order_items');




            //testing
            //$menu['Order']['submenu']['New Order']=array('href'=>'#','class'=>'has-sub');

            $menu['Admin Menu']=array('href'=>'#','class'=>'has-sub');
            $menu['Admin Menu']['submenu']['Users']=array('href'=>'/users');
            $menu['Admin Menu']['submenu']['User Roles']=array('href'=>'/roles');
            $menu['Admin Menu']['submenu']['Transactions']=array('href'=>'/transactions');
           //$menu['Admin Menu']['submenu']['Staffs']=array('href'=>'/staffs');

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
