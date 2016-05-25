<?php

class Caiji extends CI_Controller {

            private $_local_path = "D:/work-2015/caiji_template/temp/";
            private $_local_temp = "D:/work-2015/caiji_template/file_2_excel/";
            private $_company_arr = array(
                                        'Abnova' ,
                                        'Avas' ,
             //                         'Biogems' ,
                                        'Everest' ,
                                        'Merdianlifescience',
                                        'Novus' ,
                                        'Proteins_Peptides' ,
                                        'Rockland' ,
             //                     'Tonbo' 
                     );
             private $_path;
             private $_company_control_arr = array(
                'Abnova' => 'collection_abnova',
                'Avas' => 'collection_avas',
               // 'Biogems' => 'collection_biogems',
                'Everest' => 'collection_everest',
                'Merdianlifescience' => 'collection_merdianlifescience',
                'Novus' => 'collection_novus',
                'Proteins Peptides' => 'collection_proteins_peptides',
                'Rockland' => 'collection_rockland',
                'Tonbo' => 'collection_tonbo'
    );

    function __construct()
    {
        error_reporting(0);
        set_time_limit(0);
        parent::__construct();
    }

    public function index()
    {
        $data['server_path'] = $this->_local_path ;
        $data['company_arr']  = $this->_company_arr ;
        $this->load->view('main', $data);
    }
    //脚本
    public function work()
    {
        $post = $this->input->post(null, TRUE);  
        //job:set_image_type_from_legend
        if ( isset($post['submit1'])&&$post['set_image_type_from_legend'])
        {
            $input_file = $this->input->post('set_image_type_from_legend');
            $this->ck_file($input_file);
            set_image_type_from_legend($this->_path);
           
        }
        //job:set_image_type_from_legend
        if (isset($post['submit2'])&&$post['rename_image_name_by_catalog_and_type'])
        {
            $input_file = $this->input->post('rename_image_name_by_catalog_and_type');
            $this->ck_file($input_file);
            rename_image_name_by_catalog_and_type($this->_path);
        }
        //job:set_image_type_from_legend
        if (isset($post['submit3'])&&$post['change_image_name_by_catalog'])
        {
            $input_file = $this->input->post('change_image_name_by_catalog');
            $this->ck_file($input_file);
            change_image_name_by_catalog($this->_path, $origin_image_path = "D:/work-2015/caiji_template/origin_image/", $origin_image_path = "D:/work-2015/caiji_template/change_image/");
        }
        //job:set_image_type_from_legend
        if (isset($post['submit4'])&&$post['replace_catalog'])
        {
            $input_file = $this->input->post('replace_catalog');
            $this->ck_file($input_file);
            replace_catalog($this->_path);
        }
       //genid => 获取uniprot id
      if (isset($post['submit5'])&&$post['uniprot_id_by_genid'])
      {
            $input_file = $this->input->post('uniprot_id_by_genid');
            $this->ck_file($input_file);
            redirect( 'get_uniproid_by_genid/work/'.base64_encode($this->_path));
      }
       //讲本地文件名放到文件夹下面导出文件名到excel
      if (isset($post['submit6']))
      {
            
           if(!is_dir($this->_local_temp))
           {
               exit('文件夹不存在!');
           }
           $this->load->helper('file');
           $file_names = get_filenames($this->_local_temp);
           if( count($file_names) )
           {
               foreach ($file_names as $v)
               {
                   $file_arr[]['name'] = $v;
               }
               export_excel( array('name'=>'name'),$file_arr,'temp_filename_');
           }
      }
       //多行合并为单行 支持4列
      if (isset($post['submit7']))
      {
            $input_file = $this->input->post('rows_file');
            $this->ck_file($input_file);
            mutiple_2_row($this->_path);
      }
        
    }
    private function  ck_file($input_file)
    {
            $this->_path = $this->_local_path . $input_file;
            if (!file_exists($this->_path))
            {
                exit("file not found!");
            }
            return $this->_path;
    }
    //选择公司采集
    public function select_company()
    {
        $post = $this->input->post( NULL,TRUE );
        $company_arr = $this->_company_control_arr;
        if( isset($company_arr[$post['company_type']]) )
        {
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
                    $txt_data = file_get_contents( $file['full_path'] );
                    $this->get_product_detail_from_url( $file['full_path'] );
                    redirect($company_arr[$post['company_type']].'/export_data/'.$txt_data) ;
              }
             else
             {
                  redirect($company_arr[$post['company_type']].'/export_data/'.  base64_encode(trim($post['url_input']) ));
                 
             }
        }
    }
    
    public function chekimg(){
        $excel_data = reade_excel('D:\work-2015\problem/1.xls', '.xls' );
        $cols = $excel_data[0];
        unset($excel_data[0]);
     
        foreach ($excel_data as $k => $item)
        {
           
            for($i=2;$i<22;$i++)

           if(!file_exists('D:\work-2015\problem/1297/'.$item[$i]))
           {
                    echo $item[0]." 不存在<br>";
           }       
        }
    }
    
}
