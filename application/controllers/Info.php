<?php

/**
 * Controller to Get information
 *
 * @author Morris
 */
class Info extends CI_Controller{

    function index() {
$this->view();
       // $this->load->view('home');
    }
function view($page='home') {

        $this->load->view($page);
    }
}

?>
