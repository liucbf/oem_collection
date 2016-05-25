<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Collection_stressmaq extends CI_Controller {

    private $_base_fields;
    private $_other_fields;

    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
        $this->load->helper('mycollection');

        $this->_base_fields = array('Catalog', 'Size', 'Price', 'Availability', 'Title', 'DatasheetTitle1', 'Description', 'Host', 'Specificity'
            , 'Target_Species', 'Known_Cross_Reactivity', 'Product_Type', 'Kit_Type', 'Clonality', 'Application', 'Species_of_Origin', 'Physical_State', 'Shipping_condition'
            , 'Format', 'PSC_Type', 'PSC_Format', 'Label', 'FP_Value', 'Concentration', 'Buffer', 'Reconstitution_Volume', 'Reconstitution_Buffer', 'Preservative', 'GeneName'
            , 'Stabilizer', 'DatasheetTitle2', 'Background', 'Immunogen', 'Storage_Condition', 'Application_Note', 'Purity', 'Disclaimer_Note', 'image1', 'image2'
        );
    }

    public function index()
    {

        $contents = file_get_contents("C:/Users/li_hao/Desktop/oem_strssq/1.txt");
        $product_arr = explode('http://', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $url = 'http://' . trim($v);
            //$temp_product_arr[] = self::get_base_data(false,$url );
            $temp_product_arr[] = $this->ck_offline_product($url);
        }
      
        $export_arr = array(
            'url' => 'url',
            'is_pub' => 'is_pub'
        );
        for ($i = 1; $i < 10; $i++)
        {
            $export_arr['Image_' . $i] = 'Image_' . $i;
            $export_arr['Type_' . $i] = 'Type_' . $i;
            $export_arr['Legend_' . $i] = 'Legend_' . $i;
        }
        export_excel($export_arr, $temp_product_arr, 'oemstressq' . date("YmdHis"), "C:\Users\li_hao\Desktop\oem_strssq/");
    }
    //
    public function get_citation()
    {
         $contents = file_get_contents("C:/Users/li_hao/Desktop/oem_strssq/need_citation.txt");
        $product_arr = explode('http://', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $url = 'http://' . trim($v);
            $temp_product_arr[] = $this->get_base_citation($url);
        }
         $export_arr = array(
            'url' => 'url',
        );
        for ($i = 1; $i < 15; $i++)
        {
            $export_arr['citation_' . $i] = 'citation_' . $i;
        }
        export_excel($export_arr, $temp_product_arr, 'oemstressq_citiation' . date("YmdHis"), "C:\Users\li_hao\Desktop\oem_strssq/");
    }

    public function get_base_data($id = false, $url = "http://www.stressmarq.com/Products/Antibodies/SMC-137D.aspx")
    {
        $product_get_url = $url;
        $product = array(); //产品总数组
        $product['url'] = $product_get_url;
        $content = file_get_contents($product_get_url);
        $content = compress_html($content);
        //采集base图片
        if ($content)
        {
            $preg_arr = self::set_base_rules('image_base', $content);
            if ($preg_arr)
            {
                preg_match_all('#<img alt="(.*)"src="(.*)"style="(.*)"\/>#iUs', $preg_arr[1][0], $img_arr);
                $img['image'] = $img_arr[2];
                if (count($img_arr[2]) == 0)
                {
                    preg_match_all('#<img src="(.*)"style="(.*)"alt="(.*)"\/>#iUs', $preg_arr[1][0], $img_arr);
                    $img['image'] = $img_arr[1];
                }
                if (count($img_arr[1]) == 0)
                {
                    preg_match_all('#<img alt="(.*)"style="(.*)"src="(.*)"\/>#iUs', $preg_arr[1][0], $img_arr);
                    $img['image'] = $img_arr[3];
                }
                $tmp_legend = preg_replace("#<a[^>]*>(.*?)</a>#is", "@#", $preg_arr[1][0]);
                $re_leg = preg_replace("#<img[^>]*>#is", "@#", $tmp_legend);
                $b = trim($re_leg, "@#");
                $legend_arr = explode("@#", $b);
                $img['legend'] = $legend_arr;
                if (count($img['image']) > 0)
                {
                    foreach ($img['image'] as $k => $v)
                    {
                        $product['Image_' . ($k + 1)] = $v;
                        $product['Legend_' . ($k + 1)] = $img['legend'][$k];
                    }
                }
            }
        }
        //采集其他靶点的catalog对应的图片
//          $preg_arr_b_d_2 = self::set_base_rules('image_b_d_2', $content);
//          preg_match_all( '#<div class="conjugate-desc (.*)"id="(.*)"style="display: none;">#iUs', $preg_arr_b_d_2[1][0] ,$b_d_2_arr );
//          //catalog
//          $b_d_2_all = $b_d_2_arr[2];
//          $product['catalog'] = $b_d_2_all;
//          //去除垃圾信息
//          $b_d_2_tmp = preg_replace("#<h3[^>]*>(.*?)</h3>#is", "", $preg_arr_b_d_2[1][0]);
//          $b_d_2_tmp =preg_replace("#<strong[^>]*>(.*?)</strong>#is", "", $b_d_2_tmp);
//          $b_d_2_tmp =preg_replace("#<br[^>]*>#is", "", $b_d_2_tmp);
//          $b_d_2_tmp =preg_replace("#<p[^>]*> </p>#is", "", $b_d_2_tmp);
//          $b_d_2_tmp =preg_replace("#<a[^>]*>(.*?)</a>#is", "", $b_d_2_tmp);
//          foreach ($b_d_2_all as $k=>$catalog )
//          {
//              $b_d_2_tmp_arr = $b_d_2_tmp_arr_2 = array();
//              preg_match_all( '#<div class="conjugate-desc (.*)"id="'.$catalog.'"style="display: none;"><p>(.*)</p></div>#iUs', $b_d_2_tmp ,$b_d_2_tmp_arr );
//              preg_match( '#<img alt=""height="(.*)"src="(.*)"width="(.*)"/>#iUs', $b_d_2_tmp_arr[2][0] ,$b_d_2_tmp_arr_2 );
//             
//              if(!$b_d_2_tmp_arr_2){
//                  preg_match( '#<img alt="(.*)"src="(.*)"style="(.*)"/>#iUs', $b_d_2_tmp_arr[2][0] ,$b_d_2_tmp_arr_2 );
//              }
//              $b_d_2_img[$k]['legend'] =strip_tags($b_d_2_tmp_arr[2][0]);
//              $b_d_2_img[$k]['img'] = $b_d_2_tmp_arr_2[2];
//          }
//         $product['b_d_2_img'] = $b_d_2_img;
//        }
//      foreach( $product['catalog'] as $k=>$v)
//      {
//          echo $v.",".$b_d_2_img[$k]['legend'].","."图片".'\r\n<br>';
//          file_put_contents("C:\Users\li_hao\Desktop\oem_strssq/".$v.".png",  file_get_contents($b_d_2_img[$k]['img']));
//      }
        return $product;
    }

    public function set_base_rules($param = NULL, $content)
    {
        $preg = array();
        $preg['image_b_d_2'] = '#<div id="2">(.*)<\/div><div id="3">#iUs';

        $preg['image_base'] = '#<div id="3">(.*)<\/div><div id="4">#iUs';
        $preg['image_base_1'] = '#<div id="2">(.*)<\/div><div id="3">#iUs';

        $preg['image_base_each'] = '#<img alt="(.*)"src="(.*)"style="(.*)"/>#iUs';
        $data = array();
        if ($param && $content)
        {
            preg_match_all($preg[$param], $content, $pre_arr);
            return $pre_arr;
        }
        return $data;
    }

    //判断产品下架，页面没有tabs
    public function ck_offline_product($url)
    {
        $product = array(); //产品总数组
        $product['url'] = $url;
        $content = file_get_contents($url);
        $content = compress_html($content);
        //采集base图片
        if ($content)
        {
            preg_match_all('#<div class="tabs">(.*)<\/div>#iUs', $content, $arr);
            if (count($arr[1]) == 0)
            {
                $product['is_pub'] = 'offline';
            }
        }

        return $product;
    }

    public function get_base_citation($url = "http://www.stressmarq.com/Products/Antibodies/SMC-104B.aspx")
    {
        $product = array(); //产品总数组
        $product['url'] = $url;
        $content = file_get_contents($url);
        $content = compress_html($content);

        //采集base图片
        if ($content)
        {
            preg_match_all('#<div id="3">(.*)<\/div>#iUs', $content, $citation_arr);
            //preg_match_all('#<div id="4">(.*)<\/div>#iUs', $content, $citation_arr);
            $arr = explode("<br />&nbsp;<br />", $citation_arr[1][0]);
            if ($arr)
            {
                foreach ($arr as $k=>$v)
                {
                    $product["citation_".($k+1)] = $v;
                }
            }
        }

        return $product;
    }

}

?>