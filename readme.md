## Millions BIP Lottery - Daily random draw

**[EN]**

This script is used for daily drawing during the Millions BIP Lottery.

The lottery is held on the Minter blockchain and includes the drawing of the main bank at the end, and the drawing of daily rewards from mining the network while the lottery is going on.

Official Telegram channel: [@millionsbiplottery](https://t.me/millionsbiplottery)

**[RU]**

Этот скрипт используется для ежедневного розыгрыша в лотерее Millions BIP Lottery.

Лотерея проводится на блокчейне Minter и включает розыгрыш основного банка в конце, а также розыгрыш ежедневных наград от майнинга сети, пока идет лотерея.

Официальный Телеграм канал: [@millionsbiplottery](https://t.me/millionsbiplottery)

### Overview

**[EN]**

The script performs the following actions:

* Get wallet' amount rewards for the previous day (UTC time) via Minter Explorer API
* Download the list of lottery ticket buyers for the previous day (UTC time)
* Calculate the number and amount of prizes using the drawing scheme, which is determined by the rules of the lottery
* Multiplies wallets in proportion to the number of tickets purchased
* Draws and randomly select winners' wallets using `playout.php`
* Publishes the list of the ticket buyers to the lottery channel
* Publishes a message with the results of the draw in the lottery channel

The mechanics of the draw is to randomly select one line at a time from the list of ticket buyers. The wallet in the drop-down line is considered to be the winner, and this line is removed from the list of ticket buyers before the next choice (or marked as deleted and excluded from subsequent selections).

For random selection, the [Mersenne Twister](http://www.math.sci.hiroshima-u.ac.jp/~m-mat/MT/emt.html) math algorithm is used.

**[RU]**

Скрипт выполняет следующие действия:

* Получает размер ревардов за предыдущий день (время UTC) через Minter Explorer API
* Скачивает список покупателей лотерейных билетов за предыдущий день (время UTC)
* Рассчитывает количество и сумму призов, используя схему розыгрыша, определенную в правилах лотереи
* Размножает кошельки пропорционально количеству купленных билетов
* Разыгрывает и выбирает случайным образом кошельки победителей, используя `playout.php`
* Публикует список покупателей билетов в канале лотереи
* Публикует сообщение с результатами розыгрыша в канале лотереи

Механика розыгрыша состоит в случайном выборе одной строки за раз из списка покупателей билетов. Кошелек в выпавшей строке считается выигравшим, а данная строка удаляется из списка покупателей билетов перед следующим выбором (или помечается как удаленная и исключается из последующих выборов).

Для случайного выбора используется математический алгоритм [Вихря Мерсена](http://www.math.sci.hiroshima-u.ac.jp/~m-mat/MT/emt.html).

### Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

### Credits

* [Minter Node API](https://docs.minter.network/#tag/Node-API)
* [Minter Explorer API](https://github.com/MinterTeam/minter-explorer-api)
* [Minter PHP SDK](https://github.com/MinterTeam/minter-php-sdk)
* [Simple PHPLogger](https://github.com/advename/Simple-PHP-Logger)
