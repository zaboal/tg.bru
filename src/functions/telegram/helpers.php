<?php

class TinybirdClient
{
	private $token;
	private $baseUrl;

	public function __construct($token, $baseUrl = 'https://api.tinybird.co/v0')
	{
		$this->token = $token;
		$this->baseUrl = rtrim($baseUrl, '/');
	}

    /**
     * Query a Tinybird pipe with optional SQL query and parameters
     *
     * @param string $pipe The name of the Tinybird pipe to query
     * @param string|null $query Optional SQL query to execute
     * @param array $params Optional additional parameters to include in the request
     * @return object The JSON response from Tinybird as a PHP object
     */
    public function query($pipe, $query = null, $params = [])
    {
        $url = "{$this->baseUrl}/pipes/{$pipe}.json";
        $queryParams = [];
        
        if (!empty($query)) {
            $queryParams['q'] = $query;
        }
        
        if (!empty($params)) {
            $queryParams = array_merge($queryParams, $params);
        }
        
        return $this->sendRequest($url, $queryParams);
    }

	/**
	 * Send an event to a Tinybird datasource
	 *
	 * @param string $datasource The name of the datasource to send events to
	 * @param array $data The event data to send (single event or array of events)
	 * @param array $params Optional additional parameters to include in the request
	 * @return object The JSON response from Tinybird as a PHP object
	 */
	public function sendEvent($datasource, $data, $params = [])
	{
		$url = "{$this->baseUrl}/events";
		$params['name'] = $datasource;
		
		// Format URL with query parameters
		if (!empty($params)) {
			$url = $url . '?' . http_build_query($params);
		}
		
		// Use cURL directly for POST request
		$ch = curl_init();
		$jsonData = json_encode($data);
		
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => [
				'Authorization: Bearer ' . $this->token,
				'Content-Type: application/json',
				'Accept-Encoding: gzip'
			],
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT => 30
		]);
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		return json_decode(gzdecode($response));
	}

	private function sendRequest($url, $params = [])
	{
		if (!empty($params)) {
			$url = $url . '?' . http_build_query($params);
		}

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => [
				'Authorization: Bearer ' . $this->token,
				'Accept-Encoding: gzip'
			],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT => 30
		]);

		$response = curl_exec($ch);
		curl_close($ch);

		return json_decode(gzdecode($response));
	}
}