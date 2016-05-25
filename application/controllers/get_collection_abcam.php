<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Get_collection_abcam extends CI_Controller {

    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
        $this->load->helper('mycollection');
    }

    public function get_product_data()
    {
        $name = "left-cardiovascular";
        $fileName = 'C:\Users\li_hao\Desktop\2015abcam\step3_product_detail/' . $name . '.txt';
        $filecsv = 'C:\Users\li_hao\Desktop\2015abcam\step3_product_detail/' . $name . '.csv';
        $product_arr = $export_data = array();
        //open file
        $contents = file_get_contents($fileName);
        $product_arr = explode('http', $contents);
        unset($product_arr[0]);
        $export_row = array('child_url', 'title', 'product_name', 'species', 'applicaiton', 'alternative_names', 'clonality', 'isotype', 'uniport_id', 'human_uniport_id','all_uniport_id','abreview');
        if (!file_exists($filecsv))
        {
            file_put_contents($filecsv, implode(',', $export_row)."\r\n");
        }
        $f = fopen($filecsv, 'a+');
        foreach ($product_arr as $k => $v)
        {
            $each_one = 'http' . trim($v);
            if (!fputcsv($f, array_values(self::get_base_data_from($each_one))))
            {
                echo ' [  ' . $each_one . ' failed!  row:' . ($k + 1) . " ] ";
            }
        }
        fclose($f);
        echo ' ===finished! ===' . date("Y-m-d H:m");
    }
    public function get_reviews()
    {
        $name = "carderverdr";
        $fileName = 'C:\Users\li_hao\Desktop\2015abcam\step4/' . $name . '.txt';
        $filecsv = 'C:\Users\li_hao\Desktop\2015abcam\step4/' . $name . '.csv';
        $product_arr = $export_data = array();
        //open file
        $contents = file_get_contents($fileName);
        $product_arr = explode('http', $contents);
        unset($product_arr[0]);
        $export_row = array('child_url','abreview');
        if (!file_exists($filecsv))
        {
            file_put_contents($filecsv, implode(',', $export_row)."\r\n",FILE_APPEND);
        }
        $f = fopen($filecsv, 'a+');
        foreach ($product_arr as $k => $v)
        {
            $tmp_arr = $tmp_arr_row = array();
            $each_one = 'http' . trim($v);
            $tmp_arr = self::get_base_data_from($each_one);
           
            $tmp_arr_row = array(
                $tmp_arr['child_url'],
                $tmp_arr['abreview']
            );
            if (!fputcsv($f, $tmp_arr_row))
            {
                echo ' [  ' . $each_one . ' failed!  row:' . ($k + 1) . " ] ";
            }
        }
        fclose($f);
        echo ' ===finished! ===' . date("Y-m-d H:m");
    }
    public function get_base_data_from($url = '')
    {

        $url = strlen($url) ? $url : "http://www.abcam.com/2-cys-peroxiredoxin-antibody-6e5-ab16765.html?productWallTab=Questions";
        $product_get_url = $url;
        $product = array(); 
        $product['child_url'] = $url;
        $content = format_html($product_get_url);
        $title = $this->set_base_rules('title', $content);
        if ((!empty($title)) && strlen($title[2][0]))
        {
            $product['title'] = $title[2][0];
            //==================================================
            $product['product_name'] = strip_tags(self::set_base_rules('product_name', $content));
            $species_a = strip_tags(self::set_base_rules('species_a', $content));
            $species_b = strip_tags(self::set_base_rules('species_b', $content));
            $product['species'] = strlen($species_a) ? str_replace("Reacts with:", " ", $species_a) : str_replace("Reacts with:", " ", $species_b);
            $applicaiton_a = strip_tags(self::set_base_rules('applicaiton_a', $content));
            $applicaiton_b = strip_tags(self::set_base_rules('applicaiton_b', $content));
            $product['applicaiton'] = strlen($applicaiton_a) ? str_replace("more details", "", $applicaiton_a) : str_replace("more details", "", $applicaiton_b);
            $product['alternative_names'] = strip_tags(self::set_base_rules('alternative_names', $content));
            $product['clonality'] = strip_tags(self::set_base_rules('clonality', $content));
            $product['isotype'] = strip_tags(self::set_base_rules('isotype', $content));
            $product['uniport_id'] = strip_tags(self::set_base_rules('uniport_id', $content));
            $product['human_uniport_id'] = '';
            $human_uniport_id = self::set_base_rules('human_uniport_id', $content);
            if ($human_uniport_id)
            {
                $product['human_uniport_id'] = $human_uniport_id;
            }
            $all_uniport_id = self::set_base_rules('all_uniport_id', $content);

            $product['all_uniport_id'] = trim(implode('|', $all_uniport_id[1]));
            //abreview
            $product['abreview'] = 0;
            $review = self::set_base_rules('review', $content);
            $product['abreview'] = $review[2][0];
        }
        return $product;
    }

    //abcam �ɼ�������
    public function set_base_rules($param, $content)
    {
        $data = $preg_unique = $preg = $pre_arr = array();

        //������
        $preg['title'] = '#<h1 class="title"(.*)>(.*)</h1>#iUs';
        $preg_unique['product_name'] = '#<span class="name">Product name</span><span class="value">(.*)</span>#iUs';
        //��ͬ��Ʒspecies
        $preg_unique['species_a'] = '#<span class="name">Species reactivity</span><div class="value">(.*)</div>#iUs';
        $preg_unique['species_b'] = '#<span class="name">Species</span><span class="value">(.*)</span>#iUs';

        //application | tested application
        $preg_unique['applicaiton_a'] = '#<span class="name">Tested applications</span><span class="value">(.*)</span>#iUs';
        $preg_unique['applicaiton_b'] = '#<span class="name">Applications</span><span class="value">(.*)</span>#iUs';

        //Alternative names | clonality | isotype
        $preg_unique['alternative_names'] = '#<div class="name">Alternative names</div><div class="value"><ul>(.*)</ul></div>#iUs';
        $preg_unique['clonality'] = '#<span class="name">Clonality</span><span class="value">(.*)</span>#iUs';
        $preg_unique['isotype'] = '#<span class="name">Isotype</span><span class="value">(.*)</span>#iUs';
        //unipror id
        $preg['all_uniport_id'] = '#SwissProt:(.*)</a>#iUs';
        $preg_unique['human_uniport_id'] = '#SwissProt:(.*)</a>Human#iUs';
        //Database links ���ܳ��ֵ�uniproid Human| array
        $preg_unique['uniport_id'] = '#UniProt accession <a href="http://www.uniprot.org/uniprot/(.*)"rel="nofollow"target="_blank"class="dsIcon extLink">(.*)</a>#iUs';
        
        //abreview
        $preg['review'] = '#<a id="ds_header_Questions_link"href="(.*)?productWallTab=Questions"class="pws_link pws_questions scroll_to">Q&amp;A \(([0-9]+)\)</a>#iUs';
       
        
        if ($param && $content)
        {
            if (isset($preg_unique[$param]))
            {
                preg_match($preg_unique[$param], $content, $pre_arr);
                return $pre_arr[1];
            }
            else
            {
                preg_match_all($preg[$param], $content, $pre_arr);
                return $pre_arr;
            }
        }
        return $pre_arr;
    }

    public function do_child_url($url = '')
    {
        $url = strlen($url) ? $url : "http://www.abcam.com/products?selected.researchAreas=Cardiovascular--Angiogenesis--Adhesion+%2F+ECM--Extracellular+Matrix&selected.targetName=LAMB3&selected.productType=Primary+antibodies";
        $product_get_url = $url;
        $product = $pre_arr = array();
        $contents = format_html($product_get_url);
        if ($product_get_url)
        {
            preg_match('#<div class="pws_search_count_statement ga something"(.*)data-track-value="(.*)"(.*)">#iUs', $contents, $arr);
            $num = intval($arr[2]);
            if ($num > 10)
            {
                $page_num = $num / 10;
            }else{
                $page_num =  1;
            }
            $page_last_page = $num % 10;
            if ($page_last_page > 0)
            {
                $page_num++;
            }
          
            for ($i = 1; $i <= $page_num; $i++)
            {
                $web_url = $product_get_url . "&pageNumber=" . $i;

                $content = "";
                $content = format_html($web_url);
                $pre_arr = array();
                if ($content)
                {
                    //这里有可能同时出现下面3个情况，也有可能出现其中2个，导致重复（需要后期去重）
                    //step1:
                    //抗体 
                    preg_match_all('#<div class="pws-item-info">(.*)<\/div><div class="clear">#iUs', $content, $pre_arr1);
                    //多肽
                    preg_match_all('#<div class="pws_left_panel">(.*)<\/div><div class="pws_right_panel">#iUs', $content, $pre_arr2);
                    preg_match_all('#<div class="pws_left_panel">(.*)<\/div><div class="clear">#iUs', $content, $pre_arr3);
                    $pre_arr  = $pre_arr1+$pre_arr2+$pre_arr3;
                    //kits
                    //蛋白
                    //step2:
                    foreach ($pre_arr[1] as $k => $item)
                    {
                        $pre_arr_2 = array();
                        preg_match('#<h3>(.*)<a href="(.*)">(.*)<span>\(([\w]+)\)</span></a>(.*)<\/h3>#iUs', $item, $pre_arr_2);
                        if ($pre_arr_2)
                        {
                            $product[] = array(
                                'category-url' => $product_get_url,
                                'catalog' => $pre_arr_2[4],
                                'title' => $pre_arr_2[3],
                                'url' => $pre_arr_2[2]
                            );
                        }
                    }
                }
            }
        } 
        return $product;
    }

    public function get_products_url()
    {
        $name = 'left_cell_biology';
        $fileName = 'C:\Users\li_hao\Desktop\2015abcam\script/' . $name . '.txt';
        $product_arr = $export_data = array();
        //open file
        $contents = file_get_contents($fileName);
        $product_arr = explode('/', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $each_one = 'http://www.abcam.com/' . trim($v);
            $tmp_data = $this->do_child_url($each_one);
            foreach ($tmp_data as $v)
            {
                $export_data[] =$v;
            }
        } 
        $export_arr = array(
            'category-url' => 'category-url',
            'catalog' => 'catalog',
            'title' => 'title',
            'url' => 'url',
        );
        export_excel($export_arr, $export_data, 'step2_url_' . $name, "C:\Users\li_hao\Desktop/2015abcam/script/");
    }
 public function get_products_url_cancer()
    {
        $name = '580_cancer';
        $fileName = 'C:\Users\li_hao\Desktop\2015abcam\script/' . $name . '.txt';
        $product_arr = $export_data = array();
        //open file
        $contents = file_get_contents($fileName);
        $product_arr = explode('/', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $each_one = 'http://www.abcam.com/' . trim($v);
            $tmp_data = $this->do_child_url($each_one);
            foreach ($tmp_data as $v)
            {
                $export_data[] =$v;
            }
        } 
        $export_arr = array(
            'category-url' => 'category-url',
            'catalog' => 'catalog',
            'title' => 'title',
            'url' => 'url',
        );
        export_excel($export_arr, $export_data, 'step2_url_' . $name, "C:\Users\li_hao\Desktop/2015abcam/script/");
    }
