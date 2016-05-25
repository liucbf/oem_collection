<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Collection_proteins_peptides extends CI_Controller {

  
    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
        $this->load->helper('mycollection');
    }

    //++++++++++++++++++++++++++++
    public function index(   )
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
            'title_description' => 'title_description',
            'description' => 'description',
            'endotoxin_level' => 'endotoxin_level',
            'purity' => 'purity',
            'formulation' => 'formulation',
            'effective_concentration' => 'effective_concentration',
            'sequence' => 'sequence',
            'origin' => 'origin',
            'modifications' => 'modifications',
            'molecular_formula' => 'molecular_formula',
            'cas_number' => 'cas_number',
            'refrences' => 'refrences',
            'product_citizen' => 'product_citizen',
            
        );
         for ($i = 1; $i < 21; $i++)
        {
            $export_arr['image_' . $i] = 'image_' . $i;
            $export_arr['type_' . $i] = 'type_' . $i;
            $export_arr['legend_' . $i] = 'legend_' . $i;
        }

        export_excel($export_arr, $export_data, 'protein_petides_data'.date("YmdHis"));
    }

    public function get_base_data_from($url = '')
    {

        $url = strlen($url) ? $url : "http://www.alomone.com/p/prongf_%28mut-human%29/n-285";
        $product_get_url = $url;
        $product = array(); //浜у搧鎬绘暟缁�
        $product['url'] = $url;

        $content = file_get_contents($product_get_url);
        $content = compress_html($content);
        //鍩烘湰淇℃伅
        $title = $this->set_base_rules('title', $content);


        if ($title[3][0] != '')
        {
            $product['title'] = $title[3][0];
            $product['title_description'] = self::set_base_rules("title_description", $content);
            $description = self::set_base_rules("description", $content);
            $product['description'] = $description;
            //Our bioassay // image
            $img = self::set_base_rules("img", $content);
            $legend = self::set_base_rules("legend", $content);

            if ($img && is_array($img) && count($img))
            {
                $i = 1;
                foreach ($img[3] as $k => $v)
                {
                    $product['image_' . $i] = $v;
                    $product['legend_' . $i] = strip_tags($img[1][$k] . $legend[1][$k]);
                    $i++;
                }
            }

            // sequ
            $endotoxin_level = self::set_base_rules("endotoxin_level", $content);
            $purity = self::set_base_rules("purity", $content);
            $formulation = self::set_base_rules("formulation", $content);
            $effective_concentration = self::set_base_rules("effective_concentration", $content);
            $sequence = self::set_base_rules("sequence", $content);
            $origin = self::set_base_rules("origin", $content);
            $modifications = self::set_base_rules("modifications", $content);
            $molecular_formula = self::set_base_rules("molecular_formula", $content);
            $cas_number = self::set_base_rules("cas_number", $content);

            $product['endotoxin_level'] = replace_to_html($endotoxin_level[2][0]);
            $product['purity'] = replace_to_html($purity[2][0]);
            $product['formulation'] = replace_to_html($formulation[2][0]);
            $product['effective_concentration'] = replace_to_html($effective_concentration[2][0]);
            $product['sequence'] = $sequence[2][0];
            $product['origin'] = replace_to_html($origin[2][0]);
            $product['modifications'] = replace_to_html($modifications[2][0]);
            $product['molecular_formula'] = replace_to_html($molecular_formula[2][0]);
            $product['cas_number'] = replace_to_html($cas_number[2][0]);
            //refrence
            $refrence_product_citizen = self::set_base_rules("refrence_product_citizen", $content);
            $product_citizen = $refrence = '';
            if (is_array($refrence_product_citizen) && count($refrence_product_citizen))
            {
                foreach ($refrence_product_citizen[1] as $j => $refrence_v)
                {
                    if (intval($refrence_v) == ($j + 1))
                    {
                        $refrence .= $refrence_v . " . " . $refrence_product_citizen[2][$j] . "<br>";
                    }
                    else
                    {
                        $product_citizen .= $refrence_v . " . " . $refrence_product_citizen[2][$j] . "<br>";
                    }
                    $n++;
                }
            }
            $product['refrences'] = $refrence;
            $product['product_citizen'] = $product_citizen;
        }


        return $product;
    }

    //set rules
    public function set_base_rules($param, $content)
    {
        $data = $preg_unique = $preg = $pre_arr = array();
        //title�
        $preg['title'] = '#<h1(.*)class="HeaderText"(.*)>(.*)</h1>#iUs';
        //title description 
        $preg_unique['title_description'] = '#<td class="newProductDescription">(.*)</td>#iUs';
        $preg_unique['description'] = '#<tr><td class="DescTd">(.*)</td>#iUs';
        $preg['img'] = '#<td class="entryImageCell"><img title="(.*)"(.*)src="(.*)"(.*)/></td>#iUs';
        $preg['legend'] = '#<td class="EntryDescCell">(.*)</td>#iUs';
        //Specifications
        $preg['endotoxin_level'] = '#<td class="EntryNameCell"><span class="lblEntryName">Endotoxin level</span></td><td class="EntryDetailsCell"(.*)>(.*)</td>#iUs';
        $preg['purity'] = '#<td class="EntryNameCell"><span class="lblEntryName">Purity</span></td><td class="EntryDetailsCell"(.*)>(.*)</td>#iUs';
        $preg['formulation'] = '#<td class="EntryNameCell"><span class="lblEntryName">formulation</span></td><td class="EntryDetailsCell"(.*)>(.*)</td>#iUs';
        $preg['effective_concentration'] = '#<td class="EntryNameCell"><span class="lblEntryName">Effective concentration</span></td><td class="EntryDetailsCell"(.*)>(.*)</td>#iUs';
        $preg['sequence'] = '#<td class="EntryNameCell"><span class="lblEntryName">Sequence</span></td><td class="EntryDetailsCell"(.*)>(.*)</td>#iUs';
        $preg['origin'] = '#<td class="EntryNameCell"><span class="lblEntryName">Origin</span></td><td class="EntryDetailsCell"(.*)>(.*)</td>#iUs';
        $preg['modifications'] = '#<td class="EntryNameCell"><span class="lblEntryName">Modifications</span></td><td class="EntryDetailsCell"(.*)>(.*)</td>#iUs';
        $preg['molecular_formula'] = '#<td class="EntryNameCell"><span class="lblEntryName">Molecular formula</span></td><td class="EntryDetailsCell"(.*)>(.*)</td>#iUs';
        $preg['cas_number'] = '#<td class="EntryNameCell"><span class="lblEntryName">CAS number</span></td><td class="EntryDetailsCell"(.*)>(.*)</td>#iUs';
        //refrence
        $preg['refrence_product_citizen'] = '#<td class="entryTitleCell"><span class="entryTitle">(\d+)\.(.*)</span></td>#iUs';
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
