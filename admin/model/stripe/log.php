<?php
class ModelStripeLog extends Model
{
    public function getLogs($data = array())
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "order_stripe_webhook_log WHERE log_id > 0";

        if (isset($data['filter_start_date']) && !is_null($data['filter_start_date'])) {
            $sql .= " AND DATE(date_added) >= DATE('" . $this->db->escape($data['filter_start_date']) . "')";
        }

        if (isset($data['filter_end_date']) && !is_null($data['filter_end_date'])) {
            $sql .= " AND DATE(date_added) <= DATE('" . $this->db->escape($data['filter_end_date']) . "')";
        }

        $sql .= " ORDER BY date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalLogs($data = array())
    {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "order_stripe_webhook_log WHERE log_id > 0";

        if (isset($data['filter_start_date']) && !is_null($data['filter_start_date'])) {
            $sql .= " AND DATE(date_added) >= DATE('" . $this->db->escape($data['filter_start_date']) . "')";
        }

        if (isset($data['filter_end_date']) && !is_null($data['filter_end_date'])) {
            $sql .= " AND DATE(date_added) <= DATE('" . $this->db->escape($data['filter_end_date']) . "')";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }
}
