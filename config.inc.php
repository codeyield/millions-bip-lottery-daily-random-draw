<?php

/*********************************************************************
 * Official Minter wallet and telegram channel of the Millions BIP Lottery 
 */
const LOTTERY_WALLET  = 'Mxd1267678bc5a37e7ab5ff6375ba5ebd391de2f36';
const LOTTERY_CHANNEL = '@millionsbiplottery';
const LOTTERY_COIN    = 'BIP';


/*********************************************************************
 * Other data
 */
const PIPS = '1000000000000000000';			// PIPs in BIP = 1e+18

const GITHUBSOURCE_URL = 'https://github.com/codeyield/millions-bip-lottery-daily-random-draw';
const EXPLORER_TXURL   = 'https://minterscan.net/tx/';

const BUYERSFILE_PREFIX = 'millionsbip_buyers_';

const ERRMSG_SERIOUS = 'Serious error';
const ERRMSG_FATAL   = 'FATAL error';


/*********************************************************************
 * Prize drawing scheme
 */
const DRAWING_SCHEME = [
	['min' =>     0, 'max' =>    10, 'limit' =>   10],		//	<10 - One prize
	['min' =>    10, 'max' =>   100, 'limit' =>   10],		//	10–100 - Max. possible number of prizes, at least 10 BIP each
	['min' =>   100, 'max' =>   500, 'limit' =>   50],		//	100–500 - Max. possible number of prizes, at least 50 BIP each
	['min' =>   500, 'max' =>  1000, 'limit' =>  100],		//	500–1000 - Max. possible number of prizes, at least 100 BIP each
	['min' =>  1000, 'max' =>  2500, 'limit' =>  250],		//	1000–2500 - Max. possible number of prizes, at least 250 BIP each
	['min' =>  2500, 'max' =>  5000, 'limit' =>  500],		//	2500-5000 - Max. possible number of prizes, at least 500 BIP each
	['min' =>  5000, 'max' => 10000, 'limit' => 1000],		//	5000-10000 - Max. possible number of prizes, at least 1000 BIP each
	['min' => 10000, 'max' => 20000, 'limit' => 2000],		//	10000-20000 - Max. possible number of prizes, at least 2000 BIP each
	['min' => 20000, 'max' => 99999, 'limit' => 2500],		//	>20000 - Max. possible number of prizes, at least 2500 BIP each
];


/*********************************************************************
 * Database
 */
const DB_FILE = 'dailylottery.db';

const DB_CREATE_TABLES = <<<EOF
CREATE TABLE IF NOT EXISTS draws (
	`day`     DATE PRIMARY KEY,
	`postid1` MEDIUMINT,
	`postid2` MEDIUMINT,
	`prizes`  TINYINT,
	`total`   DECIMAL(5,12),
	`winners` VARCHAR(2000),
	`txpay`   CHAR(66)
);
EOF;


/*********************************************************************
 * Minter Explorer API
 * @url https://github.com/MinterTeam/minter-explorer-api
 */
const EXPLORER_APIURL = 'https://explorer-api.minter.network/api/v1/addresses/%%LWALLET%%/statistics/rewards?startTime=%%DATE%%&endTime=%%DATE%%';


/*********************************************************************
 * Content for the posting
 */

const TEXT_BUYERSLIST = <<<EOF
🇷🇺 Список покупателей билетов Millions BIP Lottery за %%DATE%%.

🇬🇧 List of Millions BIP Lottery ticket buyers for the date %%DATE%%.
EOF;

const TEXT_WINPOST = <<<EOF
🎰 Ежедневный розыгрыш *Millions BIP Lottery* завершён!
Daily draw of the *Millions BIP Lottery* completed!

🇷🇺 Всего было разыграно *%%PRIZES%%* призов по *%%AMOUNT%% BIP* каждый, что в сумме составляет *%%TOTAL%% BIP* наград сети Minter, поступивших на [официальный кошелёк](https://minterscan.net/address/%%LWALLET%%) Лотереи за прошлые сутки (время по UTC).

🇬🇧 In total, *%%PRIZES%%* prizes were awarded for *%%AMOUNT%% BIP* each, which in total amounts to *%%TOTAL%% BIP* Minter network rewards received in the [official lottery wallet](https://minterscan.net/address/%%LWALLET%%) for the last day (UTC time).

🤑 *Выигравшие кошельки • Won wallets:*
```
%%WINNERS%%```

💫 Задать вопросы можно в [чате](https://t.me/millionsbiplottery_chat).
You can ask questions in the [chat](https://t.me/millionsbiplottery_chat).

_Этот розыгрыш проведён при помощи автоматического скрипта с открытым кодом на_ [Github](%%GITHUB%%). 
_This draw was held using an open source automated script published on_ [Github](%%GITHUB%%).
EOF;
