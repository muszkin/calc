<?php
/**
 * Created by PhpStorm.
 * User: muszkin
 * Date: 20.10.16
 * Time: 14:24
 */

namespace AppBundle\Services;

use DreamCommerce\ShopAppstoreLib\Resource;
use Symfony\Component\Translation\Translator;

class Products
{
    private $trans;

    public function __construct(Translator $translator)
    {
        $this->trans = $translator;
    }

    public function addAttribute($client,$product_id,$attribute_id){

        $stock = new Resource\ProductStock($client);
        $product_id = $stock->get($product_id)->product_id;

        $request = new Resource\Product($client);


        $product = $request->get($product_id);

        $unit_id = $product->unit_id;
        $attributes = [];
        foreach ($product->attributes as $attr){
            foreach ($attr as $k => $v) {
                $attributes[$k] = $v;
            }
        }

        $package = 1;

        if ($product->stock->package) {
            $package = $product->stock->package;
        }

        $unitRes = new Resource\Unit($client);

        $uname = $unitRes->get($unit_id);

        $shop = new Resource\ApplicationConfig($client);
        $lang = $shop->get()->default_language_name;

        $uname = $uname->translations[$lang]->name;

        $attributes[$attribute_id] = $package.' '.$uname;

        $data = [
            "attributes" => $attributes
        ];

        try {
            $return = $request->put($product_id,$data);
        }catch(\DreamCommerce\ShopAppstoreLib\Exception\Exception $e){
            $parsed = json_decode($e->getMessage());
            $return = [$this->trans->trans('product',[],"AppBundle").$product_id.' '.$this->trans->trans($parsed->error,[],"AppBundle")];
        }

        return $return;
    }
}