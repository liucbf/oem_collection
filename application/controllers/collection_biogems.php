<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Collection_biogems extends CI_Controller {

    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
    }

    //2014-11-3 start
    //导出数据excel 根据catalog
    public function get_file_data_by_catalog()
    {
        // exit('select file');
        $name = 'catalog';
        $fileName = 'D:/wamp/www/test/biogems/' . $name . '.xlsx';
        $product_arr = $export_data = array();
        //open file
        $product_arr = reade_excel($fileName, '.xlsx');
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $each_one = trim($v[0]);
            $export_data[] = self::get_base_info_catalog($each_one);
        }
        $export_arr = array(
            'catalog' => 'catalog',
            'description' => 'description',
            'url' => 'url',
            'true_url' => 'true_url',
        );
        export_excel($export_arr, $export_data, 'everest Data_' . $name . '__' . time());
        // create_cvs( $export_arr ,$export_data,$file_name = 'D:/wamp/www/test/evers/'.$name.'.csv');      
    }

    public function get_base_info_catalog($keyword_catalog = '120-14')
    {
        $product = array();
        $web_site_url = "http://www.bio-gems.com";
        $url = 'http://www.bio-gems.com/catalogsearch/result/?q=' . $keyword_catalog;

        $content = file_get_contents($url);
        $content = compress_html($content);
        $product['catalog'] = $keyword_catalog;

        $preg = array();
        //title 与 详细页url
        $preg['catalog_relate'] = '#<td class="prodlist-prod_cat_num"><div>(.*)</div></td>#iUs';
        $preg['prodname_relate'] = '#<td class="prodlist-prodname">(.*)<a(.*)href="(.*)"(.*)>(.*)</a>(.*)</td>#iUs';
        preg_match_all($preg['catalog_relate'], $content, $pre_arr);
        preg_match_all($preg['prodname_relate'], $content, $pre_arr_produc);


        if (array_search($keyword_catalog, $pre_arr[1]) == NULL)
        {
            $product['catalog'] = $pre_arr[1][0];
            $product['description'] = $pre_arr_produc[5][0];
            $product['url'] = $pre_arr_produc[3][0];
            $product['true_url'] = 0;
        }
        else
        {
            $product['catalog'] = $keyword_catalog;
            $product['description'] = $pre_arr_produc[5][array_search($keyword_catalog, $pre_arr[1])];
            $product['url'] = $pre_arr_produc[3][array_search($keyword_catalog, $pre_arr[1])];
            $product['true_url'] = 1;
            //if corrent then collection data
//                       $inner_html = compress_html( file_get_contents($product['url']));
//                       
//                       $preg['price_relate'] = '#<tr><td>(.*)</td><td class="a-right"><div class="price-box">(.*)</div></td><td class="a-center">(.*)</td></tr>#iUs';
//                       preg_match_all( $preg['price_relate'],$inner_html,$pre_arr_price ); 
//                       var_dump($pre_arr_price);
        }


        return $product;
    }

    public function get_data_from_url()
    {
        $name = 'left';
        $fileName = 'D:/wamp/www/test/biogems/' . $name . '.xls';
        $product_arr = $export_data = array();
        //open file
        $product_arr = reade_excel($fileName, '.xls');
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $each_one = trim($v[1]);
            $export_data[] = self::_get_data_from_url($each_one, $v[0], $v[2]);
        }
        $export_arr = array(
            'catalog' => 'catalog',
            'product_name' => 'product_name',
            'url' => 'url',
            'size1' => 'size1',
            'price1' => 'price1',
            'size2' => 'size2',
            'price2' => 'price2',
        );
        export_excel($export_arr, $export_data, 'biogems Data_' . $name . '__' . time());
    }

    private function _get_data_from_url($url = "http://www.bio-gems.com/recombinant-human-murine-rat-activin-a-e-coli-derived.html", $catalog, $description)
    {
        $content = file_get_contents($url);
        $content = compress_html($content);
        //if corrent then collection data
        $preg['price_relate'] = '#<tbody><tr><td>(.*)</td><td class="a-right"><div class="price-box">(.*)</div></td><td class="a-center">(.*)</td></tr><tr><td>(.*)</td><td class="a-right"><div class="price-box">(.*)</div></td><td class="a-center">(.*)</td></tr>#iUs';
        $preg['price_one'] = '#<tbody><tr><td>(.*)</td><td class="a-right"><div class="price-box">(.*)</div></td><td class="a-center">(.*)</td></tr>#iUs';
        $product['catalog'] = $catalog;
        $product['product_name'] = $description;
        $product['url'] = $url;
        preg_match($preg['price_relate'], $content, $pre_arr_price);
        preg_match($preg['price_one'], $content, $pre_one_price);
        if ($pre_arr_price || $pre_one_price)
        {
            $product['size1'] = $pre_one_price[1];
            $product['price1'] = strip_tags($pre_one_price[2]);
            $product['size2'] = $pre_arr_price[4];
            $product['price2'] = strip_tags($pre_arr_price[5]);
        }


        return $product;
    }

}
