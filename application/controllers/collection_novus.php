<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Collection_novus extends CI_Controller {

    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
    }

    //导出数据excel  | 导出后还要处理没有获取到的数据
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

        $product_arr = explode('html', $contents);
        unset($product_arr[(count($product_arr) - 1)]);
        foreach ($product_arr as $k => $v)
        {
            $each_one = trim($v) . 'html';
            $export_data[$k] = self::get_base_data_from($each_one);
        }

        $export_arr = array(
            'url' => 'url',
            'catalog_number' => 'catalog_number',
            'alsoknowas' => 'alsoknowas',
            'form' => 'form',
            'size' => 'size',
            'species' => 'species',
            'tested_applications' => 'tested_applications',
            'clonality' => 'clonality',
            'host' => 'host',
            'gene' => 'gene',
            'purity' => 'purity',
            'specificity' => 'specificity',
            'preparation_method' => 'preparation_method',
            'endotoxin_note' => 'endotoxin_note',
            'guarantee_plus' => 'guarantee_plus',
            'description' => 'description',
            'innovator_reward' => 'innovator_reward',
            'immunogen' => 'immunogen',
            'localization' => 'localization',
            'clone' => 'clone',
            'format' => 'format',
            'isotype' => 'isotype',
            'details_of_functionality' => 'details_of_functionality',
            'publications' => 'publications',
            'dilutions' => 'dilutions',
            'application_notes' => 'application_notes',
            'positive_controls' => 'positive_controls',
            'molecular_weight' => 'molecular_weight',
            'publications' => 'publications',
            'storage' => 'storage',
            'buffer' => 'buffer',
            'unit_size' => 'unit_size',
            'concentration' => 'concentration',
            'preservative' => 'preservative',
            'limitations' => 'limitations',
            'formulation' => 'formulation',
            'reconstitution_instructions' => 'reconstitution_instructions',
            'gene_symbol' => 'gene_symbol',
            'entrez' => 'entrez',
            'uniprot' => 'uniprot',
            'background' => 'background'
        );
        for ($i = 1; $i < 21; $i++)
        {
            $export_arr['Image_' . $i] = 'Image_' . $i;
            $export_arr['Type_' . $i] = 'Type_' . $i;
            $export_arr['Legend_' . $i] = 'Legend_' . $i;
        }
        export_excel($export_arr, $export_data, 'novas__' . date('YmdHis'));
    }

    // get info from 
    public function get_base_data_from($url)
    {

        $url = strlen($url) ? $url : "http://www.novusbio.com/ADAM12-Antibody_NB300-889.html";
        $product_get_url = $url;
        $product = array(); //产品总数组
        $product['url'] = $url;
        $content = file_get_contents($product_get_url);
        $content = compress_html($content);
        //基本信息
        $title = self::set_base_rules('title', $content);

        if (isset($title) && strlen($title[2][0]))
        {

            $product['title'] = $title[2][0];
            //==================================================
            $catalog_num = strip_tags(self::set_base_rules('catalog_number', $content));
            if ($catalog_num == '')
            {
                $product['catalog_number'] = strip_tags(self::set_base_rules('catalog_number_s', $content));
            }
            else
            {
                $product['catalog_number'] = $catalog_num;
            }
            $product['alsoknowas'] = strip_tags(self::set_base_rules('alsoknowas', $content));
            $product['form'] = strip_tags(self::set_base_rules('form', $content));
            $product['size'] = strip_tags(self::set_base_rules('size', $content));
            //==================================================
            $product['species'] = strip_tags(self::set_base_rules('species', $content));
            $product['tested_applications'] = strip_tags(self::set_base_rules('tested_applications', $content));
            $product['clonality'] = strip_tags(self::set_base_rules('clonality', $content));
            $product['host'] = strip_tags(self::set_base_rules('host', $content));
            $product['gene'] = strip_tags(self::set_base_rules('gene', $content));
            $product['purity'] = strip_tags(self::set_base_rules('purity', $content));
            $product['specificity'] = strip_tags(self::set_base_rules('specificity', $content));
            //==================================================
            $product['preparation_method'] = strip_tags(self::set_base_rules('preparation_method', $content));
            $product['endotoxin_note'] = strip_tags(self::set_base_rules('endotoxin_note', $content));
            $product['guarantee_plus'] = strip_tags(self::set_base_rules('guarantee_plus', $content));
            $product['description'] = strip_tags(self::set_base_rules('description', $content));
            $product['innovator_reward'] = strip_tags(self::set_base_rules('innovator_reward', $content));

            //==================================================
            $product['immunogen'] = strip_tags(self::set_base_rules('immunogen', $content));
            $product['localization'] = strip_tags(self::set_base_rules('localization', $content));
            $product['clone'] = strip_tags(self::set_base_rules('clone', $content));
            $product['format'] = strip_tags(self::set_base_rules('format', $content));
            $product['isotype'] = strip_tags(self::set_base_rules('isotype', $content));
            $product['details_of_functionality'] = strip_tags(self::set_base_rules('details_of_functionality', $content));
            //==================================================
            $product['publications'] = strip_tags(self::set_base_rules('publications', $content));
            //==================================================
            $product['dilutions'] = strip_tags(self::set_base_rules('dilutions', $content));
            $product['application_notes'] = strip_tags(self::set_base_rules('application_notes', $content));
            $product['positive_controls'] = strip_tags(self::set_base_rules('positive_controls', $content));
            $product['publications'] = strip_tags(self::set_base_rules('publications', $content));
            $product['molecular_weight'] = strip_tags(self::set_base_rules('molecular_weight', $content));
            //==================================================
            $product['storage'] = strip_tags(self::set_base_rules('storage', $content));
            $product['buffer'] = strip_tags(self::set_base_rules('buffer', $content));
            $product['unit_size'] = strip_tags(self::set_base_rules('unit_size', $content));
            $product['concentration'] = strip_tags(self::set_base_rules('concentration', $content));
            $product['preservative'] = strip_tags(self::set_base_rules('preservative', $content));
            $product['limitations'] = strip_tags(self::set_base_rules('limitations', $content));
            //==================================================
            $product['formulation'] = strip_tags(self::set_base_rules('formulation', $content));
            $product['reconstitution_instructions'] = strip_tags(self::set_base_rules('reconstitution_instructions', $content));
            $product['gene_symbol'] = strip_tags(self::set_base_rules('gene_symbol', $content));
            $product['entrez'] = strip_tags(self::set_base_rules('entrez', $content));
            $uniprot = self::set_base_rules('uniprot', $content);
            $product['uniprot'] = isset($uniprot) ? $uniprot[3][0] : '';
            //==================================================
            $product['background'] = strip_tags(self::set_base_rules('background', $content));
            //image legend type ===================
            $image_arr = self::set_base_rules('image_arr', $content);
            if (count($image_arr) && strlen($image_arr[3][0]))
            {
                //图片数量>1
                if (count($image_arr[3]))
                {
                    $j = 0;
                    for ($i = 1; $i < (count($image_arr[3]) + 1); $i++)
                    {
                        $product['Image_' . $i] = $image_arr[3][$j];
                        $product['Legend_' . $i] = $image_arr[2][$j];
                        $product['Type_' . $i] = self::get_application_image_type_by_legend($image_arr[2][$j]);
                        $j++;
                    }
                }
            }
        }

        return $product;
    }


    public function set_base_rules($param = NULL, $content)
    {
        $data = $preg_unique = $preg = $pre_arr = array();
        //唯一字段
        $preg['title'] = '#<h1(.*)>(.*)</h1>#iUs';

        //右侧上catalog_number, alsoknowas ,size,Form
        $preg_unique['catalog_number'] = '#<strong>Catalog Number</strong><span class="pri-catnum">(.*)</span>#iUs';
        $preg_unique['catalog_number_s'] = '#<div class="information-title">Catalog Number</div><div class="information-data">(.*)</div>#iUs';

        $preg_unique['alsoknowas'] = '#<strong>Also Known As &nbsp;&nbsp;</strong><span class="pri-catnum">(.*)</span>#iUs';
        $preg_unique['form'] = '#<span class="ordering-information-title">Form</span><span class="ordering-information-data form">(.*)</span>#iUs';

        $preg_unique['size'] = '#<span class="ordering-information-title sizes-title">Size\(s\)</span><span class="ordering-information-data">(.*)</span>#iUs';

        //Datasheet
        //Summary  | Species ,Tested Applications ,Clonality ,Gene,Host ,Purity,Specificity,Preparation Method,Endotoxin Note,Guarantee Plus,Description ,Innovator's Reward
        $preg_unique['species'] = '#<td><strong>Species</strong></td><td>(.*)</td>#iUs';
        $preg_unique['tested_applications'] = '#<td><strong>Tested Applications</strong></td><td>(.*)</td>#iUs';
        $preg_unique['clonality'] = '#<td><strong>Clonality</strong></td><td>(.*)</td>#iUs';
        $preg_unique['host'] = '#<td><strong>Host</strong></td><td>(.*)</td>#iUs';
        $preg_unique['gene'] = '#<td><strong>Gene</strong></td><td>(.*)</td>#iUs';
        $preg_unique['purity'] = '#<td><strong>Purity</strong></td><td>(.*)</td>#iUs';
        $preg_unique['specificity'] = '#<td><strong>Specificity</strong></td><td>(.*)</td>#iUs';

        $preg_unique['preparation_method'] = '#<td><strong>Preparation<br />Method</strong></td><td>(.*)</td>#iUs';
        $preg_unique['endotoxin_note'] = '#<td><strong>Endotoxin Note</strong></td><td>(.*)</td>#iUs';
        $preg_unique['guarantee_plus'] = '#<td><strong>Guarantee Plus</strong></td><td>(.*)</td>#iUs';
        $preg_unique['description'] = '#<td><strong>Description</strong></td><td>(.*)</td>#iUs';
        $preg_unique['innovator_reward'] = '#<td><strong>Innovator\'s Reward</strong></td><td>(.*)</td>#iUs';
        //Details  | Format,Isotype,Immunogen,Publications ,Localization,Details of Functionality,Clone
        $preg_unique['immunogen'] = '#<td><strong>Immunogen</strong></td><td>(.*)</td>#iUs';
        $preg_unique['localization'] = '#<td><strong>Localization</strong></td><td>(.*)</td>#iUs';
        $preg_unique['clone'] = '#<td><strong>Clone</strong></td><td>(.*)</td>#iUs';
        $preg_unique['format'] = '#<td><strong>Format</strong></td><td>(.*)</td>#iUs';
        $preg_unique['isotype'] = '#<td><strong>Isotype</strong></td><td>(.*)</td>#iUs';
        $preg_unique['details_of_functionality'] = '#<td><strong>Details of Functionality</strong></td><td>(.*)</td>#iUs';
        //Species Identity | Publications 
        $preg_unique['publications'] = '#<td><strong>Publications</strong></td><td>(.*)</td>#iUs';
        //Applications/Dilutions | Dilutions ,Publications,Application Notes ,Positive Controls, Localization?,Molecular Weight 
        $preg_unique['dilutions'] = '#<td><strong>Dilutions</strong></td><td>(.*)</td>#iUs';
        $preg_unique['application_notes'] = '#<td><strong>Application Notes</strong></td><td>(.*)</td>#iUs';
        $preg_unique['positive_controls'] = '#<td><strong>Positive Controls</strong></td><td>(.*)</td>#iUs';
        $preg_unique['publications'] = '#<td><strong>Publications</strong></td><td>(.*)</td>#iUs';
        $preg_unique['molecular_weight'] = '#<td><strong>Molecular Weight</strong></td><td>(.*)</td>#iUs';
        //Packaging, Storage & Formulations | Storage ,Buffer ,Unit Size ,Concentration ,Preservative ,Limitations ,Formulation ,Reconstitution Instructions 
        $preg_unique['storage'] = '#<td style="white-space:normal;"><strong>Storage</strong></td><td>(.*)</td>#iUs';
        $preg_unique['buffer'] = '#<td style="white-space:normal;"><strong>Buffer</strong></td><td>(.*)</td>#iUs';
        $preg_unique['unit_size'] = '#<td style="white-space:normal;"><strong>Unit Size</strong></td><td>(.*)</td>#iUs';
        $preg_unique['concentration'] = '#<td style="white-space:normal;"><strong>Concentration</strong></td><td>(.*)</td>#iUs';
        $preg_unique['preservative'] = '#<td style="white-space:normal;"><strong>Preservative</strong></td><td>(.*)</td>#iUs';
        $preg_unique['limitations'] = '#<td style="white-space:normal;"><strong>Limitations</strong></td><td>(.*)</td>#iUs';
        //??未定
        $preg_unique['formulation'] = '#<td style="white-space:normal;"><strong>Formulation</strong></td><td>(.*)</td>#iUs';
        $preg_unique['reconstitution_instructions'] = '#<td style="white-space:normal;"><strong>Reconstitution Instructions</strong></td><td>(.*)</td>#iUs';
        //Bioinformatics |Gene Symbol,Entrez,Uniprot
        $preg_unique['gene_symbol'] = '#<td class="bold">Gene Symbol</td><td>(.*)</td>#iUs';
        $preg_unique['entrez'] = '#<td valign="top"class="bold bio-td">Entrez</td><td valign="top"class="wider">(.*)</td>#iUs';
        $preg['uniprot'] = '#<td valign="top"class="bold bio-td">Uniprot</td><td valign="top"class="wider">(.*)<a(.*)rel="nofollow"class="inline bioinf-a">(.*)</a>(.*)</td>#iUs';
        //Background
        $preg_unique['background'] = '#Background</h2><div class="prod-cntnt-sect">(.*)</div>#iUs';

        //图片

        $preg['image_arr'] = '#<a(.*)rel="prettyPhoto\[prettyGalleryProduct\]"title="(.*)"><img src="(.*)"(.*)/></a>#iUs';
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

    //2014-11-3查询替换url后的产品存在？true："导出"
    public function ck_url_exist()
    {
        //   exit("set path!");
        $excel_name = "change_url_test.xlsx";
        $this->load->helper('common');
        $file_data['full_path'] = 'D:/wamp/www/test/everest_old/' . $excel_name;
        if (!file_exists($file_data['full_path']))
        {
            exit('no file!');
        }
        $excel_data = reade_excel($file_data['full_path'], '.xlsx');
        unset($excel_data[0]);
        $export = array();
        foreach ($excel_data as $k => $v)
        {
            $content = file_get_contents($v[1]);
            $content = compress_html($content);
            if ($content)
            {
                preg_match_all('#<div id="product-title"><h1>(.*)</h1>#iUs', $content, $pre_arr);
                if (!isset($title[1][0]))
                {
                    $export_data[$k]['no_url_catalog'] = $v[2];
                }
            }
        }
        $export_arr = array(
            'no_url_catalog' => 'no_url_catalog',
        );
        export_excel($export_arr, $export_data, 'file_' . time());
    }

}
