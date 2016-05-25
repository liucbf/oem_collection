<?php

/* 
 * collection_stress_marq_biosciences rules control
 * 
 * 
 */
class collection_stress_marq_biosciences extends CI_Controller
{
    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
    }
    //输入地址或者导入txt文件
    function index1()
    {
        
        $contents = file_get_contents( "C:\Users\li_hao\Desktop\StressMarq oem/Polyclonals.txt");
        $product_arr = explode('http', $contents);
        $i = 0;
        foreach ($product_arr as $k => $v)
        {
             $each_url = 'http' . trim($v);
             $export_data[$i] = self::get_base_data($each_url);
             $i++;
        }
        $export_arr = array(
            'product_name'=>'product_name',
            'original_catalog'=>'original_catalog',
            'size'=>'size',
            'type'=>'type',
            'description'=>'description',
            'research_area'=>'research_area',
            'alternative_names'=>'alternative_names',
            'cas_number'=>'cas_number',
            'formula'=>'formula',
            'molecular_weight'=>'molecular_weight',
            'host'=>'host',
            'purity'=>'purity',
            'solubility'=>'solubility',
            'appearance'=>'appearance',
            'storage'=>'storage',
            'shipping'=>'shipping',
            'background'=>'background',
            'references'=>'references',
            'clone_number'=>'clone_number',
            'host_species'=>'host_species',
            'isotype'=>'isotype',
             'immunogen'=>'immunogen',
             'applications'=>'applications',
             'accession_number'=>'accession_number',
              'swissprot'=>'swissprot',
             'background_info'=>'background_info',
             'species_reactivity'=>'species_reactivity',
             'recommended_dilutions'=>'recommended_dilutions',
             'form'=>'form',
             'storage_buffer'=>'storage_buffer',
            'concentration'=>'concentration',
             'certificate_of_analysis'=>'certificate_of_analysis',
            
        );
        for($i = 1;$i<21 ;$i++ )
        {
            $export_arr['Image_'.$i] = 'Image_'.$i;
            $export_arr['Type_'.$i] = 'Type_'.$i;
            $export_arr['Legend_'.$i] = 'Legend_'.$i;
        }
        export_excel($export_arr, $export_data, 'stress_marq___' .date('YmdHis'));
        //  create_cvs( $export_arr ,$export_data,$file_name = 'D:/wamp/www/test/abcam/child_url/finished/'.$name.'.csv');
    }

    public function get_base_data($url  = '')
    {
        $website_url = "http://www.stressmarq.com/";
        $product_get_url = $url?$url:"http://www.stressmarq.com/Products/Antibodies/SPC-155F.aspx";
        $product = array(); //产品总数组
        $product['url'] = $product_get_url;
        $content = format_html($product_get_url);
        if (empty($content))
        {
            $empty_product[] = $url;
        }
        else
        {
            //基本信息
              $product_name = self::set_base_rules('product_name', $content);
             
              if ($product_name)
              {
                $product['product_name'] = $product_name;
                $product['original_catalog']= self::set_base_rules('original_catalog', $content);
                $product['size']= self::set_base_rules('size', $content);
                $product['type']= self::set_base_rules('type', $content);
                $product['description']= self::set_base_rules('description', $content);
                $product['research_area']= self::set_base_rules('research_area', $content);
                $product['alternative_names']= self::set_base_rules('alternative_names', $content);
                $product['cas_number']= self::set_base_rules('cas_number', $content);
                $product['formula']= self::set_base_rules('formula', $content);
                $product['molecular_weight']= self::set_base_rules('molecular_weight', $content);
                $product['host']= self::set_base_rules('host', $content);
                $product['purity']= self::set_base_rules('purity', $content);
                $product['solubility']= self::set_base_rules('solubility', $content);
                $product['appearance']= self::set_base_rules('appearance', $content);
                $product['storage']= self::set_base_rules('storage', $content);
                $product['shipping']= self::set_base_rules('shipping', $content);
                $product['background']= self::set_base_rules('background', $content);
                $product['references']= self::set_base_rules('references', $content);
                
                $product['clone_number'] = self::set_base_rules('clone_number', $content);
                $product['host_species'] = self::set_base_rules('host_species', $content);
                $product['isotype'] = self::set_base_rules('isotype', $content);
                $product['immunogen'] = self::set_base_rules('immunogen', $content);
                $product['applications'] = self::set_base_rules('applications', $content);
                $product['accession_number'] = self::set_base_rules('accession_number', $content);
                $product['swissprot'] = self::set_base_rules('swissprot', $content);
                $product['background_info'] = self::set_base_rules('background_info', $content);
                $product['species_reactivity'] = self::set_base_rules('species_reactivity', $content);
                $product['recommended_dilutions'] = self::set_base_rules('recommended_dilutions', $content);
                $product['form'] = self::set_base_rules('form', $content);
                $product['storage_buffer'] = self::set_base_rules('storage_buffer', $content);
                $product['concentration'] = self::set_base_rules('concentration', $content);
                $product['certificate_of_analysis'] = self::set_base_rules('certificate_of_analysis', $content);
                
                    $image_match_all = self::set_base_rules('image_match_all', $content);
                    if(count($image_match_all))
                    {
                      $imgs = self::set_base_rules('image', $image_match_all[1][0]);
                      if(count($imgs))
                      {
                          $i =1;
                          foreach ($imgs[2] as $k=>$v)
                          {
                              $product['Image_'.$i] = $v;
                              $product['Legend_'.$i] = $imgs[4][$k];
                              $i++;
                          }
                      }
                   }
                    $image_match_all2 = self::set_base_rules('image_match_all2', $content);
                    
                    if(count($image_match_all2))
                    {
                      $imgs = self::set_base_rules('image2', $image_match_all2[1][0]);
                      if(count($imgs))
                      {
                          $i =1;
                          foreach ($imgs[2] as $k=>$v)
                          {
                              $product['Image_'.$i] = $v;
                              $product['Legend_'.$i] = $imgs[4][$k];
                              $i++;
                          }
                      }
                   }
                    $image_match_all4 = self::set_base_rules('image_match_all4', $content);
                    if(count($image_match_all4))
                    {
                      $imgs4 = self::set_base_rules('image4', $image_match_all4[1][0]);
                      if(count($imgs4))
                      {
                          $i =1;
                          foreach ($imgs4[2] as $k=>$v)
                          {
                              $product['Image_'.$i] = $v;
                              $product['Legend_'.$i] = $imgs[4][$k];
                              $i++;
                          }
                      }
                   }
             }
        } 
        
        
        return $product;
    }


    public function set_base_rules($param = NULL, $content)
    {
        //Title
        $preg_unique['product_name'] = '#<dt>ProductName</dt><dd>(.*)</dd>#iUs';
        //Specification 
        $preg_unique['original_catalog'] = '#<dt>Catalog\#</dt><dd>(.*)</dd>#iUs';
        $preg_unique['size'] = '#<dt>Size</dt><dd>(.*)</dd>#iUs';
        $preg_unique['type'] = '#<dt>Type</dt><dd>(.*)</dd>#iUs';
        $preg_unique['description'] = '#<dt>Description</dt><dd>(.*)</dd>#iUs';
        $preg_unique['research_area'] = '#<dt>ResearchArea</dt><dd>(.*)</dd>#iUs';
        $preg_unique['alternative_names'] = '#<dt>AlternativeNames</dt><dd>(.*)</dd>#iUs';
        $preg_unique['cas_number'] = '#<dt>CASNumber</dt><dd>(.*)</dd>#iUs';
        $preg_unique['formula'] = '#<dt>Formula</dt><dd>(.*)</dd>#iUs';
        $preg_unique['molecular_weight'] = '#<dt>Molecularweight</dt><dd>(.*)</dd>#iUs';
        $preg_unique['host'] = '#<dt>Source\/Host</dt><dd>(.*)</dd>#iUs';
        $preg_unique['purity'] = '#<dt>Purity</dt><dd>(.*)</dd>#iUs';
        $preg_unique['solubility'] = '#<dt>Solubility</dt><dd>(.*)</dd>#iUs';
        $preg_unique['appearance'] = '#<dt>Appearance</dt><dd>(.*)</dd>#iUs';
        $preg_unique['storage'] = '#<dt>StorageTemp</dt><dd>(.*)</dd>#iUs';
        $preg_unique['shipping'] = '#<dt>ShippingTemp</dt><dd>(.*)</dd>#iUs';
        $preg_unique['background'] = '#<dt>ResearchBackground</dt><dd>(.*)</dd>#iUs';
        $preg_unique['references'] = '#<dt>References</dt><dd>(.*)</dd>#iUs';
        
        //不同分类
        $preg_unique['clone_number'] = '#<dt>CloneNumber</dt><dd>(.*)</dd>#iUs';
        $preg_unique['host_species'] = '#<dt>HostSpecies</dt><dd>(.*)</dd>#iUs';
        $preg_unique['isotype'] = '#<dt>Isotype</dt><dd>(.*)</dd>#iUs';
        $preg_unique['immunogen'] = '#<dt>Immunogen</dt><dd>(.*)</dd>#iUs';
        $preg_unique['applications'] = '#<dt>Applications</dt><dd>(.*)</dd>#iUs';
        $preg_unique['accession_number'] = '#<dt>AccessionNumber</dt><dd>(.*)</dd>#iUs';
        $preg_unique['swissprot'] = '#<dt>SwissProt</dt><dd>(.*)</dd>#iUs';
        $preg_unique['background_info'] = '#<dt>BackgroundInfo</dt><dd>(.*)</dd>#iUs';
        
        $preg_unique['species_reactivity'] = '#<dt>SpeciesReactivity</dt><dd>(.*)</dd>#iUs';
        $preg_unique['recommended_dilutions'] = '#<dt>RecommendedDilutions</dt><dd>(.*)</dd>#iUs';
        $preg_unique['form'] = '#<dt>Form</dt><dd>(.*)</dd>#iUs';
        $preg_unique['storage_buffer'] = '#<dt>StorageBuffer</dt><dd>(.*)</dd>#iUs';
        $preg_unique['concentration'] = '#<dt>Concentration</dt><dd>(.*)</dd>#iUs';
        $preg_unique['certificate_of_analysis'] = '#<dt>CertificateofAnalysis</dt><dd>(.*)</dd>#iUs';
        
        $preg['image_match_all'] = '#<divid="3">(.*)<\/div>#iUs';
        $preg['image'] = '#<img(.*)src="(.*)"(.*)><br\/>(.*)<br\/>#iUs';
        
        
        $preg['image_match_all2'] = '#<divid="2">(.*)<\/div>#iUs';
        $preg['image2'] = '#<img(.*)src="(.*)"(.*)><\/a><br\/>(.*)<br\/>#iUs';
        
        $preg['image_match_all4'] = '#<divid="4">(.*)<\/div>#iUs';
        $preg['image4'] = '#<img(.*)src="(.*)"(.*)><br\/>(.*)<br\/>#iUs';
        $data = array();
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

