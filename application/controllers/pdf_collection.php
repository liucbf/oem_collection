<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Pdf_collection extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        error_reporting(0);
        set_time_limit(0);
    }

    public function index()
    {

        $this->load->helper('file');
        $image_file = "D:/wamp/www/test/pdf/left/";
        $arr = get_dir_file_info($image_file, TRUE);


        $k = 0;
        foreach ($arr as $item_name => $item_arr)
        {
            $filename = $item_arr['relative_path'] . $item_name;
            $content = file_get_contents($filename);
            $content = compress_html($content);

            $product[$k]['important_note'] = self::set_base_rules("important_note", $content);
            $product[$k]['pdf_catalog'] = self::set_base_rules("catalog", $content);
            $product[$k]['catalog'] = $item_name;

            $product[$k]['description'] = self::set_base_rules("description", $content);
            $product[$k]['specificity'] = self::set_base_rules("specificity", $content);
            $product[$k]['clone'] = self::set_base_rules("clone", $content);
            $product[$k]['host_animal'] = self::set_base_rules("host_animal", $content);
            $product[$k]['isotype'] = self::set_base_rules("isotype", $content);
            $product[$k]['source'] = self::set_base_rules("source", $content);
            $product[$k]['immunogen'] = self::set_base_rules("immunogen", $content);
            $product[$k]['format'] = self::set_base_rules("format", $content);
            $product[$k]['purification'] = self::set_base_rules("purification", $content);
            $product[$k]['concentration'] = self::set_base_rules("concentration", $content);
            $product[$k]['buffer'] = self::set_base_rules("buffer", $content);
            $product[$k]['preservative'] = self::set_base_rules("preservative", $content);

            $product[$k]['applications'] = self::set_base_rules("applications", $content);
            $product[$k]['storage'] = self::set_base_rules("storage", $content);
            $product[$k]['warning'] = self::set_base_rules("warning", $content);
            $product[$k]['references'] = self::set_base_rules("references", $content);
            $product[$k]['inactivation'] = self::set_base_rules("Inactivation", $content);
            $k++;
        }

        $export_arr = array(
            'pdf_catalog' => 'pdf_catalog',
            'important_note' => 'important_note',
            'catalog' => 'catalog',
            'description' => 'description',
            'specificity' => 'specificity',
            'clone' => 'clone',
            'host_animal' => 'host_animal',
            'isotype' => 'isotype',
            'source' => 'source',
            'immunogen' => 'immunogen',
            'format' => 'format',
            'purification' => 'purification',
            'concentration' => 'concentration',
            'buffer' => 'buffer',
            'preservative' => 'preservative',
            'applications' => 'applications',
            'storage' => 'storage',
            'warning' => 'warning',
            'references' => 'references',
            'inactivation' => 'inactivation',
        );
        export_excel($export_arr, $product, 'pdf_to_excel_' . time());
    }

    //abcam 閲囬泦瀛愰摼鎺�
    public function set_base_rules($param, $content)
    {
        $data = $preg_unique = $preg = $pre_arr = array();

        $preg['important_note'] = '#<span(.*)>Important Note(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['catalog'] = '#Catalog(.*):(.*)</span></p></td><td(.*)>(.*)</td>#iUs';

        $preg['description'] = '#<span(.*)>Description(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['specificity'] = '#<span(.*)>Specificity(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['clone'] = '#<span(.*)>Clone(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['host_animal'] = '#<span(.*)>Host Animal(.*):</span></p></td><td(.*)>(.*)</td>#iUs';

        $preg['isotype'] = '#<span(.*)>Isotype(.*):</span></p></td><td(.*)>(.*)</td>#iUs';

        $preg['source'] = '#<span(.*)>Source(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['immunogen'] = '#<span(.*)>Immunogen(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['format'] = '#<span(.*)>Format(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['purification'] = '#<span(.*)>Purification(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['concentration'] = '#<span(.*)>Concentration(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['buffer'] = '#<span(.*)>Buffer(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['preservative'] = '#<span(.*)>Preservative(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        //
        $preg['applications'] = '#<span(.*)>Applications(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['storage'] = '#<span(.*)>Storage(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['warning'] = '#<span(.*)>Warning(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['references'] = '#<span(.*)>References(.*):</span></p></td><td(.*)>(.*)</td>#iUs';
        $preg['Inactivation'] = '#<span(.*)>Inactivation(.*):</span></p></td><td(.*)>(.*)</td>#iUs';

        if ($param && $content)
        {
            if (isset($preg_unique[$param]))
            {
                preg_match($preg_unique[$param], $content, $pre_arr);
                return $pre_arr[1];
            }
            else
            {
                preg_match($preg[$param], $content, $pre_arr);


                return strip_tags($pre_arr[4]);
            }
        }
        return $pre_arr;
    }

    //check the files exists
    public function check_files()
    {
        $this->load->helper('file');
        $image_file = "D:/wamp/www/test/pdf/image/";
        $arr = get_dir_file_info($image_file, TRUE);

        foreach ($arr as $item_name => $item_arr)
        {
            $html_name = $item_arr['relative_path'] . $item_name . '/' . $item_name . '.html';
            $content = file_get_contents($html_name);
            preg_match("#<font(.*)>Catalog(.*)</font>#iUs", $content, $pre_arr);

            if (count($pre_arr) == 0)
            {
                $empty_arr[] = $item_name;
            }
            else
            {

                $image_file_arr = get_filenames($item_arr['relative_path'] . $item_name);
                $image_file = $item_arr['relative_path'] . $item_name . "/" . $item_name . ".files/";
                $error = self::_ck_jpg($image_file_arr, $image_file, $item_name);
                if ($error)
                {
                    echo $error;
                }
            }
        }

        foreach ($empty_arr as $v)
        {
            echo "error catalog: " . $v . '<br>';
        }
    }

    //chk jpg exists
    private function _ck_jpg($image_file_arr, $image_file, $catalog)
    {
        $all_image_file = "D:/wamp/www/test/pdf/to_all_images/";
        $is_has_jpg_image = 0;
        $error_msg = null;
        foreach ($image_file_arr as $v)
        {
            $extend = pathinfo($v);
            if (( $extend["extension"] == 'JPG' ) || ($extend["extension"] == 'JPEG'))
            {
                $is_has_jpg_image++;
                if (!@copy($image_file . $v, $all_image_file . $v))
                {
                    $error_msg = '(' . $v . ') copy failed !<br>';
                }
                else
                {
                    if (rename($all_image_file . $v, $all_image_file . $catalog . '_' . $is_has_jpg_image . '.JPG') == FALSE)
                    {
                        $error_msg = '(' . $v . ')  rename failed!<br>';
                    }
                    $image_arr['image_' . $is_has_jpg_image] = $catalog . '_' . $is_has_jpg_image . ".JPG";
                }
            }
        }
        if ($is_has_jpg_image == 0)
        {
            return FALSE;
        }
        else
        {

            return $error_msg;
        }
    }

    //不规则的catalog的个别字段的数据处理规则
    public function pdf_error_description()
    {
        $image_file = "D:/wamp/www/test/pdf/problem/description.xlsx";
        $arr = reade_excel($image_file, TRUE);
        $html = "D:/wamp/www/test/pdf/text/";
        $product = array();
        $k = 1;
        foreach ($arr as $k => $item)
        {
            $product[$k]['catalog'] = $item[0];
            $filename = $html . trim($item[0]) . ".htm";
            $content = file_get_contents($filename);
            $content = compress_html($content);
            $description = '#<span(.*)>Description(.*):</span></p></td>(.*)Source(.*):</span></p></td>#iUs';
            preg_match($description, $content, $pre_arr);


            $product[$k]['productname'] = strip_tags($pre_arr[3]);
            $k++;
        }
        $export_arr = array(
            'catalog' => 'catalog',
            'productname' => 'productname',
        );
        export_excel($export_arr, $product, 'pdf_to_descript_' . time());
    }

    //处理isotype问题：
    public function isotype_1()
    {
        $image_file = "D:/wamp/www/test/pdf/problem/isotype.xlsx";
        $arr = reade_excel($image_file, true);
        $product = array();

        unset($arr[0]);

        foreach ($arr as $k => $item)
        {
            $preg_arr = preg_split("/Isotype:/", $item[1]);
            $product[$k]['catalog'] = $item[0];
            if (strlen($preg_arr[1]))
            {
                $product[$k]['isotype'] = $preg_arr[1];
                $product[$k]['host_animal'] = $preg_arr[0];
                if (strpos($preg_arr[1], "cells") !== FALSE)
                {
                    $preg_isotype_arr = preg_split("/cells/", $preg_arr[1]);
                    if (strlen($preg_isotype_arr[0]))
                    {
                        $product[$k]['isotype'] = $preg_isotype_arr[0];
                    }
                    $product[$k]['host_animal'] .="cells" . $preg_isotype_arr[1];
                }
            }
            else
            {
                $product[$k]['host_animal'] = $preg_arr[0];
                $product[$k]['isotype'] = '';
            }
        }
        $export_arr = array(
            'catalog' => 'catalog',
            'host_animal' => 'host_animal',
            'isotype' => 'isotype',
        );
        export_excel($export_arr, $product, 'pdf_to_istotype_' . time());
    }

}
