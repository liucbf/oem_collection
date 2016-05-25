<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Collection_rockland extends CI_Controller {

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

    public function index(  )
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
        $product_arr = explode('http://', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $temp_product_arr[] = self::get_base_data(false, 'http://' . trim($v));
        }
        unset($product_arr);
        $export_arr = array(
            'url' => 'Url',
            'Title' => 'Title',
            'Catalog' => 'Catalog',
            'Size' => 'Size',
            'Price' => 'Price',
            'Availability' => 'Availability',
            'DatasheetTitle1' => 'DatasheetTitle1',
            'Description' => 'Description',
            'Host' => 'Host',
            'Specificity' => 'Specificity',
            'Target_Species' => 'Target_Species',
            'Known_Cross_Reactivity' => 'Known_Cross_Reactivity',
            'Product_Type' => 'Product_Type',
            'Kit_Type' => 'Kit_Type',
            'GeneName' => 'GeneName',
            'Clonality' => 'Clonality',
            'Application' => 'Application',
            'Species_of_Origin' => 'Species_of_Origin',
            'Physical_State' => 'Physical_State',
            'Shipping_condition' => 'Shipping_condition',
            'Format' => 'Format',
            'PSC_Type' => 'PSC_Type',
            'PSC_Format' => 'PSC_Format',
            'Label' => 'Label',
            'FP_Value' => 'FP_Value',
            'Concentration' => 'Concentration',
            'Buffer' => 'Buffer',
            'Reconstitution_Volume' => 'Reconstitution_Volume',
            'Reconstitution_Buffer' => 'Reconstitution_Buffer',
            'Preservative' => 'Preservative',
            'Stabilizer' => 'Stabilizer',
            'DatasheetTitle2' => 'DatasheetTitle2',
            'Background' => 'Background',
            'Immunogen' => 'Immunogen',
            'Storage_Condition' => 'Storage_Condition',
            'Application_Note' => 'Application_Note',
            'Purity' => 'Purity',
            'Disclaimer_Note' => 'Disclaimer_Note',
            
            
        );
         for ($i = 1; $i < 21; $i++)
        {
            $export_arr['Image_' . $i] = 'Image_' . $i;
            $export_arr['Type_' . $i] = 'Type_' . $i;
            $export_arr['Legend_' . $i] = 'Legend_' . $i;
        }
        export_excel($export_arr, $temp_product_arr, 'rockland_data_' . date("YmdHis"));
    }

    public function get_base_data($id = false, $url = false)
    {
        $website_url = "http://rockland-inc.com";
        $product_get_url = "http://rockland-inc.com/Product.aspx?id=";
        if ($id)
        {
            $product_get_url .= $id;
        }
        elseif ($url)
        {
            $product_get_url = $url; //32797
        }
        $product = array(); //产品总数组
        $product['url'] = $product_get_url;
        $content = file_get_contents($product_get_url);
        $content = compress_html($content);

        if (empty($content))
        {
            //  $product['is_epmty'] = 1;
        }
        else
        {
            $title = self::set_base_rules('Title', $content);
            $product['Title'] = $title;
            if ($title)
            {
                $product['Catalog'] = self::set_base_rules('Catalog', $content);
                $product['Size'] = self::set_base_rules('Size', $content);
                $product['Price'] = self::set_base_rules('Price', $content);
                $product['Availability'] = self::set_base_rules('Availability', $content);

                $product['DatasheetTitle1'] = self::set_base_rules('DatasheetTitle1', $content);
                $product['Description'] = self::set_base_rules('Description', $content);
                $product['Host'] = self::set_base_rules('Host', $content);
                $product['Specificity'] = self::set_base_rules('Specificity', $content);
                $product['Target_Species'] = self::set_base_rules('Target_Species', $content);
                $product['Known_Cross_Reactivity'] = self::set_base_rules('Known_Cross_Reactivity', $content);
                $product['Clonality'] = self::set_base_rules('Clonality', $content);
                $product['Application'] = self::set_base_rules('Application', $content);
                $product['Species_of_Origin'] = self::set_base_rules('Species_of_Origin', $content);

                $product['Product_Type'] = self::set_base_rules('Product_Type', $content);
                $product['Kit_Type'] = self::set_base_rules('Kit_Type', $content);
                $product['GeneName'] = self::set_base_rules('GeneName', $content);


                $product['Physical_State'] = self::set_base_rules('Physical_State', $content);
                $product['Shipping_condition'] = self::set_base_rules('Shipping_condition', $content);
                $product['Format'] = self::set_base_rules('Format', $content);

                $product['PSC_Type'] = self::set_base_rules('PSC_Type', $content);
                $product['PSC_Format'] = self::set_base_rules('PSC_Format', $content);

                $product['Label'] = self::set_base_rules('Label', $content);
                $product['FP_Value'] = self::set_base_rules('FP_Value', $content);
                $product['Concentration'] = self::set_base_rules('Concentration', $content);
                $product['Buffer'] = self::set_base_rules('Buffer', $content);
                $product['Reconstitution_Volume'] = self::set_base_rules('Reconstitution_Volume', $content);
                $product['Reconstitution_Buffer'] = self::set_base_rules('Reconstitution_Buffer', $content);
                $product['Preservative'] = self::set_base_rules('Preservative', $content);
                $product['Stabilizer'] = self::set_base_rules('Stabilizer', $content);

                $product['DatasheetTitle2'] = self::set_base_rules('DatasheetTitle2', $content);
                $product['Background'] = self::set_base_rules('Background', $content);
                $product['Immunogen'] = self::set_base_rules('Immunogen', $content);
                $product['Storage_Condition'] = self::set_base_rules('Storage_Condition', $content);
                $product['Application_Note'] = self::set_base_rules('Application_Note', $content);
                $product['Purity'] = self::set_base_rules('Purity', $content);
                $product['Disclaimer_Note'] = self::set_base_rules('Disclaimer_Note', $content);
                
                //image
                $preg_arr = self::set_base_rules('image', $content);
                  if (!empty($preg_arr[1]))
                 {
                        $num_all = count($preg_arr[1]);
                        $j = 1;
                        for ($i = 0; $i < $num_all; $i++)
                        {
                            $product['Image_' . $j] = image_url_format(strip_tags($preg_arr[2][$i]));
                            $product['Legend_' . $j] = strip_tags($preg_arr[4][$i]);
                            $j++;
                        }
                  }
           }
        }
        return $product;
    }

    public function set_base_rules($param = NULL, $content)
    {
        $preg = array();
        //Title
        $preg['Title'] = '#<div id="product-selected-title"style="width: 600px;"><h1>(.*)</h1></div>#iUs';

        //title 
        $preg['Catalog'] = '#<div id="table1"><div class="product-table-header">Code</div><hr /><div class="product-text1">(.*)</div></div>#iUs';
        $preg['Size'] = '#<div id="table2"><div class="product-table-header">Size</div><hr /><div class="product-text1">(.*)</div></div>#iUs';
        $preg['Price'] = '#<div id="table3"><div class="product-table-header">Price</div><hr /><div class="product-text1">(.*)</div></div>#iUs';
        $preg['Availability'] = '#<div id="table4"><div class="product-table-header">Availability</div><hr /><div class="product-text2">(.*)</div></div>#iUs';

        //datasheet
        //DatasheetTitle1 
        $preg['DatasheetTitle1'] = '#<div class="product-detail-title"><h2>(.*)</h2></div>#iUs';

        $preg['Description'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Description</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div>#iUs';
        $preg['Host'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Host</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Specificity'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Specificity</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Target_Species'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> Target Species</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Known_Cross_Reactivity'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> Known Cross Reactivity</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Clonality'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Clonality</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Application'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Application</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Species_of_Origin'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> Species of Origin </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Product_Type'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> Product Type </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Kit_Type'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> Kit Type </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['GeneName'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">GeneName</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';

        $preg['Physical_State'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> Physical State </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Shipping_condition'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> Shipping condition </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Format'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Format</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['PSC_Type'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> PSC Type </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['PSC_Format'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> PSC Format </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';


        $preg['Label'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Label</div></div><div class="detail-rightcolumn">(.*)</div>#iUs';
        $preg['FP_Value'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> FP Value </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Concentration'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Concentration</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Buffer'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Buffer</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Reconstitution_Volume'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> Reconstitution Volume </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Reconstitution_Buffer'] = '#<div class="detail-leftcolumn"><div class="detail-text-title"> Reconstitution Buffer </div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Preservative'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Preservative</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';
        $preg['Stabilizer'] = '#<div class="detail-leftcolumn"><div class="detail-text-title">Stabilizer</div></div><div class="detail-rightcolumn"><div class="detail-text">(.*)</div></div>#iUs';

        // DatasheetTitle2
        $preg['DatasheetTitle2'] = '#<div class="product-detail-title2"><h2>(.*)</h2></div>#iUs';
        $preg['Background'] = '#<div class="detail-leftcolumn2"><div class="detail-text-title2">Background</div></div><div class="detail-rightcolumn2"><div class="detail-text2 pad-bottom">(.*)</div></div>#iUs';
        $preg['Immunogen'] = '#<div class="detail-leftcolumn2"><div class="detail-text-title2">Immunogen</div></div><div class="detail-rightcolumn2"><div class="detail-text2 pad-bottom">(.*)</div></div>#iUs';
        $preg['Storage_Condition'] = '#<div class="detail-leftcolumn2"><div class="detail-text-title2"> Storage Condition </div></div><div class="detail-rightcolumn2"><div class="detail-text2 pad-bottom">(.*)</div></div>#iUs';
        $preg['Application_Note'] = '#<div class="detail-leftcolumn2"><div class="detail-text-title2"> Application Note </div></div><div class="detail-rightcolumn2"><div class="detail-text2 pad-bottom">(.*)</div></div>#iUs';
        $preg['Purity'] = '#<div class="detail-leftcolumn2"><div class="detail-text-title2">Purity/Specifity</div></div><div class="detail-rightcolumn2"><div class="detail-text2 pad-bottom">(.*)</div></div>#iUs';
        $preg['Disclaimer_Note'] = '#<div class="detail-leftcolumn2"><div class="detail-text-title2"> Disclaimer Note-General </div></div><div class="detail-rightcolumn2"><div class="detail-text2 pad-bottom">(.*)</div></div>#iUs';

        
        $preg['image'] = '#<a id="product(.*)" class="group1" href="(.*)"(.*)>(.*)</a>#iUs';
       
        //image 
        // $pre['image1'] =  '#<a id="product1" class="group1" href="(.*)"(.*)> #iUs';
        //$pre['image2'] = '#<div id="product-image2"align="center"></div>#iUs';   

        $data = array();
        if ($param && $content)
        {
            if (in_array($param, $this->_base_fields))
            {
                preg_match_all($preg[$param], $content, $pre_arr);
                $data = strip_tags(@$pre_arr[1][0]);
            }
        }
        return $data;
    }

    public function export_image_data2excel($cli = FALSE)
    {
        exit('select file');
        $name = 'image1801-2602.txt'; //image501-800.txt
        $fileName = 'D:/wamp/www/product_rockland/' . $name;
        $product_arr = $export_data = $temp_product_arr = array();
        $contents = file_get_contents($fileName);
        $product_arr = explode('http://', $contents);
        unset($product_arr[0]);
        foreach ($product_arr as $k => $v)
        {
            $temp_product_arr[] = self::get_image_file(false, 'http://' . trim($v));
        }
        if (!$cli)
        {
            unset($product_arr);

            $export_arr = array(
                'url' => 'url',
                'Image_1' => 'Image_1',
                'Legend_1' => 'Legend_1',
                'Image_2' => 'Image_2',
                'Legend_2' => 'Legend_2',
                'Image_3' => 'Image_3',
                'Legend_3' => 'Legend_3',
                'Image_4' => 'Image_4',
                'Legend_4' => 'Legend_4',
                'Image_5' => 'Image_5',
                'Legend_5' => 'Legend_5',
                'Image_6' => 'Image_6',
                'Legend_6' => 'Legend_6',
                'Image_7' => 'Image_7',
                'Legend_7' => 'Legend_7',
                'Image_8' => 'Image_8',
                'Legend_8' => 'Legend_8',
            );

            export_excel($export_arr, $temp_product_arr, 'file_' . time());
        }
    }

    public function get_image_file($id = false, $url = false)
    {
        $website_url = "http://rockland-inc.com";
        $product_get_url = "";
        if ($id)
        {
            $product_get_url = "http://rockland-inc.com/Product.aspx?id=" . $id;
        }
        elseif ($url)
        {
            $product_get_url = $url;
        }
        $product = $preg_arr = array(); //产品总数组
        $product['url'] = $product_get_url;
        //图片数据  
        $content = file_get_contents($product_get_url);
        $preg['image'] = '#<a id="product(.*)" class="group1" href="(.*)"(.*)>(.*)</a>#iUs';
        preg_match_all($preg['image'], $content, $preg_arr);
        //$pre_arr[1] 数目 $pre_arr[2]地址 $pre_arr[4] legend
        if (!empty($preg_arr[1]))
        {
            $num_all = count($preg_arr[1]);
            $j = 1;
            for ($i = 0; $i < $num_all; $i++)
            {
                $product['Image_' . $j] = image_url_format(strip_tags($preg_arr[2][$i]));
                $product['Legend_' . $j] = strip_tags($preg_arr[4][$i]);
                $j++;
            }
            //下载图片
            if ($this->input->is_cli_request())
            {
                $this->load->helper('download');
                for ($i = 0; $i < $num_all; $i++)
                {
                    $save_local_path = 'D:/wamp/www/product_rockland/image/' . image_url_format(strip_tags($preg_arr[2][$i]));

                    $file_website_url = $website_url . strip_tags($preg_arr[2][$i]);
                    $product['imageurl'][] = $file_website_url;
                    $arr_no_file = array();

                    $data = file_get_contents($file_website_url);
                    if ($data)
                    {
                        file_put_contents($save_local_path, $data);
                    }
                    else
                    {
                        echo ' [' . $url . '] ';
                    }
                }
            }
        }
        return $product;
    }

}

?>