<?php
/**
 * USPS OAuth2 + Domestic Prices v3 (REST) helper for OpenCart.
 * Token is cached using the registry cache driver.
 */
class UspsRest {
	private $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * @param array $o Keys: client_id, client_secret, use_tem (bool), origin_zip, dest_zip,
	 *                 weight (lb), length, width, height (inches), mail_class, processing_category,
	 *                 rate_indicator, destination_entry_facility_type, price_type, debug (bool)
	 * @return array{ok:bool, cost:float, title:string, error:string, raw:array|null}
	 */
	public function getDomesticBaseRate(array $o) {
		$required = array('client_id', 'client_secret', 'origin_zip', 'dest_zip', 'weight');
		foreach ($required as $k) {
			if (!isset($o[$k]) || $o[$k] === '') {
				return array('ok' => false, 'cost' => 0.0, 'title' => '', 'error' => 'Missing: ' . $k, 'raw' => null);
			}
		}

		$token = $this->getAccessToken($o['client_id'], $o['client_secret'], !empty($o['use_tem']), !empty($o['debug']));
		if ($token === false) {
			return array('ok' => false, 'cost' => 0.0, 'title' => '', 'error' => 'OAuth token request failed', 'raw' => null);
		}

		$api_base = !empty($o['use_tem']) ? 'https://apis-tem.usps.com' : 'https://apis.usps.com';
		$url = $api_base . '/prices/v3/base-rates/search';

		$weight = (float)$o['weight'];
		if ($weight < 0.1) {
			$weight = 0.1;
		}

		$body = array(
			'originZIPCode'                => $this->zip5($o['origin_zip']),
			'destinationZIPCode'         => $this->zip5($o['dest_zip']),
			'weight'                     => $weight,
			'length'                     => (float)$o['length'],
			'width'                      => (float)$o['width'],
			'height'                     => (float)$o['height'],
			'mailClass'                  => isset($o['mail_class']) ? $o['mail_class'] : 'USPS_GROUND_ADVANTAGE',
			'processingCategory'         => isset($o['processing_category']) ? $o['processing_category'] : 'MACHINABLE',
			'rateIndicator'              => isset($o['rate_indicator']) ? $o['rate_indicator'] : 'SP',
			'destinationEntryFacilityType' => isset($o['destination_entry_facility_type']) ? $o['destination_entry_facility_type'] : 'NONE',
			'priceType'                  => isset($o['price_type']) ? $o['price_type'] : 'RETAIL',
			'hasNonstandardCharacteristics' => false,
		);

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => json_encode($body),
			CURLOPT_HTTPHEADER     => array(
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Bearer ' . $token,
			),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 20,
		));

		$response = curl_exec($ch);
		$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err = curl_error($ch);

		if ($err) {
			if (!empty($o['debug'])) {
				$this->registry->get('log')->write('USPS REST rates cURL: ' . $err);
			}
			return array('ok' => false, 'cost' => 0.0, 'title' => '', 'error' => $err, 'raw' => null);
		}

		$data = json_decode($response, true);
		if (!empty($o['debug'])) {
			$this->registry->get('log')->write('USPS REST rates HTTP ' . $http . ' body: ' . substr((string)$response, 0, 2000));
		}

		if ($http !== 200 || !is_array($data)) {
			$msg = '';
			if (is_array($data) && !empty($data['message'])) {
				$msg = $data['message'];
			} elseif (is_array($data) && !empty($data['error'])) {
				$msg = $data['error'];
			}
			return array('ok' => false, 'cost' => 0.0, 'title' => '', 'error' => $msg !== '' ? $msg : 'HTTP ' . $http, 'raw' => $data);
		}

		$cost = 0.0;
		if (isset($data['totalBasePrice'])) {
			$cost = (float)$data['totalBasePrice'];
		} elseif (!empty($data['rates'][0]['price'])) {
			$cost = (float)$data['rates'][0]['price'];
		}

		$title = '';
		if (!empty($data['rates'][0]['description'])) {
			$title = $data['rates'][0]['description'];
		} elseif (!empty($data['rates'][0]['mailClass'])) {
			$title = $data['rates'][0]['mailClass'];
		} else {
			$title = 'USPS';
		}

		return array('ok' => true, 'cost' => $cost, 'title' => $title, 'error' => '', 'raw' => $data);
	}

	private function zip5($zip) {
		$z = preg_replace('/\D/', '', (string)$zip);
		return substr($z, 0, 5);
	}

	private function getAccessToken($client_id, $secret, $use_tem, $debug) {
		$cache = $this->registry->get('cache');
		$key = 'usps.' . md5($client_id . ($use_tem ? 'tem' : 'prod'));
		$cached = $cache->get($key);
		if (is_array($cached) && !empty($cached['access_token']) && !empty($cached['expires_at']) && (int)$cached['expires_at'] > time() + 120) {
			return $cached['access_token'];
		}

		$token_url = $use_tem ? 'https://apis-tem.usps.com/oauth2/v3/token' : 'https://apis.usps.com/oauth2/v3/token';
		$params = array(
			'grant_type'    => 'client_credentials',
			'client_id'     => $client_id,
			'client_secret' => $secret,
		);

		$attempts = array(
			array(
				'body' => json_encode($params),
				'headers' => array('Content-Type: application/json', 'Accept: application/json'),
			),
			array(
				'body' => http_build_query($params),
				'headers' => array('Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'),
			),
		);

		$response = '';
		$http = 0;
		foreach ($attempts as $attempt) {
			$ch = curl_init($token_url);
			curl_setopt_array($ch, array(
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => $attempt['body'],
				CURLOPT_HTTPHEADER     => $attempt['headers'],
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => 15,
			));
			$response = curl_exec($ch);
			$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$err = curl_error($ch);
			if ($err) {
				if ($debug) {
					$this->registry->get('log')->write('USPS REST OAuth cURL: ' . $err);
				}
				return false;
			}
			if ($http === 200) {
				break;
			}
		}

		$data = json_decode($response, true);
		if ($http !== 200 || !is_array($data) || empty($data['access_token'])) {
			if ($debug) {
				$this->registry->get('log')->write('USPS REST OAuth failed HTTP ' . $http . ' ' . substr((string)$response, 0, 500));
			}
			return false;
		}

		$expires_in = isset($data['expires_in']) ? (int)$data['expires_in'] : 3600;
		$cache->set($key, array(
			'access_token' => $data['access_token'],
			'expires_at'   => time() + $expires_in - 120,
		));

		return $data['access_token'];
	}
}
