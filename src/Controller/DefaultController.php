<?php

namespace USDBuySell\App\Controller;

use Illuminate\Database\Capsule\Manager;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class DefaultController
 * @package USDBuySell\App\Controller
 */
class DefaultController extends AppController
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function home(Request $request, Response $response, array $args = [])
    {
        $moneyProviders = [];
        $moneyProviders = Manager::table('payment_providers')->get();

        return $this->getView()->render($response, 'default/home.twig', [
            'moneyProviders' => $moneyProviders
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dashboardView(Request $request, Response $response, array $args = [])
    {
        if ($_SESSION['auth']->role != 'general-user') {
            $this->getFlash()->addMessage('error', 'Sorry, Invalid Permission');
            return $response->withRedirect('/login');
        }

        return $this->getView()->render($response, 'default/dashboard.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function faqView(Request $request, Response $response, array $args = [])
    {
        return $this->getView()->render($response, 'default/faq.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function aboutView(Request $request, Response $response, array $args = [])
    {
        return $this->getView()->render($response, 'default/about.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function paymentProofView(Request $request, Response $response, array $args = [])
    {
        return $this->getView()->render($response, 'default/payment_proof.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function contactView(Request $request, Response $response, array $args = [])
    {
        return $this->getView()->render($response, 'default/contact.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function termAndConditionView(Request $request, Response $response, array $args = [])
    {
        return $this->getView()->render($response, 'default/terms_and_conditions.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function testimonialsView(Request $request, Response $response, array $args = [])
    {
        return $this->getView()->render($response, 'default/customer_feedback.twig', []);
    }
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function providersView(Request $request, Response $response, array $args = [])
    {
        $providers =  Manager::table('payment_providers')->get();
        $providersArray = (array) $providers;

        return $response->withJson($providersArray);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function userProfileUpdate(Request $request, Response $response)
    {
        $userUUID = $request->getAttribute('userUUID');
        $postData = $request->getParsedBody();

        try {
            $updateUser = Manager::table('users')
                ->where('uuid', '=', $userUUID)
                ->update($postData);
            if ($updateUser == true) {
                $this->getLogger()->info('User has been updated.');
                $this->getFlash()->addMessage('success', 'User has been update successfully !');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }
            $this->getLogger()->error('User does not updated.');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }
        catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getLogger()->debug($e->getTraceAsString());
        }

        $this->getFlash()->addMessage('error', 'Invalid User ');
        return $response->withRedirect($request->getServerParam('HTTP_REFERRER'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function uploadPicture(Request $request, Response $response)
    {
        $userUUID = $request->getAttribute('userUUID');
        $image = $_FILES['picture'];
        $allowedExts = ["jpeg", "jpg", "png"];
        //Get image extension
        $extension = explode(".", $_FILES["picture"]["name"]);
        $extension = $extension[1];
        /**
         * If the format is not allowed, show error message to user
         */
        if (!in_array($extension, $allowedExts)) {
            $this->getFlash()->addMessage('error', 'Sorry, only JPG, JPEG & PNG files are allowed.');
            return $response->withRedirect('/dashboard');
        }else {
            try {
                $profilePicUpload = $this->uploadImages($image['tmp_name'], $image['name'], 'assets/images/profiles/', uniqid());

                if($profilePicUpload) {
                    $data['profile_pic'] =  "/" . $profilePicUpload['path'];
                    $changedProfilePic = Manager::table('users')
                        ->where('uuid', $userUUID)
                        ->update($data);

                    $_SESSION['auth']->profile_pic = $data['profile_pic'];

                    if($changedProfilePic == 1) {
                        $this->getFlash()->addMessage("success", "Profile picture has been changed");
                        return $response->withRedirect('/dashboard');
                    }
                }
            } catch (\Exception $e) {
                $this->getLogger()->error($e->getMessage());
                $this->getLogger()->debug($e->getTraceAsString());

                $this->getFlash()->addMessage("error", "Failed to get user photo");
                return $response->withRedirect('/dashboard');
            }
        }
    }

    /**
     * @param $tmp
     * @param $name
     * @param $path
     * @param null $customName
     * @return array|bool
     */
    public static function uploadImages($tmp, $name, $path, $customName = null)
    {
        $ext = explode(".", $name);
        $ext = $ext[1];
        $uploadFile = $path . $customName . '.' . $ext;
        if ($ext && $ext != '') {
            if (move_uploaded_file($tmp, $uploadFile)) {
                $name = explode('/', $uploadFile);
                $name = $name[3];
                $data = array(
                    'name' => $name,
                    'path' => $uploadFile,
                );
                return $data;
            }
            return false;
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function providersSellRate(Request $request, Response $response, array $args = [])
    {
        $providers =  Manager::table('payment_providers')->where("slug", $request->getAttribute("providerName"))->first();
        if(empty($providers)){
            return $response->withStatus(404);
        }

        return $response->withJson(['sell_rate' => (float) $providers->sell_rate, 'buy_rate' => (float) $providers->buy_rate]);
    }
}