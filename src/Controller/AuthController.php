<?php


namespace USDBuySell\App\Controller;

use Illuminate\Database\Capsule\Manager;
use Previewtechs\PHPUtilities\UUID;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class AuthController
 * @package USDBuySell\App\Controller
 */
class AuthController extends AppController
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function loginView(Request $request, Response $response, array $args = [])
    {
        return $this->getView()->render($response, 'default/login.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function loginProcess(Request $request, Response $response, array $args = [])
    {
        $postData = $request->getParsedBody();

        if (!array_key_exists('email_address', $postData) && !array_key_exists('password',
                $postData) && empty($postData['email_address']) && empty($postData['password'])) {
            $this->getFlash()->addMessage('error', 'Invalid login credentials.');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/login');
        }

        $result = Manager::table('users')
            ->where('email_address', $postData['email_address'])
            ->first();

        // Authorizing user credential
        if(isset($result->password) && $result->password)
        {
            // If authorized then matching password encrption.
            if(password_verify($postData['password'], $result->password) == true)
            {
                // If password encryption matched, then store user data in session.
                $_SESSION['auth'] = $result;

                if ($_SESSION['auth']->role == 'general-user') {
                    return $response->withRedirect("/dashboard");
                }

                return $response->withRedirect("/admin");
            }
        }

        $this->getFlash()->addMessage('error', 'Invalid login credentials');
        return $response->withStatus(302)->withHeader('Location', '/login');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function signUpView(Request $request, Response $response, array $args = [])
    {
        //$this->getFlash()->getMessages();
        return $this->getView()->render($response, 'default/signup.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function signUpProcess(Request $request, Response $response, array $args = [])
    {
        $postData = $request->getParsedBody();

        if (!array_key_exists('first_name', $postData) || empty($postData['first_name'])) {
            $this->getFlash()->addMessage("error", "First name must be required");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
        }

        if (!array_key_exists('last_name', $postData) || empty($postData['last_name'])) {
            $this->getFlash()->addMessage("error", "Last name must be required");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
        }

        if (!array_key_exists('email_address', $postData) || empty($postData['email_address'])) {
            $this->getFlash()->addMessage("error", "Email address must be required");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
        }

        $usersModel = Manager::table('users');
        $emailAddressExists = $usersModel->where('email_address',
            $postData['email_address'])->select('email_address')->first();

        if ($emailAddressExists != null) {
            $this->getFlash()->addMessage("error", "This email address has already registered.");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
        }

        if (!array_key_exists('password', $postData) || empty($postData['password'])) {
            $this->getFlash()->addMessage("error", "Password must be required");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
        }

        if (!array_key_exists('confirm_password', $postData) || empty($postData['confirm_password'])) {
            $this->getFlash()->addMessage("error", "Confirm password must be required");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
        }

        if (strlen($postData['password']) >= 6) {
            if ($postData['password'] != $postData['confirm_password']) {
                $this->getFlash()->addMessage("error", "Password doesn'\t match");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
            }
        } else {
            $this->getFlash()->addMessage('error', 'Password should be minimum six character');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
        }

        unset($postData['confirm_password']);

        if(!isset($postData['uuid']) || empty($postData['uuid'])){
            $postData['uuid'] = UUID::v4();
        }

        $postData['first_name'] = filter_var($postData['first_name'], FILTER_SANITIZE_STRING);
        $postData['last_name'] = filter_var($postData['last_name'], FILTER_SANITIZE_STRING);
        $postData['email_address'] = filter_var($postData['email_address'], FILTER_SANITIZE_EMAIL);
        $postData['password'] = password_hash($postData['password'], PASSWORD_BCRYPT);

        try {
            $userCreated = Manager::table('users')->insert($postData);

            if ($userCreated == true) {
                $this->getLogger()->info('A new user has registered');

                $result = Manager::table('users')
                    ->where('email_address', $postData['email_address'])
                    ->where('uuid', $postData['uuid'])
                    ->first();

                if (!empty($result)) {
                    $_SESSION['auth'] = $result;

                    return $response->withRedirect('/dashboard');
                }

                $this->getLogger()->error('User information not found');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
            }

            $this->getLogger()->error('User registration has failed.');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');

        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getLogger()->debug($e->getTraceAsString());

            $this->getFlash()->addMessage("error", "Failed to sign up process");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER') ?: '/signup');
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function forgotPasswordView(Request $request, Response $response, array $args = [])
    {
        return $this->getView()->render($response, 'default/forgot_password.twig', []);
    }

    /**
     * @param $emailAddress
     * @param $password
     * @return bool
     */
    public function processtoDashboard($emailAddress, $password)
    {
        $usersModel = Manager::table('users');
        $result = $usersModel->where('email_address', $emailAddress)->first();

        // Authorizing user credential
        if(isset($result->password) && $result->password)
        {
            // If authorized then matching password encrption.
            if(password_verify($password, $result->password) == true)
            {
                // If password encryption matched, then return true.
                $_SESSION['auth'] = $result;
                return $result;
            }
        }

        // Otherwise return false.
        $_SESSION['auth'] = null;
        return false;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function logoutProcess(Request $request, Response $response)
    {
        if(!empty($_SESSION['auth'])){
            unset($_SESSION['auth']);
            $this->getFlash()->addMessage("success", "You are successfully logout!");

            return $response->withRedirect("/login");
        }

        return $response->withRedirect("/login");

    }
}