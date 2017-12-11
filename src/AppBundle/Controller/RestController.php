<?php
/**
 * Created by PhpStorm.
 * User: muszkin
 * Date: 05.12.16
 * Time: 14:07
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RestController extends Controller
{
    /**
     * @Route("/productInfo/{shop_id}/{product_id}",name="productInfo")
     */
    public function productInfo($shop_id,$product_id){
        $shop = $this->getDoctrine()->getRepository('BillingBundle:Shop')->find($shop_id);

        if (!is_null($shop)){
            $token = $shop->getToken()->getAccessToken();
            $c = curl_init($shop->getShopUrl().'/webapi/rest/products/'.$product_id);
            curl_setopt_array($c,[
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => ["Authorization: Bearer ".$token],
            ]);
            $product = curl_exec($c);

            return new JsonResponse($product);
        }

        return new JsonResponse(["error"=>"no shop found"]);
    }
}