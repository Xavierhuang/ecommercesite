<?php
/**
 * Email queue processor for cron.
 * Call with token to process pending queue (no login required).
 * Set config: tool_email_queue_token in oc_setting (store_id=0) to a secret; use same in cron URL.
 */
class ControllerToolEmailQueue extends Controller {
	public function process() {
		$token = isset($this->request->get['token']) ? $this->request->get['token'] : '';
		$expected = $this->config->get('tool_email_queue_token');
		if (empty($expected) || $token !== $expected) {
			$this->response->addHeader('HTTP/1.1 403 Forbidden');
			$this->response->setOutput('Forbidden');
			return;
		}
		require_once(DIR_SYSTEM . 'library/email_notification_manager.php');
		$manager = new EmailNotificationManager($this->config, $this->db, $this->log);
		$limit = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : 50;
		$manager->processQueue($limit > 0 ? $limit : 50);
		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->setOutput(json_encode(['processed' => true]));
	}
}
