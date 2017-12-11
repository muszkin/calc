<?php
/**
 * Created by PhpStorm.
 * User: muszkin
 * Date: 07.10.16
 * Time: 15:45
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Settings;
use AppBundle\Form\SettingsType;
use DreamCommerce\ShopAppstoreBundle\Controller\ApplicationController;
use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception;
use DreamCommerce\ShopAppstoreLib\Resource;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IndexController extends ApplicationController
{
    /**
     * @Route("/",name="index")
     */
    public function indexAction(Request $request){

        $setting = $this->get('settings');
        $attributes = $setting->getAttributes($this->client);

        $session = $request->getSession();
        if (!$session->get('locale')) {
            $session->set('locale', $request->getLocale());
        }

        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->findOneBy([
            "shop" => $this->shop
        ]);

        $form = $this->createForm(
            SettingsType::class,null, [
                'data' => $settings,
                'attributes'=>$attributes,
                'shop' => $this->shop,
                'attr' => [
                    'novalidate' => 'novalidate'
                ]
        ]);

        $translation = $request->get('translations');

        $setting->manageMetafield($this->client,$settings,$translation);

        $current_setting = $setting->prepareInfo($settings,$this->client);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $data = $form->getData();


            if ($settings == null){
                $settings = new Settings();
                $settings->setShop($this->shop);
            }
            $settings->setTypeDesc($data->getTypeDesc());
            $settings->setType2($data->getType2());
	        $settings->setActive($data->getActive());
            $settings->setDtext($data->getDtext());

            $attribute_id = $data->getAttributeId();

            if ( 'new' == $data->getAttributeId() && null !== $data->getNewAttribute()){
                $name = $data->getNewAttribute();
                $attribute_id = $setting->createAttribute($this->client,$name,$settings);
                $settings->setNewAttribute($attribute_id);
            }else{
                $settings->setNewAttribute(null);
            }

            $settings->setAttributeId($attribute_id);
            $settings->setAttribute($setting->getAttributeName($this->client,$settings->getAttributeId()));

            $em = $this->getDoctrine()->getManager();

            $em->persist($settings);
            $em->flush();

            $translation = $request->get('translations');

            $setting->manageMetafield($this->client,$settings,$translation);

            $attributes = $setting->getAttributes($this->client);

            $form = $this->createForm(
                SettingsType::class,null, [
                'data' => $settings,
                'attributes'=>$attributes,
                'shop' => $this->shop,
                'attr' => [
                    'novalidate' => 'novalidate'
                ]
            ]);
        }

        return $this->render('AppBundle:admin:config.html.twig',['form'=>$form->createView(),'current'=>$current_setting]);
    }

    /**
     * @Route("/productlist",name="productlist")
     */
    public function productListAction(Request $request){
        $products = json_encode($request->get('id'));

        $session = $request->getSession();
        if (!$session->get('locale')) {
            $session->set('locale', $request->getLocale());
        }

        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->findOneBy([
            "shop" => $this->shop,
        ]);

        return $this->render('AppBundle:admin:productlist.html.twig',['ids'=>$products,'settings' => $settings]);
    }

    /**
     * @Route("/productAdd",name="productadd")
     */
    public function productAttributeAddAction(Request $request){
        $session = $request->getSession();
        if (!$session->get('locale')) {
            $session->set('locale', $request->getLocale());
        }

        $product_id = $request->get('product_id');

        $products = $this->get('products');

        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->findOneBy([
            "shop" => $this->shop,
        ]);
        $result = $products->addAttribute($this->client,$product_id,$settings->getAttributeId());
        $return["success"] = ((!is_array($result))?1:0);
        if (is_array($result)) {
            $return['errors'] = $result[0];
        }

        return new JsonResponse($return);
    }


}
