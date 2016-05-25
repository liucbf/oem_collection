<?php

Class Collection_email_from_alzforum extends CI_Controller
{
         private $_url = 'https://www.alzforum.org';
         private $_pageNum =  300;
         private $_startNum = 0;
                 function __construct()
        {
            error_reporting(0);
            set_time_limit(0);
            parent::__construct();
            $this->load->helper('mycollection');
        }
        public function test()
        {     
             $content = format_html( "https://www.alzforum.org/member-directory?page=4" );
              $arrs = array(
                  0,4,6,8,11,12,21,25,27,31,35,36,37,39,40,42,44,46,47,48,49,50,54,57,60,64,73,74,
                  75,76,81,83,85,86,88,91,92,96,100,102,107,110,113,114,120,121,122,127,128,129,132,
                  134,135,136,140,143,144,145,147,149,150,151,153,162,164,168,173,175,180,185,186,190,
                  193,194,195,199,201,204,209,215,216,218,220,223,224,226,228,229,230,231,234,235,237,238,245,
                  246,250,251,254,255,258,268,269,275,276,277,279,280,286,291,292,300,306,307,308,311,313,314,
                  316,319,326,327,329,335,336,337,343,344,345,346,347,352,362,377,379,380,387,389,391,392,394,
                  395,396,398,399,400,407,409,416,418,419,421,428,430,432,433,434,437,441,442,444,449,450,451,
                  454,455,456,458,460,464,467,471,473,474,475,477,479,485,489,494,495,497,498,499,501,503,510,512,513,
                  514,517,520,527,530,535,536,538,539,540,541,547,548,549,550,555,556,562,568,569,576,580,581,583,585,
                  589,592,594,597,601,602,605,606,610,611,613,616,618,620,622,625,629,630,632,635,639,641,642,644,645,
                  646,647,650,651,655,657,658,660,661,662,663,667,672,673,674,676,678,679,680,681,682,684,693,695,699,
                  708,711,714,716,719,720,726,1,5,7,9,12,13,22,26,28,32,36,37,38,40,41,43,45,47,48,49,50,51,55,58,61,65,74,
                  75,76,77,82,84,86,87,89,92,93,97,101,103,108,111,114,115,121,122,123,128,129,130,133,135,136,137,141,
                  144,145,146,148,150,151,152,154,163,165,169,174,176,181,186,187,191,194,195,196,200,202,205,210,216,217,
                  219,221,224,225,227,229,230,231,232,235,236,238,239,246,247,251,252,255,256,259,269,270,276,277,278,280,
                  281,287,292,293,301,307,308,309,312,314,315,317,320,327,328,330,336,337,338,344,345,346,347,348,353,363,
                  378,380,381,388,390,392,393,395,396,397,399,400,401,408,410,417,419,420,422,429,431,433,434,435,438,442,
                  443,445,450,451,452,455,456,457,459,461,465,468,472,474,475,476,478,480,486,490,495,496,498,499,500,502,504,
                  511,513,514,515,518,521,528,531,536,537,539,540,541,542,548,549,550,551,556,557,563,569,570,577,581,582,584,
                  586,590,593,595,598,602,603,606,607,611,612,614,617,619,621,623,626,630,631,633,636,640,642,643,645,646,647,648,651,652,656,658,659,661,
                  662,663,664,668,673,674,675,677,679,680,681,682,683,685,694,696,700,709,712,715,717,720,721,727,
              );
             $j=0;
             $filename = "C:\Users\li_hao\Desktop/left_email.txt";
             
             file_put_contents($filename,'name,email,num,page \r ');
             foreach( $arrs as $i )
             {
                 echo $i." page is collectioning.... ";
                 $webUrl = $this->_url."/member-directory?page=".$i;
                 $content = format_html( $webUrl );
                 preg_match_all( '#<a class="url fn"href="(.*)"><img class="image"typeof="foaf:Image"src="(.*)"(.*)>(.*)<\/a>#iUs' , $content ,$arr );
                if( count($arr) )
                 {
                  
                     foreach ( $arr[1] as $keys =>$v )
                     {
                         $person[$j]['name'] = trim($arr[4][$keys]);
                         $person_url = $this->_url.$v;
                         $content_person = format_html( $person_url );
                         preg_match( '#<span class="fn">(.*)</span><br/><em>(.*)</em>#iUs' , $content_person ,$arr_person );
                       
                         if(count($arr_person)){
                                 $email = str_replace(array('[at]','[dot]',' '),array('@','.',''), $arr_person[2]);
                                 $person[$j]['email'] = trim($email);
                        }
                       $person[$j]['num']  = count($arr[1]);
                     
                       file_put_contents($filename,$person[$j]['name'].','.$person[$j]['email'].','.$person[$j]['num'].','.$i."\r\n" ,FILE_APPEND);
                      
                       $j++;
                     }
                      $num = count($arr[1]);
                      echo "ok:{$i},co{$num }<br>";
                 }
                 else{
                      echo "error:{$i},no data<br>";
                 }
             }
        }

        public function get_email()
        {
            $person = array();
             $j=0;
             for( $i=$this->_startNum; $i<$this->_pageNum ;$i++ )
             {
                 $webUrl = $this->_url."/member-directory?page=".$i;
                 $content = format_html( $webUrl );
                 preg_match_all( '#<a class="url fn"href="(.*)"><img class="image"src="(.*)"width="70"height="70">(.*)<\/a>#iUs' , $content ,$arr );
                 if( count($arr) )
                 {
                    
                     foreach ( $arr[1] as $keys =>$v )
                     {
                         $person[$j]['name'] = trim($arr[3][$keys]);
                         $person_url = $this->_url.$v;
                         $content_person = format_html( $person_url );
                         preg_match( '#<span class="fn">(.*)</span><br/><em>(.*)</em>#iUs' , $content_person ,$arr_person );
                         if(count($arr_person)){
                                 $email = str_replace(array('[at]','[dot]',' '),array('@','.',''), $arr_person[2]);
                                 $person[$j]['email'] = trim($email);
                       }
                       $person[$j]['num']  = count($arr[1]);
                       $person[$j]['page']  = $i;
                       $j++;
                     }
                 }
             }
              export_excel( array( 'name' => 'name', 'email' => 'email','num'=>'num','page'=>'page'), 
                         $person,
                         '__email_name'
                         );
        }
           public function get_email_to_csv()
        {
            $person = array();
             $j=0;
             $filename = "C:\Users\li_hao\Desktop/email__.txt";
             
             file_put_contents($filename,'name,email,num,page'.'\r\n');
             
             for( $i=0; $i<729 ;$i++ )
             {
                 echo $i." page is collectioning.... ";
                 $webUrl = $this->_url."/member-directory?page=".$i;
                 $content = format_html( $webUrl );
                 preg_match_all( '#<a class="url fn"href="(.*)"><img class="image"src="(.*)"(.*)>(.*)<\/a>#iUs' , $content ,$arr );
                 
                if( count($arr) )
                 {
                  
                     foreach ( $arr[1] as $keys =>$v )
                     {
                         $person[$j]['name'] = trim($arr[3][$keys]);
                         $person_url = $this->_url.$v;
                         $content_person = format_html( $person_url );
                         preg_match( '#<span class="fn">(.*)</span><br/><em>(.*)</em>#iUs' , $content_person ,$arr_person );
                         if(count($arr_person)){
                                 $email = str_replace(array('[at]','[dot]',' '),array('@','.',''), $arr_person[2]);
                                 $person[$j]['email'] = trim($email);
                        }
                       $person[$j]['num']  = count($arr[1]);
                     
                       file_put_contents($filename,$person[$j]['name'].','.$person[$j]['email'].','.$person[$j]['num'].','.$i."\r\n" ,FILE_APPEND);
                      
                       $j++;
                     }
                      $num = count($arr[1]);
                      echo "ok:{$i},co{$num }<br>";
                 }
                 else{
                      echo "error:{$i},no data<br>";
                 }
             }
        }
        
     
}