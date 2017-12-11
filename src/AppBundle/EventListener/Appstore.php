<?php
/**
 * Created by PhpStorm.
 * User: muszkin
 * Date: 07.10.16
 * Time: 16:29
 */

namespace AppBundle\EventListener;

use DreamCommerce\ShopAppstoreBundle\EventListener\AppstoreListener;
use DreamCommerce\ShopAppstoreBundle\Utils\ShopChecker;
use DreamCommerce\ShopAppstoreBundle\Utils\TokenRefresher;
use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception;
use DreamCommerce\ShopAppstoreBundle\Event\Appstore\InstallEvent;
use DreamCommerce\ShopAppstoreBundle\Event\Appstore\UninstallEvent;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Model\TokenInterface;
class Appstore extends AppstoreListener {
    /**
     * handle installation event
     * @param InstallEvent $event
     * @return bool
     * @throws Exception
     */
    public function onInstall(InstallEvent $event){
        // extract shop entity from event
        $shop = $this->getShopByEvent($event);
        $update = false;
        // already installed, skip
        if($shop){
            if ($shop->getInstalled()) {
                return false;
            } else {
                $update = true;
            }
        }
        $shopChecker = new ShopChecker();
        try {
            $params = $event->getPayload();
            $app = $event->getApplication();
            $url = $shopChecker->getRealShopUrl($params['shop_url']);
            if(!$url){
                $url = $params['shop_url'];
                //throw new Exception($url.' - Cannot determine real URL for: '.$params['shop_url']);
            }
            // perform client instantiation
            $client = Client::factory(
                Client::ADAPTER_OAUTH,
                [
                    'entrypoint'=>$url,
                    'client_id'=>$app['app_id'],
                    'client_secret'=>$app['app_secret'],
                    'auth_code'=>$params['auth_code'],
                    'skip_ssl'=>$this->skipSsl,
                    'user_agent'=>$app['user_agent']
                ]
            );
            // and get tokens
            $token = $client->authenticate(true);
        }catch(Exception $ex){
            // allow error to be logged
            throw $ex;
        }
        // region shop
        if ($update) {
            $shopModel = $shop;
        } else {
            /**
             * @var $shopModel ShopInterface
             */
            $shopModel = $this->objectManager->create('DreamCommerce\ShopAppstoreBundle\Model\ShopInterface');
        }
        $shopModel->setApp($event->getApplicationName());
        $shopModel->setName($params['shop']);
        $shopModel->setShopUrl($url);
        $shopModel->setVersion($params['application_version']);
        $shopModel->setInstalled(true);
        $this->objectManager->save($shopModel, false);
        // endregion
        // region token
        if ($update) {
            $tokenModel = $shop->getToken();
        } else {
            /**
             * @var $tokenModel TokenInterface
             */
            $tokenModel = $this->objectManager->create('DreamCommerce\ShopAppstoreBundle\Model\TokenInterface');
        }
        $tokenModel->setAccessToken($token['access_token']);
        $tokenModel->setRefreshToken($token['refresh_token']);
        $expiresAt = new \DateTime();
        $expiresAt->add(\DateInterval::createFromDateString($token['expires_in'].' seconds'));
        $tokenModel->setExpiresAt($expiresAt);
        $tokenModel->setShop($shopModel);
        $this->objectManager->save($tokenModel);
        // endregion
    }
    /**
     * uninstall shop from application
     * @param UninstallEvent $event
     * @return bool
     */
    public function onUninstall(UninstallEvent $event){
        $shop = $this->getShopByEvent($event);
        if(!$shop || !$shop->getInstalled()){
            return false;
        }
        $shop->setInstalled(false);
        $this->objectManager->save($shop);
    }
}