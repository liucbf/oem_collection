<?php

class Collection_common extends CI_Controller {

    private $webs_handler;

    function __construct() {
         error_reporting(0);
          set_time_limit(0);
        parent::__construct();
    }

    /**
     * 筑牛左侧菜单采集 
     * @param type $type
     */
    public function index2() {
        $this->load->helper('directory');
        $path = "E:\www\MainSite\V2.0.0\Application\Home\View\Public";
        $map = directory_map($path, FALSE, TRUE);
        foreach ($map as $item) {
            if (strpos($item, '-header') !== false || strpos($item, '-left') !== false || strpos($item, '-menu') !== false) {
                $content = format_html($path . "/" . $item);

                preg_match_all('#<li><a href="(.*)"(.*)>(.*)<\/a><\/li>#iUs', $content, $li_arr);
                foreach($li_arr[3] as $li_k=>$li_v)
                {
                        $pro[] = array(
                         'file' => $path.'/'.$item,
                         'href' => $li_arr[1][$li_k],
                         'name' => preg_replace('/(.*)\>+/','',$li_v)
                         );
                }
               
            }
        }
       export_excel(array('file'=>'file','href'=>'href','name'=>'name'),$pro,'xxx');
    }

    function sset($arr) {
        foreach ($arr as $key => $item) {
            $arr2[$key] = strip_tags($item);
        }
        return $arr2;
    }

    public function work($type = 1) {

        switch ($type) {
            case 1: self::lsbio_collection("1");
                break;
            case 2: self::lsbio_collection("1");
                break;
//             case 3: self::lsbio_collection("left_4001-5000"); break;
//              case 5: self::lsbio_collection("left_5001-6000"); break;
//            case 4: self::lsbio_collection("left_6001-7500"); break;
        }
    }

//life span 采集网站图片数据
    public function lsbio_collection($name) {

        $data = file_get_contents("C:/Users/li_hao/Desktop/lifespan/" . $name . ".txt");
        if (!$data) {
            exit('no file');
        }
        $data_arr = explode('http', $data);
        $product = array();
        foreach ($data_arr as $cc => $url) {
            //read content
            if ($url) {
                $product[$cc]['url'] = trim('http' . $url);
                $content = format_html($product[$cc]['url']);
                preg_match_all('#<table class="srcSitecss_AntibodyDetailsRightColumn_ImageCaption">(.*)<\/table>#iUs', $content, $step1_arr);
                if (count($step1_arr)) {
                    foreach ($step1_arr[1] as $k => $v) {
                        $step1_arr_all = array();
                        preg_match_all('#<img src="/image2/(.*)"alt="(.*)"class="(.*)"/>#iUs', $v, $step1_arr_all);
                        preg_match_all('#<td class="srcSitecss_AntibodyDetailsRightColumn_ImageCaption">(.*)</td>#iUs', $v, $legend_arr_all);
                        if (count($step1_arr_all)) {
                            $product[$cc]['image_' . $k] = $step1_arr_all[1][0];
                            $product[$cc]['type_' . $k] = $step1_arr_all[2][0];
                            $product[$cc]['legend_' . $k] = $legend_arr_all[1][0];
                        }
                    }
                }
            }
        }
        $cols_arr = array('url' => 'url');
        for ($i = 0; $i < 10; $i++) {
            $cols_arr['image_' . $i] = 'image_' . $i;
            $cols_arr['type_' . $i] = 'type_' . $i;
            $cols_arr['legend_' . $i] = 'legend_' . $i;
        }
        export_excel($cols_arr, $product, $name . '--lifespan_img__' . $name, 'C:/Users/li_hao/Desktop/lifespan/');
    }

    function download() {
        $contents = file_get_contents("C:/Users/li_hao/Desktop/lisbio/left/1-2000.txt");
        $product_arr = explode('http', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $K => $v) {
            $url = "http" . $v;

            $filename = "C:/Users/li_hao/Desktop/lisbio/left/1-2000/" . basename($url);
            if (!file_exists($filename)) {
                $contents = file_get_contents($url);
                if (file_put_contents($filename, $contents))
                    echo "$K =";
            }
        }
    }

