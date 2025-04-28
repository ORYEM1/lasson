<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Store::index');



//users
$routes->match(['get','post'], '/users', 'Users::index');
$routes->match( ['get','post'],'/users/new_user/(:any)', 'Users::new_user/$1');
$routes->match(['get','post'], '/users/view_user/(:any)', 'Users::view_user/$1');
$routes->match(['get','post'], '/users/edit_user/(:any)', 'Users::edit_user/$1');
$routes->match(['get','post'], '/users/delete_user/(:any)', 'Users::delete_user/$1');

//login
$routes->match(['get','post'], '/login', 'Login::index');

//register
$routes->match(['get','post'], '/register', 'Register::index');

//product
$routes->match(['get','post'],'/products', 'Products::index');
$routes->match(['get','post'],'/products/new_product/(:any)', 'Products::new_product/$1');
$routes->match(['get','post'], '/products/view_product/(:any)', 'Products::view_product/$1');
$routes->match(['get','post'],'/products/edit_product/(:any)', 'Products::edit_product/$1');

//stock
$routes->match(['get','post'],'/stocks', 'Stocks::index');
$routes->match(['get','post'],'/stocks/new_stock', 'Stocks::new_stock');
$routes->match(['get','post'],'/stocks/view_stock/(:any)', 'Stocks::view_stock/$1');


$routes->match(['get','post'],'/stocks/edit_stock/(:any)', 'Stocks::edit_stock/$1');


//roles
$routes->match(['get','post'], '/roles', 'Roles::index');
$routes->match(['get','post'],'/roles/edit_role/(:any)', 'Roles::edit_role/$1');
$routes->match(['get','post'],'/roles/new_role', 'Roles::new_role');
//$routes->get('roles/new_role/(:any)', 'Roles::new_role/$1');

$routes->match(['get','post'],'users/new_user', 'Users::new_user');


$routes->match(['get','post'],'/users/new_user/(:any)', 'Users::new_user/$1');

$routes->match(['get','post'],'/users/view_user/(:any)', 'Users::view_user/$1');
$routes->match(['get','post'],'/users/edit_user/(:any)', 'Users::edit_user/$1');
$routes->match(['get','post'],'/users/change_password', 'Users::change_password');
$routes->match(['get','post'],'/users/reset_password/(:any)', 'Users::reset_password/$1');

//data table
$routes->get('data_tables/get_data/(:any)', 'DataTables::get_data/$1');
$routes->get('data_table/get_data/(:any)', 'DataTable::get_data/$1');


$routes->match(['get','post'],'products/new_product', 'Products::new_product');

$routes->match(['get','post'],'products/view_product/(:any)', 'Products::view_product/$1');

/*products
$routes->match(['get','post'],'/products', 'Products::index');
$routes->match(['get','post'], '/products/view_product/(:any)', 'Products::view_product/$1');*/



//orders
$routes->match(['get','post'],'/orders', 'Orders::index');
$routes->match(['get','post'],'/orders/view_order/(:any)', 'Orders::view_order/$1');
$routes->match(['get','post'],'/orders/edit_order/(:any)', 'Orders::edit_order/$1');
$routes->match(['get','post'],'/orders/new_order', 'Orders::new_order');
$routes->match(['get','post'],'/orders/delete_order/(:any)', 'Orders::delete_order/$1');
$routes->match(['get','post'],'/orders/status/(:segment)', 'Orders::index/$1');

