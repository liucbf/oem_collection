<?php

/* 
 * Everest rules control
 * 
 * 
 */
class Collection_everest extends CI_Controller
{
    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
    }
    //输入地址或者导入txt文件
    function index()
    {
        $post = $this->input->post();
        $type = $post['select_type'];
        if( $type == '1' )
        {
            $config['allowed_types'] = 'txt';
            $config['upload_path'] = './static/uploads/';
            $this->load->library('upload', $config);
            if ( ! $this->upload->do_upload('url_file'))
            {
                exit( $this->upload->display_errors());
            }
            $file = $this->upload->data();
            $contents = file_get_contents( $file['full_path'] );
        }
        if( $type == '2' )
        {
           $contents = trim($post['url_input']);
        }
        $product_arr = $export_data = array();
        $product_arr = explode('http', $contents);
        unset($product_arr[0]);
       
        foreach ($product_arr as $k => $v)
        {
            $each_one = 'http' . trim($v);
            $export_data[] =self::get_row_data_by_url($each_one);
        }
        $export_arr = array(
            'code' => 'code',
            'name' => 'name',
            'application' => 'application',
            'size' => 'size',
            'reactivity' => 'reactivity',
            'price' => 'price',
            'principal_names' => 'principal_names',
            'official_symbol' => 'official_symbol',
            'accession_number' => 'accession_number',
            'human_genid' => 'human_genid',
            'non_human_genid' => 'non_human_genid',
            'important_comments' => 'important_comments',
            'immunogen' => 'immunogen',
            'purification' => 'purification',
            'title' => 'title',
            'url' => 'url',
            'tested' => 'tested',
            'type_dilution' => 'type_dilution',
        );
          for($i = 1;$i<21 ;$i++ )
        {
            $export_arr['Image_'.$i] = 'Image_'.$i;
            $export_arr['Type_'.$i] = 'Type_'.$i;
            $export_arr['Legend_'.$i] = 'Legend_'.$i;
        }
        export_excel($export_arr, $export_data, 'Everest'.date('YmdHis'));
        // create_cvs( $export_arr ,$export_data,$file_name = 'D:/wamp/www/test/evers/'.$name.'.csv');      
    }
     //根据一个地址采集一条产品数据，返回为一个数组
    public function get_row_data_by_url($product_get_url = "http://everestbiotech.com/product/goat-anti-pai1-serpine1-antibody/")
    {
        $product = array(); 
        $content = format_html($product_get_url);
        //基本信息
        $product['url'] = $url;
        $title = self::set_base_rules('title', $content);
        if (isset($title))
        {
            $product['title'] = $title;
            $type_dilution = self::set_base_rules('type_dilution', $content);
            $product['type_dilution'] = strip_tags($type_dilution);
            $image_legend = self::set_base_rules('image_legend', $content);
            $i = 0;
            if (is_array($image_legend[1]))
            {
                $i = 1;
                foreach ($image_legend[1] as $k => $v)
                {
                    $product['Image_' . $i] = $v;
                    $product['Legend_' . $i] = $image_legend[3][$k];
                    $i++;
                }
            }
          //  Target Protein | [ Principal Names| Official Symbol |Accession Number(s)|Human GeneID(s)| Human GeneID(s)|Important Comments ]
            $product['code'] = self::set_base_rules('code', $content);
            $product['name'] = self::set_base_rules('name', $content);
            $product['application'] = self::set_base_rules('application', $content);
            $product['size'] = self::set_base_rules('size', $content);
             $product['reactivity'] = self::set_base_rules('reactivity', $content);
            $product['price'] = self::set_base_rules('price', $content);
            //Target Protein 
            $product['principal_names'] = self::set_base_rules('principal_names', $content);
            $product['official_symbol'] = self::set_base_rules('official_symbol', $content);
            $product['accession_number'] = self::set_base_rules('accession_number', $content);
            $product['human_genid'] = strip_tags(self::set_base_rules('human_genid', $content));
            $product['non_human_genid'] = strip_tags(self::set_base_rules('non_human_genid', $content));
            $product['important_comments'] = self::set_base_rules('important_comments', $content);
            $product['immunogen'] = strip_tags(self::set_base_rules('immunogen', $content));
            $product['purification'] = strip_tags(self::set_base_rules('purification', $content));
            $product['tested'] = self::set_base_rules('tested', $content);
        }
        return $product;
    }
    //设定规则
      public function set_base_rules($param = NULL, $content)
    {
        $data = $preg_unique = $preg = $pre_arr = array();
        //唯一字段
        //code  name applications  size reactivity price 
        $preg_unique['code'] = '#<td class="column-code">(.*)</td>#iUs';
        $preg_unique['name'] = '#<td class="column-product">(.*)</td>#iUs';
        $preg_unique['application'] = '#<td class="column-applications text-small">(.*)</td>#iUs';
        $preg_unique['size'] = '#<td class="column-size">(.*)</td>#iUs';
        $preg_unique['reactivity'] = '#<td class="column-reactivity text-small">(.*)</td>#iUs';
        $preg_unique['price'] = '#<td class="column-cost">(.*)</td>#iUs';
        //Target Protein | [ Principal Names| Official Symbol |Accession Number(s)|Human GeneID(s)| Human GeneID(s)|Important Comments ]
        $preg_unique['principal_names'] = '#<strong>Principal Names:</strong><span class="text-medium">(.*)</span>#iUs';
        $preg_unique['official_symbol'] = '#<strong>Official Symbol:</strong><span class="text-medium">(.*)</span>#iUs';
        $preg_unique['accession_number'] = '#<strong>Accession Number\(s\):</strong><span class="text-medium">(.*)</span>#iUs';
        $preg_unique['human_genid'] = '#<strong>Human GeneID\(s\):</strong><span class="text-medium">(.*)</span>#iUs';
        $preg_unique['non_human_genid'] = '#<strong>Non-Human GeneID\(s\):</strong><span class="text-medium">(.*)</span>#iUs';
        $preg_unique['important_comments'] = '#<strong>Important Comments:</strong><span class="text-medium">(.*)</span>#iUs';
        $preg_unique['immunogen'] = '#<div class="section"><h4>Immunogen</h4>(.*)</div>#iUs';
        $preg_unique['purification'] = '#<div class="section"><h4>Purification and Storage</h4>(.*)</div>#iUs';
        $preg_unique['tested'] = '#<strong>Tested:</strong><span class="text-medium">(.*)</span>#iUs';
        //Title |legend
        $preg_unique['title'] = '#<div id="product-title"><h1>(.*)</h1>#iUs';
        $preg_unique['type_dilution'] = '#<div class="section"><h4>Applications Tested</h4>(.*)</div><div class="section last">#iUs';
        //applicaion more(多个)
        $preg['image_legend'] = '#</div><div class="image-block"><a href="(.*)"target="_blank">(.*)/></a><div class="caption text-small">(.*)</div>#iUs';
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
    //20141112根据原始everest catalog返回列表页然后再查询产品详细页 | 暂时用不到
    public function get_file_data_catalog_from_catlaog()
    {
        // exit('select file');
        $name = '2001-3168';
        $fileName = 'D:/work-2015/everest/2015-4-20/' . $name . '.txt';
        
        
        $product_arr = $export_data = array();
        //open file
        $contents = file_get_contents($fileName);
      
        $product_arr = explode('EB', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $each_one = 'EB' . trim($v);
            $export_data[] = self::get_base_info_catalog($each_one);
        }
        $export_arr = array(
            'catalog' => 'catalog',
            'code' => 'code',
            'name' => 'name',
            'application' => 'application',
            'size' => 'size',
            'reactivity' => 'reactivity',
            'price' => 'price',
            'principal_names' => 'principal_names',
            'official_symbol' => 'official_symbol',
            'accession_number' => 'accession_number',
            'human_genid' => 'human_genid',
            'non_human_genid' => 'non_human_genid',
            'important_comments' => 'important_comments',
            'immunogen' => 'immunogen',
            'purification' => 'purification',
            'title' => 'title',
            'url' => 'url',
            'type_dilution' => 'type_dilution',
        );
//           for($i = 1;$i<21 ;$i++ )
//        {
//            $export_arr['Image_'.$i] = 'Image_'.$i;
//            $export_arr['Type_'.$i] = 'Type_'.$i;
//            $export_arr['Legend_'.$i] = 'Legend_'.$i;
//        }
//        
        export_excel($export_arr, $export_data, 'everest Data_' . $name . '__' . time());
        // create_cvs( $export_arr ,$export_data,$file_name = 'D:/wamp/www/test/everest/'.$name.'.csv');               
    }
     public function get_base_info_catalog($keyword_catalog = 'EB06443')
    {
        $product = array();
        $web_site_url = "http://everestbiotech.com";
        $url = 'http://everestbiotech.com/search/?keywords=' . $keyword_catalog;
        $content = format_html($url);
        $product['catalog'] = $keyword_catalog;
        $preg = array();
        //title 与 详细页url
        $preg['alis'] = '#<td class="column-name"><a href="(.*)">(.*)</a></td>#iUs';
        preg_match_all($preg['alis'], $content, $pre_arr);
        //标题超链接
        if (!empty($pre_arr))
        {
            $original_url = $pre_arr[1][0];
            $title = $pre_arr[2][0];
            $collection_url = $web_site_url . $original_url;
            $product += $this->get_row_data_by_url($collection_url);
        }

        return $product;
    }

    
}

