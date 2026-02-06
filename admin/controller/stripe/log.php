<?php
class ControllerStripeLog extends Controller
{

    public function index()
    {
        $data = $this->load->language('stripe/log');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['heading_title'] = $this->language->get('heading_title');

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('stripe/log', 'user_token=' . $this->session->data['user_token'], true),
        );

        if (isset($this->request->get['filter_start_date'])) {
            $filter_array['filter_start_date'] = $data['filter_start_date'] = $this->request->get['filter_start_date'];
        } else {
            $filter_array['filter_start_date'] = $data['filter_start_date'] = date('Y-m-d');
        }

        if (isset($this->request->get['filter_end_date'])) {
            $filter_array['filter_end_date'] = $data['filter_end_date'] = $this->request->get['filter_end_date'];
        } else {
            $filter_array['filter_end_date'] = $data['filter_end_date'] = date('Y-m-d');
        }

        $data['user_token'] = $this->session->data['user_token'];

        $data['clear'] = html_entity_decode($this->url->link('stripe/log', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');

        $this->load->model('stripe/log');

        $logs = $this->model_stripe_log->getLogs($filter_array);

        $data['log'] = '';

        foreach ($logs as $log) {
            $data['log'] .= $log['date_added'] . "\t-\t" . $log['comment'] . "\n";
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('stripe/log', $data));
    }
}
