<?php 
    //Codeigniter Model

    //Search for products and return results.
    //Requires per page limit and offset for pagination
    //Create session for inputs to keep pagination active for the view results
    function search_products($perPAGE,$offset){

        //SELECT
        $select = 'tbl_products.id,tbl_products.pname,';
        $select .= 'tbl_brands.name,tbl_products.gender,';
        $select .= 'tbl_products.strapType,tbl_products.colour,';
        $select .= 'tbl_products.description,';
        $select .= 'tbl_products.filename,tbl_products.price';

        $this->db->select($select);
        
        //If submit_search isset
        //Create array from the forms inputs and session the array
        if($this->input->post('submit_search')){

            //validate search in put and create array for session
            if($this->validate_search()){

                $arr = array(
                    'gender'    => $this->input->post('gender'),
                    'strapType' => $this->input->post('strapType'),
                    'brand'     => $this->input->post('brand'),
                    'priceOne'  => substr($this->input->post('value_one'),1),//remove '$' //price range
                    'priceTwo'  => substr($this->input->post('value_two'),1),//remove '$' //price range
                );
            
                $this->session->set_userdata('product_search',$arr);

            }
        }

        //If this session('product_search') is available.
        //If sessions element is equal to ZERO "DON'T" run its WHERE statement
        //If its not ZERO, client has requested a search related to the sessions element 
        //eg. Client has picked a gender but nothing else. Only gender WHERE statement will run

        //if price search is less than 1000 run the price as requested by user
        //else the user has selected the max which is a 1000 then search DB for products over 1000
        if($this->session->userdata('product_search')){

            $results = $this->session->userdata('product_search');

            if(!$results['gender'] == 0){
                $this->db->where('tbl_products.gender',$results['gender']);
            }

            if(!$results['strapType']== 0){
                $this->db->where('tbl_products.strapType',$results['strapType']);
            }

            if(!$results['brand'] == 0){
                $this->db->where('tbl_products.brandID',$results['brand']);
            }
            
            if(!$results['priceTwo'] == 0){

                if($results['priceTwo'] < 1000){  
                    $this->db->where("(`tbl_products`.`price` BETWEEN ".$results['priceOne']." AND ".$results['priceTwo'].")", NULL, FALSE); 
                }else{
                    $this->db->where("(`tbl_products`.`price` BETWEEN ".$results['priceOne']." AND 1000000)", NULL, FALSE); 
                }
            }
        }

        $this->db->from('tbl_products');
        $this->db->join('tbl_brands', 'tbl_brands.id = tbl_products.brandID');

        //Get total num rows before LIMIT statement executes
        $tempdb = clone $this->db;
        $numrows = $tempdb->count_all_results();

        $this->db->limit($perPAGE,$offset);
        $query = $this->db->get();

        return array($query->result_array(),$numrows);

    }//EOF search_products()