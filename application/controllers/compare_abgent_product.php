<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
     /*
     * AM                                             * AP
     *                                                   SA	    Peptide Affinity Purifications
            SD  MAB ProteinG Purifications               SD	    Peptide Affinity Purifications
            SDG MAB ProteinG Purifications               
            CA MAB Ascites Antibody ELISA                SDP	Peptide Affinity Purifications
          *  SG MAB ProteinG Purifications                SG 	ProteinG Purifications   
            SM MAB ProteinG Purifications                SH/$	SAS Purifications,China Purifications
            SK MAB ProteinA Purifications                SS	    N/A   
            SH,SG MAB SAS Purifications,MAB ProteinG Purifications  SH/$	SAS Purifications,China Purifications
     */
class compare_abgent_product extends CI_Controller{
        //abgent tables
                 const TBL_PRO_LOTS = 'products_lots';
        //PD tables
                 const TBL_PEP_AFF_PURIF = 'peptide_affinity_purifications';
                 const TBL_PROJECTS      = 'projects';
                 const TBL_TARGETS       = 'project_targets';
                 const TBL_MAB_PG        = 'mab_proteing_purifications';
                 const TBL_MAB_PA        = 'mab_proteina_purifications';    
                 const TBL_PG            = 'proteing_purifications';
                 const TBL_PA            = 'proteina_purifications';
                 const TABL_SAS          = 'sas_purifications';
                 const TABL_FUSIONS      = 'fusions';
                 const TABL_MAB_A_A_ELISA   = 'mab_ascites_antibody_elisas';
                 const TABL_CHINA          = 'china_purifications';
                 const MAB_SAS_PF          = 'mab_sas_purifications';
                 
      function __construct()
      {
             ini_set("memory_limit",'3072M');
             error_reporting(0);
             set_time_limit(0);
             parent::__construct();
      }
      
      private function _abgent_link()
      {
           return  $this->load->database('abgent_product',TRUE);
      }

      private function _pd_link()
    {
         return $this->load->database('pd', TRUE);
    }

     //abgent
     public function index()
     {
          $filename = "D:\work-2015\pd_abgent_proCompare/abgent.xlsx";
          $empty_data = array( );
          $excel_data = reade_excel($filename, '.xlsx');
         
          unset($excel_data[0]);
          $i = 0;
          foreach ($excel_data as $k => $v)
          {
                    $catalog = $v[0];
                    $lot_name = $v[1];
                    $clone_name = $v[2];
                    $export_data[$k] = array( 'catalog' => $catalog , 'lot_name' => $lot_name,'clone_name' => $clone_name);
                    if($catalog == '' || $lot_name == '' || $clone_name == '')
                    {
                           $export_data[$k]  += array('is_empty' => 'YES' );
                    }
                   else
                   {
                       $row = $this->query_data( $lot_name );
                       if( !$row )
                       {
                           $export_data[$k]  += array( 'is_ok' => 'lot not exists'  );
                       }
                        else
                       {
                            if( count($row) == 1 )
                            {
                                      if( (strtoupper($clone_name) == strtoupper($row[0]['clone_name']))    && (strtoupper($catalog) == strtoupper($row[0]['catalog']) ) )
                                       {
                                            $export_data[$k]  += array( 'is_ok' => 'YES'  );
                                       }
                                       else
                                       {
                                            $catalog_ok = (strtoupper($catalog) == strtoupper($row[0]['catalog']) ) ? 1:0;
                                            $clone_name_ok = (strtoupper($clone_name) == strtoupper($row[0]['clone_name'])) ? 1:0;
                                            $export_data[$k]  += array('abgent_catalog' => $row[0]['catalog'] , 'abgent_clone_name'=>$row[0]['clone_name'],'catalog_ok'=>$catalog_ok,'clone_name_ok'=>$clone_name_ok,'is_ok' => 'NO'  );
                                       }
                            }
                           else
                           {            
                                     $arrs = $this->chk_array_type($row);
                                     
                                     if( in_array( $clone_name,  $arrs['clone_name'] )&& in_array( $catalog, $arrs['catalog'] ))
                                    {
                                         $export_data[$k]  += array( 'is_ok' => 'YES'  );
                                    }
                                    else
                                    {
                                         $abgent_clone_name = implode('|',$arrs['clone_name'] );
                                         $abgent_catalog = implode('|', $arrs['catalog']);
                                         $catalog_ok =  in_array( $catalog, $arrs['catalog'] ) ? 1:0;
                                         $clone_name_ok = in_array( $clone_name,  $arrs['clone_name'] )? 1:0;
                                         $export_data[$k]  += array( 'abgent_catalog' => $abgent_catalog ,'abgent_clone_name'=>$abgent_clone_name ,'catalog_ok'=>$catalog_ok,'clone_name_ok'=>$clone_name_ok,'is_ok' => 'NO'  );
                                    }
                           }
                       }
                    }
          }
        
         $export_arr = array(
            'catalog' => 'catalog',
            'lot_name' => 'lot_name',
            'clone_name' => 'clone_name',
             
              'is_ok' => 'is_ok',
             'abgent_clone_name'=>'abgent_clone_name',
             'abgent_catalog'=>'abgent_catalog',
             'catalog_ok'=>'catalog_ok',
             'clone_name_ok'=>'clone_name_ok',
             'is_empty' => 'is_empty',
        );
        
       export_excel($export_arr, $export_data, __FUNCTION__  .time());
     }
     private function chk_array_type($arr)
     {
         $arrs = array();
         foreach ($arr as $v)
         {
             $arrs['catalog'][] = $v['catalog'];
             $arrs['clone_name'][]= $v['clone_name']; 
         }
         return $arrs;
     }

