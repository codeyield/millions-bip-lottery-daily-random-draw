<?php
/**
 * This script is used for daily drawing during the Millions BIP Lottery.
 *
 * The lottery is held on the Minter blockchain and includes the drawing of the main bank at the end, 
 * and the drawing of daily rewards from mining the network while the lottery is on.
 * 
 * Official Telegram channel: @millionsbiplottery
 */

define('INDEXDIR',  dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('VENDORDIR', realpath(INDEXDIR . '../vendor')  . DIRECTORY_SEPARATOR);
define('SECRETDIR', realpath(INDEXDIR . '../secrets') . DIRECTORY_SEPARATOR);
define('TEMPDIR',   realpath(INDEXDIR . 'temp') . DIRECTORY_SEPARATOR);
define('DBDIR',     realpath(INDEXDIR . 'db')   . DIRECTORY_SEPARATOR);

require_once(VENDORDIR . 'autoload.php');
require_once(SECRETDIR . 'secrets.inc.php');
require_once(INDEXDIR  . 'config.inc.php');
require_once(INDEXDIR  . 'playout.php');
require_once(INDEXDIR  . 'logger.php');


	set_time_limit(180);
	date_default_timezone_set('UTC');
	

	define('YESTERDAY', date('Y-m-d', strtotime('-1 day')));
	define('SCRIPTNAME', strtoupper(basename(__FILE__, '.php')) . ': ');
	define('WHERECOND', " WHERE day = '" .YESTERDAY. "'");

	// Notice messages array
	$msginfo = [];


	/*********************************************************************
	 * Open the database and check for the draw today 
	 * (if any operations did not complete and this script was run again)
	 */
    try {
        $db = new SQLite3(DBDIR . DB_FILE);
        $db->query(DB_CREATE_TABLES);
		
		$result = $db->query("SELECT * FROM draws" . WHERECOND);
		$todayDraw = $result->fetchArray(SQLITE3_ASSOC);	// Return false if no data
		
		if(empty($todayDraw)) {
			$db->query("INSERT INTO draws (day) VALUES ('".YESTERDAY."')");
		}
	}
	catch(Exception $e) {
		Logger::error($m = 'Can`t open or fulfill the request to the SQLite3 database', [$e->getMessage()]);
		mail(EMAIL_ALERT, SCRIPTNAME . ERRMSG_SERIOUS, $m .PHP_EOL. $e->getMessage());
		exit(1);
	}

	
	/*********************************************************************
	 * Initialize the Telegram Bot API
	 */
	try {
		$bot = new \TelegramBot\Api\BotApi(TELEGRAM_BOT_TOKEN);
/*
		if(isset($tgProxy['type'], $tgProxy['proxy'], $tgProxy['usrpwd'])) {
			$bot->setCurlOption(CURLOPT_HTTPPROXYTUNNEL, true);
			$bot->setCurlOption(CURLOPT_PROXYTYPE, $tgProxy['type']);
			$bot->setCurlOption(CURLOPT_PROXY, $tgProxy['proxy']);
			$bot->setCurlOption(CURLOPT_PROXYUSERPWD, $tgProxy['usrpwd']);
		}
*/
	} catch (\TelegramBot\Api\Exception $e) {
		Logger::error($m = 'Can`t initialize the Telegram Bot API', [$e->getMessage()]);
		mail(EMAIL_ALERT, SCRIPTNAME . ERRMSG_SERIOUS, $m .PHP_EOL. $e->getMessage());
		exit(1);
	}


	/*********************************************************************
	 * Get wallet' amount rewards for the previous day (UTC time) via Minter Explorer API
	 */
	$apiUrl = strtr(EXPLORER_APIURL, ['%%LWALLET%%' => LOTTERY_WALLET, '%%DATE%%' => YESTERDAY]);
	$data = json_decode(file_get_contents($apiUrl), true);

	if(isset($data['data'][0]['time'], $data['data'][0]['amount']) and (substr($data['data'][0]['time'], 0, 10) == YESTERDAY)) {
		
		$totalRewards = $data['data'][0]['amount'];
		Logger::info($msginfo[] = 'Total rewards yesterday: ' . $totalRewards);
	}
	else {
		Logger::fatal($m = 'Can`t get amount rewards from the Minter Explorer API');
		mail(EMAIL_ALERT, SCRIPTNAME . ERRMSG_FATAL, $m);
		exit(1);
	}


	/*********************************************************************
	 * Download the list of lottery ticket buyers for the previous day (UTC time)
	 */
	$buyersData = json_decode(file_get_contents(GET_BUYERS_DATA_URL), true);
	
	if(!empty($buyersData) and is_array($buyersData)) {
		Logger::info($msginfo[] = 'List of lottery ticket buyers downloaded: ' . count($buyersData) . ' records');
	}
	else {
		Logger::fatal($m = 'Can`t download the list of lottery ticket buyers or it empty');
		mail(EMAIL_ALERT, SCRIPTNAME . ERRMSG_FATAL, $m);
		exit(1);
	}
	

	/*********************************************************************
	 * Calculate the number and amount of every prizes
	 */
	$prizes = 0;
	$prizeAmount = 0;
	
	foreach(DRAWING_SCHEME as $item) {
		if(($totalRewards >= $item['min']) and ($totalRewards < $item['max'])) {
			
			$tmp = (int) ($totalRewards / $item['limit']);
			$prizes = ($tmp > 0 ? $tmp : 1);
			$prizeAmount = $totalRewards / $prizes;
			break;
		}
	}
	
	// Failed to calculate the amount and number of prizes
	if(($prizes == 0) or ($prizeAmount == 0)) {
		Logger::fatal($m = 'Failed to calculate the amount and number of prizes');
		mail(EMAIL_ALERT, SCRIPTNAME . ERRMSG_FATAL, $m);
		exit(1);
	}

	
	/*********************************************************************
	 * Check if there was a draw today and the winners’ wallets
	 */
	$winners = !empty($todayDraw['winners']) ? explode(',', $todayDraw['winners']) : false;

	if(empty($winners)) {
		
		// Multiply wallets in proportion to the number of tickets purchased
		$wallets2Draw = [];
		foreach($buyersData as $item) {
			$wallets2Draw = array_merge($wallets2Draw, array_fill(0, $item['bets'], $item['wallet']));
		}

		// Play out and randomly select winners' wallets
		$draw = new Playout();
		$winners = $draw->random($wallets2Draw, $prizes);

		// Saving the winners’ wallets to the database
		if(!empty($winners) and is_array($winners)) {
			Logger::info($msginfo[] = 'Winners have been choose: ' . implode(', ', $winners));
			$db->query("UPDATE draws SET prizes = '{$prizes}', total = '{$totalRewards}', winners = '" .implode(',', $winners). "'" . WHERECOND);
		}
		else {
			Logger::error($m = 'Can`t play out and not choose the winners');
			mail(EMAIL_ALERT, SCRIPTNAME . ERRMSG_SERIOUS, $m);
			exit(1);
		}
	}

	
	/*********************************************************************
	 * Prepare the data and publish the list of the ticket buyers to the channel
	 */
	$csvFile = TEMPDIR . BUYERSFILE_PREFIX . YESTERDAY . '.txt';
	$csvData = array_map(function($item) { return implode("\t", $item);	}, $buyersData);
	file_put_contents($csvFile, implode("\n", $csvData));

	if(empty($todayDraw['postid1'])) {
		
		try {
			$document = new \CURLFile(realpath($csvFile));
			$result = $bot->sendDocument(LOTTERY_CHANNEL, $document, str_replace('%%DATE%%', YESTERDAY, TEXT_BUYERSLIST));

			if(($postid1 = $result->getMessageId()) > 0) {
				Logger::info($msginfo[] = 'Post #1 was published in the channel with id: ' . $postid1);
				$db->query("UPDATE draws SET postid1 = '{$postid1}'" . WHERECOND);
			}
			
		} catch (\TelegramBot\Api\Exception $e) {
			Logger::error($m = 'Can`t publish post #1 to the channel', [$e->getMessage()]);
			mail(EMAIL_ALERT, SCRIPTNAME . ERRMSG_SERIOUS, $m .PHP_EOL. $e->getMessage());
			exit(1);
		}
	}


	/*********************************************************************
	 * Create and publish the post of the draw to the channel
	 */
	$winText = strtr(TEXT_WINPOST, [
		'%%LWALLET%%' => LOTTERY_WALLET,
		'%%LCOIN%%'   => LOTTERY_COIN,
		'%%GITHUB%%'  => GITHUBSOURCE_URL,
		'%%TOTAL%%'   => sprintf('%01.6f', $totalRewards), 
		'%%AMOUNT%%'  => sprintf('%01.6f', $prizeAmount), 
		'%%PRIZES%%'  => $prizes, 
		'%%WINNERS%%' => implode("\n", $winners),
	]);
	
	try {
		// Publishing new post
		if(empty($todayDraw['postid2'])) {
			
			$result = $bot->sendMessage(LOTTERY_CHANNEL, $winText, 'Markdown', true, null, null);
			
			if(($postid2 = $result->getMessageId()) > 0) {
				Logger::info($msginfo[] = 'Post #2 was published in the channel with id: ' . $postid2);
				$db->query("UPDATE draws SET postid2 = '{$postid2}'" . WHERECOND);
			}
		}
		
	} catch (\TelegramBot\Api\Exception $e) {
		Logger::error($m = 'Can`t publish post #2 to the channel', [$e->getMessage()]);
		mail(EMAIL_ALERT, SCRIPTNAME . ERRMSG_SERIOUS, $m .PHP_EOL. $e->getMessage());
		exit(1);
	}


	/*********************************************************************
	 * Daily draw completed
	 */
	Logger::info($msginfo[] = 'Daily draw completed!');
	echo implode("\n", $msginfo) . "\n";
	exit(0);
