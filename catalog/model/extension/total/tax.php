<?php
class ModelExtensionTotalTax extends Model {
	public function getTotal($total) {
		// Reseller tax exemption (scope 2.3(E)): if buyer has approved exemption, do not add tax
		if ($this->customer->isLogged()) {
			if (!class_exists('TaxExemptionHelper')) {
				require_once(DIR_SYSTEM . 'library/tax_exemption_helper.php');
			}
			$helper = new TaxExemptionHelper($this->db, $this->config);
			if ($helper->hasValidExemption($this->customer->getId())) {
				$total['taxes'] = array();
			}
		}

		foreach ($total['taxes'] as $key => $value) {
			if ($value > 0) {
				$total['totals'][] = array(
					'code'       => 'tax',
					'title'      => $this->tax->getRateName($key),
					'value'      => $value,
					'sort_order' => $this->config->get('total_tax_sort_order')
				);

				$total['total'] += $value;
			}
		}
	}
}