<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class avas extends CI_Controller {

    //path
    var $_path = "D:/work-2015/aves/";
    var $_model_url = "http://www.aveslab.com/products/neuronal-cell-markers/neuronal-markers/amyloid-precursor-protein-app-peptide-3/";

    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
        $this->load->helper('mycollection');
    }

    //++++++++++++++++++++++++++++
    public function index()
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
        
        $product_arr = explode('http', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $each_one = 'http' . trim($v);
            $export_data[] = self::get_base_data_from($each_one);
        }
        $export_arr = array(
            'url' => 'url',
            'title' => 'title',
            'price' => 'price',
            'catalog' => 'catalog',
            'concentrations' => 'concentrations',
            'buffer' => 'buffer',
            'quality_control' => 'quality_control',
            'dilutions' => 'dilutions',
            'volume' => 'volume',
            'description' => 'description',
            'image' => 'image',
            'legend' => 'legend'
        );
        export_excel($export_arr, $export_data, ' aves_' .'__' .date('YmdHis'));
    }

    public function get_base_data_from($url = '')
    {
        $url = strlen($url) ? $url : $this->_model_url;
        $product_get_url = $url;
        $product = array(); //浜у搧鎬绘暟缁�
        $product['url'] = $url;
        $content = file_get_contents($product_get_url);
        $content = compress_html($content);
        $title = $this->set_base_rules('title', $content);
        if($title)
        {
            $product['title'] = $title;
            $product['price'] = $this->set_base_rules('price', $content);
            $concentrations  = $this->set_base_rules('concentrations', $content);
            $quality_control  = $this->set_base_rules('quality_control', $content);
            $dilutions  = $this->set_base_rules('dilutions', $content);
            $volume  = $this->set_base_rules('volume', $content);
            $catalog  = $this->set_base_rules('catalog', $content);
             $buffer  = $this->set_base_rules('buffer', $content);
            $product['catalog'] = $catalog[2][0];
            //根据图表只有一张图片
            $imgs  = $this->set_base_rules('img', $content);
            $product['image'] = $imgs[3][0];
            $product['legend'] = $imgs[2][0];
            $buffer_1 = $this->set_base_rules('buffer_1', $content);
            $volume_1 = $this->set_base_rules('volume_1', $content);
            $concentrations_1 = $this->set_base_rules('concentrations_1', $content);
            $quality_control_1 = $this->set_base_rules('quality_control_1', $content);
            $description_1 = $this->set_base_rules('description_1', $content);
            $product['volume'] = $volume[2][0].$volume_1[2][0] ;
            $product['concentrations']  = $concentrations[2][0].$concentrations_1[2][0];
            $product['quality_control']  = $quality_control[2][0].$quality_control_1[2][0];
            $product['description']  = strip_tags($this->set_base_rules('description', $content)).$description_1;
            $product['dilutions']  = $dilutions[2][0];
            $product['buffer'] = $buffer[3][0].$buffer_1[2][0];
        }
        return $product;
    }

    //set rules
    public function set_base_rules($param, $content)
    {
        $data = $preg_unique = $preg = $pre_arr = array();
        //title�
        $preg_unique['title'] = '#<h1>(.*)</h1>#iUs';
        //Product Info 
        $preg_unique['price'] = '#<td><strong>Price:</strong>(.*)<br />#iUs';
        $preg_unique['description'] = '#<p><b>Description:</b>(.*)</p>#iUs';
        $preg['buffer'] = '#<p><b>Buffer(.*):(.*)</b>(.*)</p>#iUs';
        $preg['concentrations'] = '#<p><b>Antibody(.*)Concentrations:</b>(.*)</p>#iUs';
        $preg['quality_control'] = '#<p><b>Quality(.*)Control:</b>(.*)</p>#iUs';
        $preg['dilutions'] = '#<p><b>Recommended(.*)Dilutions:</b>(.*)</p>#iUs';
        $preg['volume'] = '#<p><b>Volume:(.*)</b>(.*)</p>#iUs';
        $preg['catalog'] = '#<strong>Catalog(.*)\#:</strong>(.*)<br />#iUs';
        $preg['img'] = '#<a(.*)rel="lightbox"title="(.*)"><img class="prod_img"src="(.*)"(.*)/></a>#iUs';
   
        $preg['buffer_1'] = '#<p><strong>Buffer:(.*)</strong>(.*)</p>#iUs';
        $preg['volume_1'] = '#<p><strong>Volume:(.*)</strong>(.*)</p>#iUs';
        $preg['concentrations_1'] = '#<p><strong>Antibody(.*)Concentration</strong>(.*)</p>#iUs';
        $preg['quality_control_1'] = '#<p><strong>Quality(.*)Control:</strong>(.*)</p>#iUs';
        $preg_unique['description_1'] = '#<p><strong>Description:</strong>(.*)</p>#iUs';
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

}
