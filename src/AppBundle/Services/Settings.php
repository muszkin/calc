<?php
/**
 * Created by PhpStorm.
 * User: muszkin
 * Date: 18.10.16
 * Time: 10:27
 */

namespace AppBundle\Services;

use DreamCommerce\ShopAppstoreBundle\Utils\TokenRefresher\Exception;
use DreamCommerce\ShopAppstoreLib\Resource;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Translator;


class Settings
{
    private $trans;

    public function __construct(Translator $translation)
    {
        $this->trans = $translation;
    }

    public function getShopSettings($client){
        $shopInfo = new Resource\ApplicationConfig($client);
        return $shopInfo->get();
    }

    public function getAttributes($client){
        $attribute = new Resource\Attribute($client);
        $attribute->limit(50);
        $attributes = $attribute->get();
        $return = [];
        if ($attributes->pages > 1){
            for ($x=1;$x<= $attributes->pages;$x++){
                $attribute->page($x);
                $return = array_merge($return,$attribute->get()->getArrayCopy());
            }
        }else{
            $return = $attributes->getArrayCopy();
        }
        $data = [];
        $groups = $this->getAttributeGroups($client);
        foreach ($return as $ret){
            $data[$groups[$ret->attribute_group_id].' - '.$ret->name] = $ret->attribute_id;
        }
        return $data;
    }

    public function getAttributeGroups($client){
        $attributeGroup = new Resource\AttributeGroup($client);
        $attributeGroup->limit(50);
        $attributeGroups = $attributeGroup->get();
        $return = [];
        if ($attributeGroups->pages > 1){
            for ($x=1;$x<= $attributeGroups->pages;$x++){
                $attributeGroup->page($x);
                $return = array_merge($return,$attributeGroup->get()->getArrayCopy());
            }
        }else{
            $return = $attributeGroups->getArrayCopy();
        }
        $data = [];
        foreach ($return as $ret){
            $data[$ret->attribute_group_id] = $ret->name;
        }
        return $data;
    }

    public function createAttribute($client,$name,\AppBundle\Entity\Settings $settings){
        $attributeGroup = new Resource\AttributeGroup($client);
        $attributeGroup->limit(1);
        $attributeGroup->filters([
            "name" => "Calculator App Group"
        ]);
        $attributeGroups = $attributeGroup->get();
        if (0 == $attributeGroups->count){
            $id = $this->createAttributeGroup($client,"Calculator App Group");
        }else{
            $id = $attributeGroups->getArrayCopy()[0]->attribute_group_id;
        }

        $attribute = [
            "name" => $name,
            "attribute_group_id" => $id,
            "order" => 1,
            "type" => 0,
            "active" => 1,
        ];

        $request = new Resource\Attribute($client);

        try{
            $id = $request->post($attribute);
        }catch (Exception $e){
            $id = null;
        }

        return $id;
    }

    public function createAttributeGroup($client,$name){
        $shop = new Resource\ApplicationConfig($client);
        $lang_id = $shop->get()->default_language_id;

        $attributeGroup = [
            "name" => $name,
            "lang_id" => $lang_id,
            "active" => 1,
            "filters" => 0,
            "categories" => $this->getAllCategories($client),
        ];

        $request = new Resource\AttributeGroup($client);
        try{
            $id = $request->post($attributeGroup);
        }catch(Exception $e){
            $id = null;
        }
        return $id;
    }

    public function getAllCategories($client){
        $categories = new Resource\Category($client);
        $categories->limit(50);
        $return = $categories->get();
        $data = [];
        if ($return->pages > 1){
            for ($x = 1;$x <= $return->pages;$x++){
                $categories->page($x);
                foreach($categories->get()->getArrayCopy() as $category){
                    $data[] = $category->category_id;
                }
            }
        }else{
            foreach($return as $category){
                $data[] = $category->category_id;
            }
        }
        return $data;
    }

    public function prepareInfo($settings,$client){
        if ($settings != null) {
            $name = $settings->getAttribute();
            $active = $settings->getActive();
            $dtext = $settings->getDtext();

            $current = new \stdClass();
            $current->name = $name;
            $current->active = $active;
            $current->dtext = $dtext;
        }else{
            $current = null;
        }
        return $current;
    }

    public function getAttributeName($client,$id){
        $request = new Resource\Attribute($client);
        return $request->get($id)->name;
    }

    public function manageMetafield($client,$settings,$translation){
        if (null == $settings){
            return null;
        }
        $namespace = 'calculatorapp';
        $key = 'script';
        $value = $this->createCalculator($settings->getShop(),$settings->getAttributeId(),$settings->getAttribute(),$settings->getTypeDesc(),$settings->getDtext(),$settings->getType2(),$translation);

        $m = new Resource\Metafield($client);
        $m->filters([
            'namespace' => $namespace
        ]);

        $result = $m->get('system');

        $v = new Resource\MetafieldValue($client);

        if($result->count == 0){
            $data = array(
                'namespace' => $namespace,
                'key' => $key,
                'type' => Resource\Metafield::TYPE_BLOB
            );

            $id = $m->post('system', $data);

            $data = array(
                'metafield_id' => $id,
                'value' => $value
            );

            $v->post($data);
        }else{
            $id = $result[0]['metafield_id'];

            $metafieldvalue_id =$v->filters([
                "metafield_id" => $id
            ])->get()[0]['value_id'];


            $v->put($metafieldvalue_id, [
                'value'=> $value
            ]);
        }
    }

    private function createCalculator($shop_id,$attribute_id,$attribute,$type_desc,$dtext,$type2,$translation){
        if ($translation == 'pl_PL' ) {
            $other = 'lub';
        }else{
            $other = 'other';
        }

        $finder = new Finder();
        $files = $finder->files()->in(__DIR__.'/..')->name('meta.js');
        $script = '<script type="text/javascript">';
        foreach ($files as $file){
            $file->openFile('r');
            $script .= $file->getContents();
        }
        $script .= '</script>';

        $script = sprintf($script,$shop_id,$attribute_id,$attribute,$dtext,$other,$type_desc,$type2);
        return $script;
    }
}
