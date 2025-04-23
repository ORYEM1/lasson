<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class RestrictedBaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['url','general','text','form','my_form','menu','data_tables','query','permission'];
    protected \App\Models\BaseModel $base_model;
    protected \CodeIgniter\Session\Session $session;
    protected \App\Libraries\AdvancedSearch $advanced_search;
    protected \App\Libraries\Permission $permission;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        //Models
        $this->base_model = new \App\Models\BaseModel();

        //Libraries
        $this->session = \Config\Services::session();
        $this->advanced_search=new \App\Libraries\AdvancedSearch();
        $this->permission=new \App\Libraries\Permission();
        //Helpers
        helper($this->helpers);

        if(!isset($_SESSION['user_data']) || !isset($_SESSION['permissions']))
        {
            redirect_user(base_url('login'));
        }
        $this->refresh_user_data();
        $this->permission->refresh_permissions();
        date_default_timezone_set($_ENV['locale.timezone']);
    }
    public function refresh_user_data()
    {
        if(empty($_SESSION['user_data']['id']))
        {
            redirect_user(base_url('login'));
        }
        $user_data=$this->base_model->get_data(array('table'=>'users','where'=>array('id'=>$_SESSION['user_data']['id']),'use_cache'=>true),true);
        if(empty($user_data))
        {
            redirect_user(base_url('login'));
        }
        if(strtolower($user_data['status'])!=1)
        {
            redirect_user(base_url('login'));
        }
        $_SESSION['user_data']=$user_data;
        return true;
    }
    public function set_search_data($params)
    {
        $this->advanced_search->set_query_search_data($params);
    }
}
