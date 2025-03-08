<?php

use Fuko\Masked\Redact;

class TinybirdClient
{
	private $token;
	private $baseUrl;
	private $logger;

	public function __construct(
		string $token, 
		?string $baseUrl = 'https://api.tinybird.co/v0', 
		?YandexFunctionsLogger $logger = null)
	{
		$this->token = $token;
		$this->baseUrl = rtrim($baseUrl, '/');
		$this->logger = $logger ?? new YandexFunctionsLogger(__CLASS__);

		$this->logger->debug('Initialized Tinybird client', [
			'token' => Redact::disguise($token),
			'base_url' => $baseUrl
		]);
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

		$this->logger->debug('Formed a query for the request', [
			'url' => $url,
			'query_params' => $queryParams
		]);
		
		return $this->sendRequest($url, $queryParams);
	}

	private function sendRequest($url, $params = [])
	{
		if (!empty($params)) {
			$url = $url . '?' . http_build_query($params);
		}

		$this->logger->debug('Formed the URL with query parameters', [
			'url' => $url
		]);

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

		$this->logger->debug('Initialized a cURL session', [
			'headers' => curl_getinfo($ch)
		]);

		$data = json_decode(gzdecode(curl_exec($ch)))->data;
		curl_close($ch);

		$data[0] == null ? $this->logger->warn('Received an empty response', [
			'response' => $data
		]) : $this->logger->debug('Received and decoded the response', [
			'response' => $data
		]);

		return $data;
	}
}

class YandexFunctionsLogger {
	private $stream_name;

	public function __construct(?string $stream_name)
	{
		$this->stream_name = $stream_name ?? debug_backtrace()[0]['class'];
	}

	/**
	 * Log with a given level and message.
	 * 
	 * @param string $level The log level (`DEBUG`, `INFO`, `WARN`, `ERROR`, `FATAL`)
	 * @param string $message The message to log without any formatting
	 * @param array|null $values Will be passed as `values` isnide the `context` key
	 */
	private function log(string $level, string $message, ?array $values = []): void {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2];

		print(json_encode([
			'level' => $level,
			'message' => $message,
			'stream_name' => $this->stream_name,
			'context' => [
				'trace' => $trace,
				'values' => $values]
		]) . PHP_EOL);
	}

	/**
	 * Log an debugging message.
	 * 
	 * @param string $message
	 * @param array|null $values
	 */
	public function debug(string $message, ?array $values = []): void {
		$this->log('DEBUG', $message, $values);
	}

	public function warn(string $message, ?array $values = []): void {
		$this->log('WARN', $message, $values);
	}

	/**
	 * Log a fatal error and exit the script.
	 * 
	 * @param string $message
	 * @param array|null $values
	 */
	public function fatal(string $message, ?array $values = []): void {
		$this->log('FATAL', $message, $values);
		exit();
	}
}