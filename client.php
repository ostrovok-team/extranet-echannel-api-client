<?php

class OstrovokEchannelAPIClient {

	protected $_auth_token = null;
	protected $_private_token = null;

	public function __construct($auth_token, $private_token) {
		$this->_auth_token = $auth_token;
		$this->_private_token = $private_token;
	}

	private function __signaturelizer($data) {
		$is_list = false;
		if (is_array($data)) {
			if (count($data) > 0) {
				if (is_int($data[0])) {
					$is_list = true;
				}
			}
		}

		if (is_array($data) && !$is_list) {
			ksort($data);
			$tmp = array();
			foreach($data as $key => $value) {
				$tmp[] = array($this->__signaturelizer($key), $this->__signaturelizer($data[$key]));
			}
			$result = array();
			foreach($tmp as $key => $value) {
				$result[] = implode("=", $value);
			}
			return implode(";", $result);
		} elseif (is_array($data) && $is_list) {
			$result = array();
			foreach($data as $value) {
				$result[] = $this->__signaturelizer($value);
			}
			$result = implode(";", $result);
			if (count($data) > 1) {
				$result = ("[" . $result . "]");
			}
			return $result;
		} elseif (is_bool($data)) {
			return $data ? "true" : "false";
		}

		return (string)$data;
	}

	private function __get_signature(array $data, $private) {
		$data['private'] = $private;
		return md5($this->__signaturelizer($data));
	}

	private function __call_api($base_url, array $params) {
		$params["token"] = $this->_auth_token;
		$params["sign"] = $this->__get_signature($params, $this->_private_token);
		$final_url = $base_url."?".http_build_query($params);
		return file_get_contents($final_url);
	}

	public function get_hotels($id = null, $limits = null, $page = null, $format = "json", $lang = null) {
		$params = array();
		if ($id) {
			$params["id"] = $id;
		}
		if ($limits) {
			$params["limits"] = $limits;
		}
		if ($page) {
			$params["page"] = $page;
		}
		if ($format) {
			$params["format"] = $format;
		}
		if ($lang) {
			$params["lang"] = $lang;
		}
		return $this->__call_api("https://extratest.ostrovok.ru/echannel/api/v0.1/hotels/", $params);
	}
}

/**
 Usage:
   $api_client = new OstrovokEchannelAPIClient($auth_token, $private_token);
   print $api_client->get_hotels(); // returns json string
 */