    function get_location() {
        $contents = file_get_contents("C:\Users\li_hao\Desktop/location/left.txt");
        $product_arr = explode('@', $contents);
        unset($product_arr[0]);
        $product = array();
        foreach ($product_arr as $K => $v) {
            if ($v) {
                $arr = ARRAY();
                $product[$K]['uniprot'] = trim($v);
                $url = "http://www.proteinatlas.org/search/" . trim($v);
                $content = format_html($url);
                #  preg_match('#<td class="center nowrap nopadd atlasmaxwidth"><a href="\/(.*)\/tissue"onmouseover="tooltip\(\'Tissue atlas(.*)\', 0\);"onmouseout="exit\(\)\;"><img style="width:60px;height:60px;"src="(.*)"><\/a><\/td>#iUs',$content,$arr);
                preg_match('#<td class="center nowrap nopadd atlasmaxwidth"><div class="premium"><a href="\/(.*)\/tissue"onmouseover="tooltip\(\'Tissue atlas(.*)\', 0\);"onmouseout="exit\(\)\;"><img style="width:60px;height:60px;"src="(.*)"><div><img src="\/images_static\/premium.png"><\/div><\/a><\/div><\/td>#iUs', $content, $arr);
                if ($arr) {
                    $arr2 = ARRAY();
                    $url2 = "http://www.proteinatlas.org/" . $arr[1] . "/tissue";
                    $content2 = format_html($url2);
                    preg_match('#<tr><th class="nowrap"><p>Predicted localization</p></th><td>(.*)</td></tr>#iUs', $content2, $arr2);

                    $product[$K]['location'] = count($arr2) ? $arr2[1] : "";
                }
            }
        }
        export_excel(array('uniprot' => 'uniprot', "location" => "location"), $product, 'locationsss', 'C:/Users/li_hao/Desktop/location/');
    }

    public function get_other_clone_names() {
        $db_hander = $this->load->database('locals', TRUE);
        $product = array();
        $re = $db_hander->select("va")->get("tmp2")->result_array();
        foreach ($re as $k => $v) {
            $db_hander->select("cid")->where("maxv>={$v['va']} and minv <={$v['va']}", null, FALSE);
            $rows = $db_hander->get("tmp")->row_array();
            if ($rows) {
                echo $product[$k]['va'] = $v['va'] . ";";
                echo $product[$k]['cid'] = $rows['cid'];
                echo "<br>";
            }
        }
    }

    /*
     * * 导出公司产品的description序列化信息 */

    function export_info_by_catalog() {
        $this->webs_handler = $this->load->database('webs', TRUE);
        $file = "C:/Users/li_hao/Desktop/1.txt";
        $handle = @fopen($file, "r");
        if ($handle) {
            //	flock($handle, LOCK_EX);//只有file函数读写才能有效
            while (!feof($handle)) {
                $buffer = fgets($handle, 1024);
                $catalog = trim($buffer);
                $product_arr[] = self::get_description_data($catalog);
                $imgs = '';
                if ($catalog) {
                    $imgs = self::_get_img($catalog); //取图
                    file_put_contents("C:\Users\li_hao\Desktop/img1.txt", $imgs . PHP_EOL, FILE_APPEND); //只有file函数
                }
            }
            //flock($handle, LOCK_UN);
            fclose($handle);
        }


        $excel_arr = array(
            'catalog' => 'catalog',
//           'antigen_source'=>'antigen_source',
//           'isotype'=>'isotype',
//           'aa_region'=>'aa_region',
//           'target_specificity'=>'target_specificity',
//           'predicted'=>'predicted',
//           'format'=>'format',
//           'clonality'=>'clonality',
//           'antigen_source'=>'antigen_source',
            'ncbi_accession' => 'ncbi_accession',
            'geneid' => 'geneid',
            'gene_name' => 'gene_name'
        );
        export_excel($excel_arr, $product_arr, 'ncbi__--', 'C:\Users\li_hao\Desktop/');
    }

    private function get_description_data($catalog) {
        $product['catalog'] = $catalog;
        $this->webs_handler->from('products' . ' pro');
        $this->webs_handler->join('products_description' . ' aa', 'pro.product_id = aa.product_id');
        $row = $this->webs_handler->select("catalog,template_data")->where('catalog', $catalog)->get()->row_array();
        if ($row) {
            $product += unserialize($row['template_data']);
        }
        return $product;
    }

    private function _get_img($catalog) {
        $str = $catalog;
        echo "SELECT `image` FROM  `abgent_products_image` WHERE  `publish` =1 and image like '%" . $catalog . "%' order by `sort` desc ,`created` desc";
        $arr = $this->webs_handler->query("SELECT `image` FROM  `abgent_products_image` WHERE  `publish` =1 and image like '%" . $catalog . "%' order by `sort` desc ,`created` desc")->result_array();
        if ($arr) {
            if ($arr[0]['image']) {
                $str .= ",http://www.abgent.com/assets/uploads/products/" . $arr[0]['image'];
            }
            if ($arr[1]['image']) {
                $str .= ",http://www.abgent.com/assets/uploads/products/" . $arr[1]['image'];
            }
        }

        return $str;
    }

    function mutiple_2_row($path, $save_file_name = 'test') {
        $path = "C:\Users\li_hao\Desktop/abgent_products_citations-1.xlsx";
        $export_data = $excel_data = $export_arr = array();
        $types = pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array($types, array('xls', 'xlsx'))) {
            exit('File type error!');
        }

        $excel_data = reade_excel($path, '.' . $types);
        $cols = $excel_data[0];
        unset($excel_data[0]);
        foreach ($excel_data as $k => $v) {
            $products[$v[0]] .= $v[1] . "|";
        }

        foreach ($products as $catalog => $v) {
            echo $catalog . "," . $v . "<br>";
        }
    }

}