     private function query_data( $lot_name )
     {
         return $this->_abgent_link()->select("clone_name,catalog")->where('lot_number',$lot_name)->get( self:: TBL_PRO_LOTS )->result_array();
       
     }
    //pd database
    public function pd_ck()
    {
          $filename = "D:/work-2015/pd_abgent_proCompare/Book1.xlsx"; 
          $empty_data = array( );
          $excel_data = reade_excel($filename, '.xlsx');
         
          unset($excel_data[0]);
          $i = 0;
          foreach ($excel_data as $k => $v)
          {
                    $catalog = $v[0];
                    $lot_name = $v[1];
                    $clone_name = $v[2];
                  
                    if($catalog == '' || $lot_name == '' || $clone_name == '')
                    {
                        $export_data[$k] = array( 'catalog' => $catalog , 'lot_name' => $lot_name,'clone_name' => $clone_name,'is_empty' => 'YES' );
                    }
                    else if( preg_match("/^(AP|AM|AX)/", $catalog) )
                    {
                       $row = $this->query_data_pd( $lot_name );
                        if( !isset($row) )
                       {
                          $export_data[$k]  = array( 'catalog' => $catalog , 'lot_name' => $lot_name,'clone_name' => $clone_name,'is_ok' => 'NO'  );
                       }
                       if( count($row) ==1 )
                       {
                           
                           if( (isset($row[0]['clone_id'])) &&( strtoupper($clone_name) == strtoupper($row[0]['clone_id'])||strpos($clone_name,$row[0]['clone_id'])!==FALSE  ) )
                           {
                                $export_data[$k]  = array( 'catalog' => $catalog , 'lot_name' => $lot_name,'clone_name' => $clone_name,'is_ok' => 'YES'  );
                           }
                           else
                           {
                                $export_data[$k]  = array( 'catalog' => $catalog , 'lot_name' => $lot_name,'clone_name' => $clone_name,'pd_clone_name'=>@$row[0]['clone_id'],'is_ok' => 'NO'  );
                           }
                           
                       }
                    else 
                       {
                                                  
                        if( isset($row)&& count($row) )
                        {
                            $clone_name_arr = array();
                            foreach( $row as $vv)
                            {
                                $clone_name_arr[] = $vv['clone_id'];
                            }
                           if( in_array($clone_name, $clone_name_arr)  )
                           {
                                $export_data[$k]  = array( 'catalog' => $catalog , 'lot_name' => $lot_name,'clone_name' => $clone_name,'is_ok' => 'YES'  );
                           }
                           else
                           {
                                $other_clone_name = implode('|',$clone_name_arr);
                                $export_data[$k]  = array( 'catalog' => $catalog , 'lot_name' => $lot_name,'clone_name' =>$clone_name,'pd_clone_name'=>$other_clone_name,'is_ok' => 'NO'  );
                           } 
                       }
                       }
                    }else{
                         $export_data[$k]  = array( 'catalog' => $catalog , 'lot_name' => $lot_name,'clone_name' =>$clone_name,'is_ok' => 'NO'  );
                    }
                  
          }
        
            $export_arr = array(
                                'catalog' => 'catalog',
                                'lot_name' => 'lot_name',
                                'clone_name' => 'clone_name',
                                 'is_ok' => 'is_ok',
                                'pd_clone_name' =>'pd_clone_name',
                                'is_empty' => 'is_empty',
                               );
                         
     export_excel($export_arr, $export_data, __FUNCTION__  .time());
    }
    public function test(){
            $a =  self::query_data_pd();
            var_dump($a);
    }

