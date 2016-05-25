<?php
 if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Prelease tmpl Model Class
 *
 * @package ptd
 * @author lh
 **/

class Uniprot_model extends CI_Model
{
    const TBL_UNIPROT = 'uniprot';
    const TBL_UNIPROT_HUMAN = 'human';

    function __construct()
    {
        parent::__construct();
    }
    //get other smiliar sequence
    function query_rows( $sequence = NULL,$uniprot_id )
    {
        if( !$sequence )
        {
            return FALSE;
        }
        $result =  $this->db->select("accession")->where('accession !=',$uniprot_id )->like( 'sequence',$sequence )->get( self::TBL_UNIPROT_HUMAN )->result_array();
        return $result;
    }
    
     //get one row by accession num
    function query_one_row( $uniprot_id )
    {
        if( !$uniprot_id )
        {
            return FALSE;
        }
        $result =  $this->db->select("sequence")->where( 'accession',$uniprot_id )->get( self::TBL_UNIPROT )->row_array();
        return $result;
    }
}