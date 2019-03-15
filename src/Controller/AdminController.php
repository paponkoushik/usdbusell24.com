<?php

namespace USDBuySell\App\Controller;


use Illuminate\Database\Capsule\Manager;
use Previewtechs\PHPUtilities\UUID;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class AdminController
 * @package USDBuySell\App\Controller
 */
class AdminController extends AppController
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getMoneyProvider(Request $request, Response $response, array $args = [])
    {
        $page = $request->getQueryParam("page") ?: 1;

        if ($request->getQueryParam('page')) {
            $page = intval($request->getQueryParam('page'));
        }

        $perPage = 15;

        $providers = [];

        $queryData = $request->getQueryParams();

        $table = Manager::table('payment_providers')
            ->orderBy('id', 'desc');

        if (!empty($queryData['name'])) {
            $table = $table->where("name", "LIKE", "%{$queryData['name']}%");
        }

        if (!empty($queryData['referer_field_name'])) {
            $table = $table->where("referer_field_name", "LIKE","%{$queryData['referer_field_name']}%");
        }

        $result = $table->paginate($perPage, ['*'], 'providers_list', $page);

        foreach ($result as $item){
            $providers[] = (array) $item;
        }

        $providersList = [
            'providers' => $providers,
            'pagination' => [
                'perPage' => $perPage,
                'page' => $page,
                'hasMorePages' => $result->hasMorePages(),
                'total' => $result->total()
            ]
        ];



        //Pagination
        $total = $providersList['pagination']['total'];
        $maxPage = $total / $perPage;

        if($maxPage > 1 && is_float($maxPage)){
            $maxPage = intval($maxPage + 1);
        } else {
            $maxPage = intval($maxPage);
        }

        return $this->getView()->render($response, 'admin/money_provider/default.twig', [
            'message' => $this->getFlash()->getMessages(),
            'providers' => $providersList['providers'],
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
    public function moneyProviderCreateView(Request $request, Response $response, array $args = [])
    {
        return $this->getView()->render($response, 'admin/money_provider/default.twig', []);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function moneyProviderCreateProcess(Request $request, Response $response, array $args = [])
    {
        $postData =  $request->getParsedBody();

        $paymentProvidersModel = Manager::table('payment_providers');
        $nameExists = $paymentProvidersModel->where('name',
            $postData['name'])->select('name')->first();

        if ($nameExists != null) {
            $this->getFlash()->addMessage("error", "Money provider's name has already exist.");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (empty($request->getParsedBodyParam('name'))) {
            $this->getFlash()->addMessage('error', 'Provider\'s name must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['name'])) {
            if (mb_strlen($postData['name']) > 80) {
                $this->getFlash()->addMessage('error', 'Provider\'s name must be less than 80 characters');
                return $response->withRedirect('HTTP_REFERER');
            }

            $postData['name'] = filter_var($postData['name'], FILTER_SANITIZE_STRING);
        }

        $smalltr = strtolower($postData['name']);
        $split = preg_split("/[\s,]+/", $smalltr);
        $postData['slug'] = implode('-',$split);

        $slugModel = Manager::table('payment_providers');
        $slugExists = $slugModel->where('slug',
            $postData['slug'])->select('slug')->first();

        if ($slugExists != null) {
            while ($slugExists != null)
            {
                $postData['slug'] = $postData['slug'] . rand(10, 1000);
            }
        }

        if (empty($request->getParsedBodyParam('referer_field_name'))) {
            $this->getFlash()->addMessage('error', 'Referer name must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['referer_field_name'])) {
            if (mb_strlen($postData['referer_field_name']) > 120) {
                $this->getFlash()->addMessage('error', 'Referer name must be less than 80 characters');
                return $response->withRedirect('HTTP_REFERER');
            }

            $postData['referer_field_name'] = filter_var($postData['referer_field_name'], FILTER_SANITIZE_STRING);
        }

        if (empty($request->getParsedBodyParam('icon_url'))) {
            $this->getFlash()->addMessage('error', 'Icon URL field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }
        /*
        if (!empty($postData['icon_url'])) {
            if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",
                $postData['icon_url'])) {
                $this->getFlash()->addMessage('error', 'icon Url is not valid URL');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }
        }*/

        if (empty($request->getParsedBodyParam('exchanged_request'))) {
            $this->getFlash()->addMessage('error', 'Referer name must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['exchanged_request'])) {
            if (mb_strlen($postData['exchanged_request']) > 120) {
                $this->getFlash()->addMessage('error', 'Referer name must be less than 120 characters');
                return $response->withRedirect('HTTP_REFERER');
            }

            $postData['exchanged_request'] = filter_var($postData['exchanged_request'], FILTER_SANITIZE_STRING);
        }

        if (empty($request->getParsedBodyParam('description'))) {
            $this->getFlash()->addMessage('error', 'Description field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['description'])) {
            $postData['description'] = filter_var($postData['description'], FILTER_SANITIZE_STRING);
        }

        if (empty($request->getParsedBodyParam('buy_rate'))) {
            $this->getFlash()->addMessage('error', 'Buy rate field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (empty($request->getParsedBodyParam('sell_rate'))) {
            $this->getFlash()->addMessage('error', 'Sell rate field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (empty($request->getParsedBodyParam('total_reserves'))) {
            $this->getFlash()->addMessage('error', 'Total reserves field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['total_reserves'])) {
            if (!preg_match('/^[0-9]*$/', $postData['total_reserves'])) {
                $this->getFlash()->addMessage('error', 'Total reserves capital must be number.');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $postData['total_reserves'] = intval($postData['total_reserves']);
        }

        $postData['buy_rate'] = floatval($postData['buy_rate']);
        $postData['sell_rate'] = floatval($postData['sell_rate']);
        $postData['uuid'] = UUID::v4();

        try {
            $providerCreated = Manager::table('payment_providers')->insert($postData);

            if ($providerCreated == true) {
                $this->getLogger()->info('A new money provider has been created.');
                $this->getFlash()->addMessage('success', 'Money prover has added successfully !');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $this->getLogger()->error('A new money provider does not created.');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));

        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getLogger()->debug($e->getTraceAsString());

            $this->getFlash()->addMessage("error", "Money provider does not added");
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function moneyProviderDelete(Request $request, Response $response, array $args = [])
    {
        $providerUUID = $request->getAttribute('providerUUID');

        if (empty($providerUUID)) {
            $this->getFlash()->addMessage('error', 'Invalid Provider');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($providerUUID)) {
            $deleteProvider = Manager::table('payment_providers')
                ->where('uuid', $providerUUID)
                ->delete();

            if ($deleteProvider == 1) {
                $this->getFlash()->addMessage('success', 'Provider has been deleted successfully');
                $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }
        }

        $this->getFlash()->addMessage('error', 'Invalid Provider');
        return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function moneyProviderUpdateProcess(Request $request, Response $response, array $args = [])
    {

        $postData = $request->getParsedBody();

        if (empty($request->getParsedBodyParam('name'))) {
            $this->getFlash()->addMessage('error', 'Provider\'s name must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['name'])) {
            if (mb_strlen($postData['name']) > 80) {
                $this->getFlash()->addMessage('error', 'Provider\'s name must be less than 80 characters');
                return $response->withRedirect('HTTP_REFERER');
            }
            $postData['name'] = filter_var($postData['name'], FILTER_SANITIZE_STRING);
        }

       /* $smalltr = strtolower($postData['name']);
        $split = preg_split("/[\s,]+/", $smalltr);
        $postData['slug'] = implode('-',$split);

        $slugModel = Manager::table('payment_providers');
        $slugExists = $slugModel->where('slug',
            $postData['slug'])->select('slug')->first();*/

        /*if ($slugExists != null) {
            while ($slugExists != null)
            {
                $postData['slug'] = $postData['slug'] . rand(10, 1000);
            }
        }*/

        if (empty($request->getParsedBodyParam('referer_field_name'))) {
            $this->getFlash()->addMessage('error', 'Referer name must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['referer_field_name'])) {
            if (mb_strlen($postData['referer_field_name']) > 120) {
                $this->getFlash()->addMessage('error', 'Referer name must be less than 80 characters');
                return $response->withRedirect('HTTP_REFERER');
            }

            $postData['referer_field_name'] = filter_var($postData['referer_field_name'], FILTER_SANITIZE_STRING);
        }

        if (empty($request->getParsedBodyParam('icon_url'))) {
            $this->getFlash()->addMessage('error', 'Icon URL field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        /*if (!empty($postData['icon_url'])) {
            if (!preg_match( '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' , $postData['icon_url'])){
                $this->getFlash()->addMessage('error', 'Icon URL is not valid');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }
        }*/

        if (empty($request->getParsedBodyParam('exchanged_request'))) {
            $this->getFlash()->addMessage('error', 'Referer name must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['exchanged_request'])) {
            if (mb_strlen($postData['exchanged_request']) > 120) {
                $this->getFlash()->addMessage('error', 'Referer name must be less than 120 characters');
                return $response->withRedirect('HTTP_REFERER');
            }

            $postData['exchanged_request'] = filter_var($postData['exchanged_request'], FILTER_SANITIZE_STRING);
        }

        if (empty($request->getParsedBodyParam('description'))) {
            $this->getFlash()->addMessage('error', 'Description field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['description'])) {
            $postData['description'] = filter_var($postData['description'], FILTER_SANITIZE_STRING);
        }

        if (empty($request->getParsedBodyParam('buy_rate'))) {
            $this->getFlash()->addMessage('error', 'Buy rate field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }


        if (empty($request->getParsedBodyParam('sell_rate'))) {
            $this->getFlash()->addMessage('error', 'Sell rate field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (empty($request->getParsedBodyParam('total_reserves'))) {
            $this->getFlash()->addMessage('error', 'Total reserves field must not be empty!');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }

        if (!empty($postData['total_reserves'])) {
            if (!preg_match('~^((?:\+|-)?[0-9]+)$~', $postData['total_reserves'])) {
                $this->getFlash()->addMessage('error', 'Total reserves capital must be number.');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $postData['buy_rate'] = floatval($postData['buy_rate']);
            $postData['sell_rate'] = floatval($postData['sell_rate']);
            $postData['total_reserves'] = floatval($postData['total_reserves']);
        }

        try {
            $providerUpdate = Manager::table('payment_providers')
                ->where('id', $postData['id'])
                ->update($postData);

            if ($providerUpdate == true) {
                $this->getLogger()->info('Money provider has been updated.');
                $this->getFlash()->addMessage('success', 'Money prover has been update successfully !');
                return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
            }

            $this->getLogger()->error('Money provider does not updated.');
            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));

        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getLogger()->debug($e->getTraceAsString());

            return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getExchangeRequest(Request $request, Response $response, array $args = [])
    {
        $this->getFlash()->getMessages();
        return $this->getView()->render($response, 'admin/exchange_request/default.twig', []);
    }

}