    public function query_data_pd( $lot_name  = 'SH020625B')
     {
         if( preg_match("/^SA/", $lot_name) )
         {
             return $this->_pd_animal_by_lot(self::TBL_PEP_AFF_PURIF,$lot_name);
         }
         if( preg_match("/^SD/", $lot_name) )
         {
             $res0 = $this->_pd_clone_by_lot(self::TBL_MAB_PG,$lot_name);
             if($res0)
             {
                 return $res0;
             }
             else
             {  
                return $res1 = $this->_pd_animal_by_lot(self::TBL_PEP_AFF_PURIF,$lot_name);
             }
         }
         if( preg_match("/^CA/", $lot_name) )
         {
               return  $this->_pd_clone_by_lot(self::TABL_MAB_A_A_ELISA,$lot_name);
         }
           if( preg_match("/^SG/", $lot_name) )
         {
                $res2 = $this->_pd_clone_by_lot(self::TBL_MAB_PG,$lot_name);
                if($res2) {
                    return $res2;
                }else {
                    $res3 =  $this->_pd_animal_by_lot( self::TBL_PG,$lot_name );
                if($res3) {
                        return $res3;
                } else {
                    $res4 = $this->_pd_clone_by_lot( self::MAB_SAS_PF,str_replace('SG', 'SH', $lot_name) );
                        if($res4) {
                            return $res4;
                        } else {
                            return $this->_pd_clone_by_lot( self::TBL_MAB_PG,str_replace('SG', '$', $lot_name) );
                        }
                    }
                 }
         }
          if( preg_match("/^SK/", $lot_name) )
         {
              return $this->_pd_clone_by_lot(self::TBL_MAB_PA,$lot_name);
         }
         
          if( preg_match("/^SM/", $lot_name) )
         {
                 return $this->_pd_clone_by_lot(self::TBL_MAB_PG,$lot_name);
         }
     
          if( preg_match("/^SH/", $lot_name) )
         { 
            $res5 = $this->_pd_clone_by_lot( self::MAB_SAS_PF,$lot_name );
            
            if($res5)
            {
              
                return $res5;
            } 
            $res6 = $this->_pd_clone_by_lot( self::TBL_MAB_PG,$lot_name);
            if($res6)
            {   
                 return $res6;
            }
             $res7 = $this->_pd_animal_by_lot( self::TABL_SAS,$lot_name);
            if($res7)
            {
                 return $res7;
            }
         
          
            $res8 = $this->_pd_animal_by_lot( self::TABL_SAS,str_replace('SH', '$', $lot_name));
           
            if($res8)
            {
                 return $res8;
            }
              $res9 = $this->_pd_animal_by_lot( self::TABL_CHINA,$lot_name);
            if($res9)
            {
                 return $res9;
            }
             $res10 = $this->_pd_animal_by_lot( self::TABL_CHINA,str_replace('SH', '$', $lot_name));
            if($res10)
            {
                 return $res10;
            }
          }
               
         else{
             $res[0] = "未匹配到";
             return $res;
         }
         
       
     }
   private function _pd_animal_by_lot($search_table,$lot_name) 
   {
        $res = $this->_pd_link()->select('animal_id as clone_id')->like( 'lot_no',$lot_name,'after' )->get($search_table)->result_array();
        if(!$res)
        {
            return false;
        }
        if(count($res)< 1)
        {
            return false;
        }
        return $res;
    }
  private function _pd_clone_by_lot($search_table,$lot_name)
   {
  
        $res = $this->_pd_link()->select('clone_id')->like( 'lot_no',$lot_name,'after' )->get($search_table)->result_array();
        if(!$res)
        {
            return false;
        }
        if(count($res)< 1)
        {
            return false;
        }
        return $res;
    }
  
    

}