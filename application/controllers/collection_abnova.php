<?php

/* 
 * Collection_abnova rules control
 * 
 * 
 */
class Collection_abnova extends CI_Controller
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
            $contents = trim($post['url_input']) ;
        }
   
        $product_arr = explode('http', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
             $each_catalog_no = 'H' . trim($v);
             $each_url = 'http' . trim($v);
             $export_data[$k] = self::get_base_data_from_abnova(false,$each_url);
        }
        $export_arr = array(
            'origin_catalog' => 'origin_catalog',
            'url'=>'url',
            'Product_description' => 'Product_description',
            'Immunogen' => 'Immunogen',
            'Sequence' => 'Sequence',
            'Reactivity' => 'Reactivity',
            'Host' => 'Host',
            'Isotype' => 'Isotype',
            'Storage_buffer' => 'Storage_buffer',
            'Storage_instruction' => 'Storage_instruction',
            'Publication_refrence' => 'Publication_refrence',
            'Entrez_geneid' => 'Entrez_geneid',
            'Genebank_accession' => 'Protein_accession',
            'Gene_name' => 'Gene_name',
            'Gene_alias' => 'Gene_alias',
            'Gene_description' => 'Gene_description',
            'Omim_id' => 'Omim_id',
            'Gene_ontology' => 'Gene_ontology',
            'Other_designations' => 'Other_designations',
            'Gen_sumary' => 'Gen_sumary',
        );
        for($i = 1;$i<21 ;$i++ )
        {
            $export_arr['Image_'.$i] = 'Image_'.$i;
            $export_arr['Type_'.$i] = 'Type_'.$i;
            $export_arr['Legend_'.$i] = 'Legend_'.$i;
        }
        export_excel($export_arr, $export_data, 'Abnova Data_' .date('YmdHis'));
        //  create_cvs( $export_arr ,$export_data,$file_name = 'D:/wamp/www/test/abcam/child_url/finished/'.$name.'.csv');
    }

    public function get_base_data_from_abnova($catalog_id = false,$url)
    {
        $website_url = "http://www.abnova.com/";
        $product_get_url = "http://www.abnova.com/products/products_detail.asp?catalog_id=";
        if ($catalog_id)
        {
            $product_get_url .= $catalog_id;
        }
        else
        {
            $product_get_url = $url;
        }
        $product = array(); //产品总数组
        $product['origin_catalog'] = $catalog_id;
        $product['url'] = $product_get_url;
        $content = file_get_contents($product_get_url);
         $content = compress_html($content);

        if (empty($content))
        {
            $empty_product[] = $catalog_id;
        }
        else
        {
            //基本信息
            $title = self::set_base_rules('title', $content);
            if ($title)
            {
                $product['title'] = $title;
                $product['Product_description'] = self::set_base_rules('Product_description', $content);
                $product['Immunogen'] = self::set_base_rules('Immunogen', $content);
                $product['Sequence'] = self::set_base_rules('Sequence', $content);
                $product['Reactivity'] = self::set_base_rules('Reactivity', $content);
                $product['Host'] = self::set_base_rules('Host', $content);
                $product['Isotype'] = self::set_base_rules('Isotype', $content);
                $product['Storage_buffer'] = self::set_base_rules('Storage_buffer', $content);
                $product['Storage_instruction'] = self::set_base_rules('Storage_instruction', $content);
                $product['Publication_refrence'] = self::set_base_rules('Publication_refrence', $content);
                //gene 相关信息
                $product['Entrez_geneid'] = self::set_base_rules('Entrez_geneid', $content);
                $product['Genebank_accession'] = self::set_base_rules('Genebank_accession', $content);
                $product['Protein_accession'] = self::set_base_rules('Protein_accession', $content);
                $product['Gene_name'] = self::set_base_rules('Gene_name', $content);
                $product['Gene_alias'] = self::set_base_rules('Gene_alias', $content);
                $product['Gene_description'] = self::set_base_rules('Gene_description', $content);
                $product['Omim_id'] = self::set_base_rules('Omim_id', $content);
                $product['Gene_ontology'] = self::set_base_rules('Gene_ontology', $content);
                $product['Other_designations'] = self::set_base_rules('Other_designations', $content);
                $product['Gen_sumary'] = self::set_base_rules('Gen_sumary', $content);
               
                // qc test image 和其他 图片相关信息     
                $applicaion_images_arr = self::get_base_image_from_abnova($content);
                if ($applicaion_images_arr)
                {
                    $num = count($applicaion_images_arr['applicaion_images']);
                    $num_i = 1;
                    foreach ($applicaion_images_arr['applicaion_images'] as $img_k => $img_v)
                    {
                        $product['Image_' . $num_i] = $img_v;
                        $product['Legend_' . $num_i] = $applicaion_images_arr['applicaion_alt'][$img_k];
                       // $product['Type_' . $num_i] = self::get_application_image_type($applicaion_images_arr['applicaion_alt'][$img_k], $img_k);
                        $num_i++;
                    }
                }
            }
        }
        return $product;
    }
    
    
