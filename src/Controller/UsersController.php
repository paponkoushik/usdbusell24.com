<?php

namespace USDBuySell\App\Controller;

use Illuminate\Database\Capsule\Manager;
use Previewtechs\PHPUtilities\UUID;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class UsersController
 * @package USDBuySell\App\Controller
 */
class UsersController extends AppController
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUsers(Request $request, Response $response, array $args = [])
    {
        $page = $request->getQueryParam("page") ?: 1;

        if ($request->getQueryParam('page')) {
            $page = intval($request->getQueryParam('page'));
        }

        $perPage = 15;

        $users = [];

        $queryData = $request->getQueryParams();

        $userRole = "super-admin";
        $table = Manager::table('users')
            ->where('role', "!=", $userRole)
            ->orderBy('id', 'desc');

        if (!empty($queryData['first_name'])) {
            $table = $table->where("first_name", "LIKE", "%{$queryData['first_name']}%");
        }


        if (!empty($queryData['last_name'])) {
            $table = $table->where("last_name", "LIKE", "%{$queryData['last_name']}%");
        }

        if (!empty($queryData['status'])) {
            $table = $table->where("status", $queryData['status']);
        }

        if (!empty($queryData['email_address'])) {
            $table = $table->where("email_address", $queryData['email_address']);
        }

        $result = $table->paginate($perPage, ['*'], 'users_list', $page);

        foreach ($result as $item){
            $users[] = (array) $item;
        }

        $usersList = [
            'users' => $users,
            'pagination' => [
                'perPage' => $perPage,
                'page' => $page,
                'hasMorePages' => $result->hasMorePages(),
                'total' => $result->total()
            ]
        ];

        //Pagination
        $total = $usersList['pagination']['total'];
        $maxPage = $total / $perPage;

        if($maxPage > 1 && is_float($maxPage)){
            $maxPage = intval($maxPage + 1);
        } else {
            $maxPage = intval($maxPage);
        }

        return $this->getView()->render($response, 'admin/users/default.twig', [
            'message' => $this->getFlash()->getMessages(),
            'users' => $usersList['users'],
            'total' => $total,
            'perPage' => $perPage,
            'page' => $page,
            'maxPage' => $maxPage,
            'queryData' => $queryData
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getProfile(Request $request, Response $response, array $args = [])
    {
        $userUUID = $request->getAttribute("userUUID");
        $userTable = Manager::table('users')
            ->where("uuid", $userUUID)
            ->first();

        return $this->getView()->render($response, 'admin/users/profile.twig', [
            'message' => $this->getFlash()->getMessages(),
            'user' => $userTable
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getAdminProfile(Request $request, Response $response, array $args = [])
    {
        $this->getFlash()->getMessages();
        return $this->getView()->render($response, 'admin/profile/profile.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function ProfileUpdate(Request $request, Response $response, array $args = [])
    {
        $this->getFlash()->addMessage('success', 'User profile has updated successfully !');
        return $response->withRedirect('/admin/users/profile');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function adminProfileUpdate(Request $request, Response $response, array $args = [])
    {
        $postData = $request->getParsedBody();
        $userId = $request->getParsedBodyParam('uuid');

        if (empty($postData['first_name'])) {
            $this->getFlash()->addMessage('error', "First name field must not be empty");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['first_name'])) {
            if (mb_strlen($postData['first_name']) > 80 ) {
                $this->getFlash()->addMessage('error', "First name must be less than 80 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $postData['first_name'] = filter_var($postData['first_name'], FILTER_SANITIZE_STRING);
        }

        if (empty($postData['last_name'])) {
            $this->getFlash()->addMessage('error', "Last name field must not be empty");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['last_name'])) {
            if (mb_strlen($postData['last_name']) > 80 ) {
                $this->getFlash()->addMessage('error', "Last name must be less than 80 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $postData['last_name'] = filter_var($postData['last_name'], FILTER_SANITIZE_STRING);
        }

        if (!empty($postData['email_address'])) {
            $userTable = Manager::table('users');
            $emailAddressExists = $userTable->where('email_address',
                $postData['email_address'])->select('email_address')->first();

            if ($emailAddressExists != null) {
                $this->getFlash()->addMessage("error", "This email address has already registered.");
                //return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            if (mb_strlen($postData['email_address']) > 128) {
                $this->getFlash()->addMessage('error', "Email address must be less than 128 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $postData['email_address'] = filter_var($postData['email_address'], FILTER_VALIDATE_EMAIL);

        }

        $_SESSION['auth'] = $postData;

        try {
            $updateUser = Manager::table('users')->where('uuid', $userId)->update($postData);

            if ($updateUser == true) {
                $this->getLogger()->info('Profile has been update successfully.');
                $this->getFlash()->addMessage('success', 'Profile has been update successfully !');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $this->getLogger()->error('Profile dose not update.');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));

        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getLogger()->debug($e->getTraceAsString());

            $this->getFlash()->addMessage("error", "Profile does not Update");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function userCreateProcess(Request $request, Response $response, array $args = [])
    {
        $postData = $request->getParsedBody();

        if (empty($postData['first_name'])) {
            $this->getFlash()->addMessage('error', "First name field must not be empty");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['first_name'])) {
            if (mb_strlen($postData['first_name']) > 80 ) {
                $this->getFlash()->addMessage('error', "First name must be less than 80 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $postData['first_name'] = filter_var($postData['first_name'], FILTER_SANITIZE_STRING);
        }

        if (empty($postData['last_name'])) {
            $this->getFlash()->addMessage('error', "Last name field must not be empty");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['last_name'])) {
            if (mb_strlen($postData['last_name']) > 80 ) {
                $this->getFlash()->addMessage('error', "Last name must be less than 80 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $postData['last_name'] = filter_var($postData['last_name'], FILTER_SANITIZE_STRING);
        }

        if (empty($postData['email_address'])) {
            $this->getFlash()->addMessage('error', "Email address name field must not be empty");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['email_address'])) {
            $userTable = Manager::table('users');
            $emailAddressExists = $userTable->where('email_address',
                $postData['email_address'])->select('email_address')->first();

            if ($emailAddressExists != null) {
                $this->getFlash()->addMessage("error", "This email address has already registered.");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            if (mb_strlen($postData['email_address']) > 128) {
                $this->getFlash()->addMessage('error', "Last name must be less than 128 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }
            $postData['email_address'] = filter_var($postData['email_address'], FILTER_VALIDATE_EMAIL);
        }


        if (!empty($postData['password'])) {
            if (mb_strlen($postData['password']) > 80) {
                $this->getFlash()->addMessage('error', "Password must not be grater than 80 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }
        }

        if (empty($postData['confirm_password'])) {
            $this->getFlash()->addMessage('error', "Confirm password filed must not be empty");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (strlen($postData['password']) >= 6) {
            if ($postData['password'] != $postData['confirm_password']) {
                $this->getFlash()->addMessage("error", "Password doesn'\t match");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }
        } else {
            $this->getFlash()->addMessage('error', 'Password should be minimum six character');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }
        unset($postData['confirm_password']);
        $postData['password'] = password_hash($postData['password'], PASSWORD_BCRYPT);

        if(!isset($postData['uuid']) || empty($postData['uuid'])){
            $postData['uuid'] = UUID::v4();
        }

        try {
            $userCreated = Manager::table('users')->insert($postData);

            if ($userCreated == true) {
                $this->getLogger()->info('A new user has been created.');
                $this->getFlash()->addMessage('success', 'A new has added successfully !');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $this->getLogger()->error('A new user does not created.');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));

        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getLogger()->debug($e->getTraceAsString());

            $this->getFlash()->addMessage("error", "User does not added");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function userDelete(Request $request, Response $response, array $args = [])
    {
        $userUUID = $request->getAttribute('userUUID');

        if (empty($userUUID)) {
            $this->getFlash()->addMessage('error', 'Invalid User');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($userUUID)) {
            $deleteUser = Manager::table('users')
                ->where('uuid', $userUUID)
                ->delete();

            $this->getFlash()->addMessage('success', 'Users has been deleted successfully');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function userUpdate(Request $request, Response $response, array $args = [])
    {
        $postData = $request->getParsedBody();
        $userId = $request->getParsedBodyParam('id');

        if (empty($postData['first_name'])) {
            $this->getFlash()->addMessage('error', "First name field must not be empty");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['first_name'])) {
            if (mb_strlen($postData['first_name']) > 80 ) {
                $this->getFlash()->addMessage('error', "First name must be less than 80 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $postData['first_name'] = filter_var($postData['first_name'], FILTER_SANITIZE_STRING);
        }

        if (empty($postData['last_name'])) {
            $this->getFlash()->addMessage('error', "Last name field must not be empty");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['last_name'])) {
            if (mb_strlen($postData['last_name']) > 80 ) {
                $this->getFlash()->addMessage('error', "Last name must be less than 80 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $postData['last_name'] = filter_var($postData['last_name'], FILTER_SANITIZE_STRING);
        }

        if (empty($postData['email_address'])) {
            $this->getFlash()->addMessage('error', "Email address name field must not be empty");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['password'])) {
            if (strlen($postData['password']) >= 6) {
                if ($postData['password'] != $postData['confirm_password']) {
                    $this->getFlash()->addMessage("error", "Password doesn'\t match");
                    return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
                }
            } else {
                $this->getFlash()->addMessage('error', 'Password should be minimum six character');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            if (mb_strlen($postData['password']) > 80) {
                $this->getFlash()->addMessage('error', "Password must not be grater than 80 characters");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }
        }
        unset($postData['confirm_password']);
        $postData['password'] = password_hash($postData['password'], PASSWORD_BCRYPT);

        try {
            $updateUser = Manager::table('users')->where('id', $userId)->update($postData);

            if ($updateUser == true) {
                $this->getLogger()->info('User has been update successfully.');
                $this->getFlash()->addMessage('success', 'User has been update successfully !');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $this->getLogger()->error('User dose not update.');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));

        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getLogger()->debug($e->getTraceAsString());

            $this->getFlash()->addMessage("error", "User does not Update");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function changePassword(Request $request, Response $response) {
        $adminUUID = $request->getAttribute('adminUUID');
        $postData = $request->getParsedBody();

        if (empty($request->getParsedBodyParam('current_password'))) {
            $this->getFlash()->addMessage('error', "Current password is required.");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        $usersModel = Manager::table('users');
        $result = $usersModel->where('uuid', $adminUUID)->first();

        if (password_verify($request->getParsedBodyParam('current_password'), $result->password) == true) {
            if (empty($request->getParsedBodyParam('password'))) {
                $this->getFlash()->addMessage('error', "New password field is required.");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            if (!empty($request->getParsedBodyParam('password'))) {
                if (mb_strlen($request->getParsedBodyParam('password')) < 6) {
                    $this->getFlash()->addMessage('error', "Please provide at least 6 characters for password");
                    return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
                }
                if (mb_strlen($request->getParsedBodyParam('password')) > 80) {
                    $this->getFlash()->addMessage('error', "Password must not be grater than 80 characters");
                    return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
                }
            }

            if (empty($request->getParsedBodyParam('confirm_password'))) {
                $this->getFlash()->addMessage('error', "Confirm password field is required.");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            if ($request->getParsedBodyParam('password') != $request->getParsedBodyParam('confirm_password')) {
                $this->getFlash()->addMessage('error', "New password and confirm password does not matched!");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            unset($postData['current_password']);
            unset($postData['confirm_password']);
            $postData['password'] = password_hash($postData['password'], PASSWORD_BCRYPT);

            try {
                $updatePassword = Manager::table('users')->where('uuid', $adminUUID)->update($postData);

                if ($updatePassword == true) {
                    $this->getLogger()->info('Password has been update successfully.');
                    $this->getFlash()->addMessage('success', 'Password has been update successfully !');
                    return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
                }

                $this->getLogger()->error('Password dose not update.');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));

            } catch (\Exception $e) {
                $this->getLogger()->error($e->getMessage());
                $this->getLogger()->debug($e->getTraceAsString());

                $this->getFlash()->addMessage("error", "Password does not Update");
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

        }
        $this->getFlash()->addMessage('error', "Current password does not matched!");
        return $response->withRedirect($request->getServerParam('HTTP_REFERER'));

    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function uploadPicture(Request $request, Response $response)
    {
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
            return $response->withRedirect('/admin/profile');
        }else {
            try {
                $profilePicUpload = $this->uploadImages($image['tmp_name'], $image['name'], 'assets/images/profiles/', uniqid());

                if($profilePicUpload) {
                    $data['profile_pic'] =  "/" . $profilePicUpload['path'];
                    $changedProfilePic = Manager::table('users')
                        ->where('uuid', $request->getAttribute('adminUUID'))
                        ->update($data);

                    $_SESSION['auth']->profile_pic = $data['profile_pic'];

                    if($changedProfilePic == 1) {
                        $this->getFlash()->addMessage("success", "Profile picture has been changed");
                        return $response->withRedirect('/admin/profile');
                    }
                }
            } catch (\Exception $e) {
                $this->getLogger()->error($e->getMessage());
                $this->getLogger()->debug($e->getTraceAsString());

                $this->getFlash()->addMessage("error", "Failed to get user photo");
                return $response->withRedirect('/admin/profile');
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

}