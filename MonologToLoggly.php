<?php

/**
 * Monolog to Loggly
 * by Michael Milawski - 2018-10-07
 * This script will upload logs that were created by Monolog to Loggly.
 * This script can be used to bulk upload logs to loggly using a cronjob.
 */


use Monolog\Logger;

class MonologToLoggly{

	//Config from config.json will be stored here:
	public $config = null;

	public function __construct(){
		//Load the config:
		$config_file = __DIR__ . "/config.json";
		if(!file_exists($config_file)){
			throw new Exception("config.json file not found.");
		}

		$conf = file_get_contents($config_file);
		$this->config = json_decode($conf);

	}

	/**
	 * Upload data to your loggly account
	 */
	public function upload($data){

		$client = new GuzzleHttp\Client([
			'base_uri' => 'https://logs-01.loggly.com'
		]);

		$uri = sprintf("/bulk/%s/tag/bulk", $this->config->loggly_customer_token);
		$response = $client->request('POST', $uri,[
			"body" => $data
		]);

		
		$code = $response->getStatusCode();
		$body = $response->getBody();

		echo $body;
	}


	/**
	 * Parse a single monolog log file line
	 * @param  string $line
	 * @return array
	 */
	public function parseLine($line){

		//At first get the general data:
		$re = '/\[(?<logtime>.{19})\].(?<app>.+?)\.(?<loglevel>.+?)\:.(\((?<user>.+)\))?(?<message>.+?)\s[\{\[]/m';

		preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
		if(empty($matches)){
			return null;
		}

		$mdata = $matches[0];


		$user = isset($mdata['user']) ? $mdata['user'] : 'NOUSER';

		//Get the extra data:
		$re = '/(?<data>\{.+?\}).\[\]$/m';
		preg_match_all($re, $line, $matches_extra, PREG_SET_ORDER, 0);

		$timestamp = new \DateTime($mdata['logtime']);
		$timestamp = $timestamp->format("Y-m-d\TH:i:s.uO");

		$extra = [];
		if(!empty($matches_extra)){
			$extra = json_decode($matches_extra[0]['data'], true);
		}

		$loglevel_number = constant('Monolog\Logger::' . $mdata['loglevel']);


		$return = [
			"message" => trim($mdata['message']),
			"context" => [],
			"user" => $user,
			"level" => $loglevel_number,
			"level_name" => $mdata['loglevel'],
			"channel" => $mdata['app'],
			"timestamp" => $timestamp,
			"datetime" => [
				"date" => $mdata['logtime'],
				"timezone_type" => 3,
				"timezone" => "Europe/Berlin"
			],
			"extra" => $extra
		];


		return $return;
	}


	/**
	 * Parse a log monolog file
	 * @param  string $file full path to the log file
	 * @return array
	 */
	public function parseLogFile($file){
		if(!file_exists($file)){
			throw new Exception("Logfile not found");
		}

		$fc = file_get_contents($file);
		$lines = explode("\n", $fc);

		$logs = [];
		$nr = 0;
		foreach($lines as $line){
			$spos = stripos($line, 'Transfering data');
			if($spos !== false){
				continue;
			}

			$parsedLine = $this->parseLine($line);
			if($parsedLine !== null){
				$logs[] = json_encode($parsedLine);
			}
		}

		$logs_str = implode("\n", $logs);

		return $logs_str;

	}

	/**
	 * Upload a log file to loggly
	 */
	public function uploadLogFile($logfile){
		$logs = $this->parseLogFile($logfile);
		$this->upload($logs);
	}


}