<?php

use Fuko\Masked\Redact;

class TinybirdClient
{
	private $token;
	private $baseUrl;

	public function __construct($token, $baseUrl = 'https://api.tinybird.co/v0')
	{
		$this->token = $token;
		$this->baseUrl = rtrim($baseUrl, '/');

		print(json_encode([
			'level' => 'DEBUG',
			'message' => 'Initialized Tinybird client',
			'stream_name' => __CLASS__,
			'context' => [
				'frame' => [
					'function' => __FUNCTION__,
					'line' => __LINE__],
				'values' => [
					'token' => Redact::disguise($token),
					'base_url' => $baseUrl ]
		]]) . PHP_EOL);
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

		print(json_encode([
			'level' => 'DEBUG',
			'message' => 'Formed a query for the request',
			'stream_name' => __CLASS__,
			'context' => [
				'frame' => [
					'function' => __FUNCTION__,
					'line' => __LINE__],
				'values' => [
					'url' => $url,
					'query_params' => $queryParams]
		]]) . PHP_EOL);
        
        return $this->sendRequest($url, $queryParams);
    }

	private function sendRequest($url, $params = [])
	{
		if (!empty($params)) {
			$url = $url . '?' . http_build_query($params);
		}

		print(json_encode([
			'level' => 'DEBUG',
			'message' => 'Formed the URL with query parameters',
			'stream_name' => __CLASS__,
			'context' => [
				'frame' => [
					'function' => __FUNCTION__,
					'line' => __LINE__],
				'values' => [
					'url' => $url]
		]]) . PHP_EOL);

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

		print(json_encode([
			'level' => 'DEBUG',
			'message' => 'Initialized a cURL session',
			'stream_name' => __CLASS__,
			'context' => [
				'frame' => [
					'function' => __FUNCTION__,
					'line' => __LINE__],
				'values' => [
					'headers' => curl_getinfo($ch)]
		]]) . PHP_EOL);

		$response = gzdecode(curl_exec($ch));
		curl_close($ch);

		print(json_encode([
			'level' => 'DEBUG',
			'message' => 'Sent the request and received a response',
			'stream_name' => __CLASS__,
			'context' => [
				'frame' => [
					'function' => __FUNCTION__,
					'line' => __LINE__ ],
				'values' => [
					'response' => $response]
		]]) . PHP_EOL);

		return json_decode($response);
	}
}