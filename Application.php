<?php

namespace fabwnklr\fat;

use fabwnklr\fat\db\DbModel;
use fabwnklr\fat\db\Database;

/**
 * Class Application
 * 
 * @author Fabien <fabien.winkler@outlook.fr>
 * @package fabwnklr\fat
 */
class Application
{
    public static Application $app;
    public static string $ROOT_DIR;

    public string $layout = 'main';
    public string $userClass;
    public Router $router;
    public Request $request;
    public Response $response;
    public Database $db;
    public ?Controller $controller = null;
    public Session $session;
    public ?UserModel $user = null;
    public View $view;

    /**
     * Constructor
     *
     * @param String $rootPath
     * @param array $config
     */
    function __construct(String $rootPath, array $config)
    {
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->userClass = $config['userClass'];
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->session = new Session();
        $this->view = new View();

        $this->db = new Database($config['db']);

        $primaryValue = $this->session->get('user');

        if ($primaryValue) :
            $primaryKey = $this->userClass::primaryKey();
            $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);
        endif;
    }

    function run()
    {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', ['exception' => $e]);
        }
    }

    public static function isGuest()
    {
        return !self::$app->user;
    }

    /**
     * Login an user
     *
     * @param DbModel $user
     * @return bool
     */
    public function login(UserModel $user): bool
    {
        $this->user = $user;
        $primaryKey = $this->user->primaryKey();
        $primaryKeyValue = $user->{$primaryKey};

        $this->session->set('user', $primaryKeyValue);

        return true;
    }

    /**
     * Logout an user
     *
     * @return void
     */
    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }
}