//step1 : 20151123获取子分类的链接
//    public function get_zifen_url()
//    {
//       $category = "Stem-Cells";
//       $each_one = "http://www.abcam.com/index.html?pageconfig=productmap&cl=3427";
//        $content = format_html( $each_one );
//         
//        $arrs = array();
//        $arr[1] = $content;
//        if ($arr)
//        {
//            preg_match_all('#<li><a href="(.*)">(.*)\(([0-9]+)\)<\/a><\/li>#iUs', $arr[1], $arr_2);
//            if(empty($arr_2))
//            {
//                
//            }
//            if ($arr_2)
//            {
//                $product_arr_url = $arr_2[1]; //链接
//                $product_arr_name = $arr_2[2];
//                $product_arr_num = $arr_2[3];
//                foreach ($product_arr_url as $k => $v)
//                {
//                    $product[$k]['categroy_url'] = $v;
//                    $product[$k]['categroy_name'] = $product_arr_name[$k];
//                    $product[$k]['product_num'] = $product_arr_num[$k];
//                }
//            }
//        }
//
//        $excel_arr = array(
//            'categroy_url' => 'categroy_url',
//            'categroy_name' => 'categroy_name',
//            'product_num' => 'product_num'
//        );
//      export_excel($excel_arr, $product, $category, "C:\Users\li_hao\Desktop/2015abcam/");
//    }

    //
}