// qc test image 和其他 图片相关信息     
    public function get_base_image_from_abnova($content)
    {
        if (!$content)
        {
            return false;
        }
        $applicaion_images_arr = array();
        $applicaion_images_arr = self::set_base_rules('applicaion_images', $content);
        $quality_control_testing_arr = self::set_base_rules('Quality_control_testing', $content);
        if (is_array($quality_control_testing_arr))
        {
            array_unshift($applicaion_images_arr['applicaion_images'], $quality_control_testing_arr['applicaion_qc_test_image']);
            array_unshift($applicaion_images_arr['applicaion_alt'], $quality_control_testing_arr['applicaion_qc_test_legend']);
        }
        return $applicaion_images_arr;
    }

    public function set_base_rules($param = NULL, $content)
    {
        //Title
        $preg['title'] = '#<h1(.*)>(.*)</h1>#iUs';
        //Specification 
        $preg['Product_description'] = '#<ul><li class="black12 sub_title"><b>Product Description:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Immunogen'] = '#<ul><li class="black12 sub_title"><b>Immunogen:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Sequence'] = '#<ul><li class="black12 sub_title"><b>Sequence:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Reactivity'] = '#<ul><li class="black12 sub_title"><b>Reactivity:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Host'] = '#<ul><li class="black12 sub_title"><b>Host:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Isotype'] = '#<ul><li class="black12 sub_title"><b>Isotype:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Storage_buffer'] = '#<ul><li class="black12 sub_title"><b>Storage Buffer:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Storage_instruction'] = '#<ul><li class="black12 sub_title"><b>Storage Instruction:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Publication_refrence'] = '#<ul class="first_title"><li class="blue12"><b>Publication Reference</b></li></ul>(.*)</div>#iUs';
        //pdf
        //$preg['Msds'] = '#<ul><li class="black12 sub_title"><b>MSDS:</b></li><li class="black11_a_underline sub_content"><a href="(.*)"(.*)>(.*)</li></ul>#iUs';
        //$preg['Datasheet'] = '#<ul><li class="black12 sub_title"><b>Datasheet:</b></li><li class="black11_a_underline sub_content"><a href="(.*)"(.*)>(.*)</li></li></ul>#iUs';
        //Gene Information
        $preg['Entrez_geneid'] = '#<ul><li class="black12 sub_title"><b>Entrez GeneID:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Genebank_accession'] = '#<ul><li class="black12 sub_title"><b>GeneBank Accession\#:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Protein_accession'] = '#<ul><li class="black12 sub_title"><b>Protein Accession\#:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Gene_name'] = '#<ul><li class="black12 sub_title"><b>Gene Name:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Gene_alias'] = '#<ul><li class="black12 sub_title"><b>Gene Alias:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Gene_description'] = '#<ul><li class="black12 sub_title"><b>Gene Description:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Omim_id'] = '#<ul><li class="black12 sub_title"><b>Omim ID:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Gene_ontology'] = '#<ul><li class="black12 sub_title"><b>Gene Ontology:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Other_designations'] = '#<ul><li class="black12 sub_title"><b>Other Designations:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        $preg['Gen_sumary'] = '#<ul><li class="black12 sub_title"><b>Gene Summary:</b></li><li class="black11_a_underline sub_content"(.*)>(.*)</li></ul>#iUs';
        //qc test image and legend
        $preg['Quality_control_testing'] = '#<ul><li class="black12 sub_title"><b>Quality Control Testing:</b></li><li class="black11_a_underline sub_content">(.*)<img src="(.*)"(.*)>(.*)</li></ul>#iUs';
        //Applications
        $preg['applicaion_images'] = '#<li class="app_img_s"><a href="(.*)"><img src="(.*)"title="(.*)"(.*)></a></li>#iUs'; //右侧大图
        $preg['applicaion_legend'] = '#<li class="black11_a_underline app_desc">(.*)</li>#iUs'; //左侧图片

        $data = array();
        if ($param && $content)
        {
            preg_match_all($preg[$param], $content, $pre_arr);
            
            $data = strip_tags($pre_arr[2][0]);
         
            if ($param == 'applicaion_images')
            {
                foreach ($pre_arr[2] as $jpg_k => $jpg_path)
                {
                    $data['applicaion_images'][] = image_url_format($jpg_path);
                    $data['applicaion_alt'][] = $pre_arr[3][$jpg_k];
                    $data['applicaion_images_path'][] = $jpg_path;
                }
            }
            if ($param == 'applicaion_legend')
            {
                //去除空白的数据
                foreach ($pre_arr[1] as $k => $jpg_legend)
                {
                    if ($jpg_legend == '')
                    {
                        unset($pre_arr[1][$k]);
                    }
                    else
                    {
                        $data['applicaion_legend'][] = $jpg_legend;
                    }
                }
            }
            if ($param == 'Quality_control_testing')
            {
                $data['applicaion_qc_test_image'] = image_url_format($pre_arr[2][0]);
                $data['applicaion_qc_test_legend'] = strip_tags($pre_arr[1][0] . $pre_arr[4][0]);
                $data['applicaion_qc_test_image_path'] = $pre_arr[2][0];
            }
        }
        return $data;
    }

}

