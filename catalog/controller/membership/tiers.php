<?php

class ControllerMembershipTiers extends Controller {
    public function index() {

        $this->load->language('membership/tiers');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['heading_title'] = $this->language->get('heading_title');
        
        // Only load header and footer if you still want the basic structure
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        
        return $this->response->setOutput($this->load->view('membership/tiers', $data));
    }
}