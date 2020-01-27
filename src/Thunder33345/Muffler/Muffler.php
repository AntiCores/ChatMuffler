<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\Muffler;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Thunder33345\Muffler\Commands\MuffleChatCommand;
use Thunder33345\Muffler\Commands\MuffleCommand;
use Thunder33345\Muffler\Commands\MuffleInfoCommand;
use Thunder33345\Muffler\Commands\UnMuffleCommand;

class Muffler extends PluginBase implements Listener
{
	/**
	 * @var $muffleTracker MufflerTracker
	 */
	private $muffleTracker;

	public function onEnable()
	{
		$config = new Config($this->getDataFolder() . '/muffle.yml');
		$players = $config->get('players', []);

		$chat = $config->get('chat', 0);
		if(!is_int($chat)) $chat = 0;

		$this->muffleTracker = new MufflerTracker($players, $chat);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();

		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->register($this->getName(), new MuffleCommand($this));
		$commandMap->register($this->getName(), new UnMuffleCommand($this));
		$commandMap->register($this->getName(), new MuffleChatCommand($this));
		$commandMap->register($this->getName(), new MuffleInfoCommand($this));
	}

	public function onDisable()
	{
		$all = $this->muffleTracker->getAllMuffled();
		$chat = $this->muffleTracker->getChatMuffle();
		$config = new Config($this->getDataFolder() . '/muffle.yml');
		$config->set('players', $all);
		$config->set('chat', $chat);
		$config->save();
	}

	public function onMuffleTest(PlayerChatEvent $chatEvent):void
	{
		$player = $chatEvent->getPlayer();
		if($player->hasPermission('chatmuffler.bypass')) return;
		$muffleTracker = $this->muffleTracker;

		if($muffleTracker->isChatMuffled()){//chat muted
			if($player->hasPermission('chatmuffler.bypass.chat')) return;

			$remain = $muffleTracker->getChatMuffle(true);
			if($remain == MufflerTracker::mute_forever)
				$remain = 'Forever';
			else
				$remain = self::parseSecondToHuman($remain);

			$msg = $this->getConfig()->get('chatMuted', 'The chat have been muted for {time}!');
			$msg = str_replace('{time}', $remain, $msg);
			$player->sendMessage($msg);
			$chatEvent->setCancelled(true);
			return;
		}

		if($muffleTracker->isMuffled($player)){//player muted
			if($player->hasPermission('chatmuffler.bypass.user')) return;

			$remain = $muffleTracker->getMuffledExpiry($player, true);
			if($remain == MufflerTracker::mute_forever)
				$remain = 'Forever';
			else
				$remain = self::parseSecondToHuman($remain);

			$msg = $this->getConfig()->get('userMuted', 'You have been muted for {time}!');
			$msg = str_replace('{time}', $remain, $msg);
			$player->sendMessage($msg);
			$chatEvent->setCancelled(true);
			return;
		}
	}


	public function getMuffleTracker():MufflerTracker
	{
		return $this->muffleTracker;
	}

	/**
	 * Parses time format into duration
	 * @param string $duration
	 *
	 * @return int|null
	 * @internal Internal parsing, may change anytime
	 */
	static public function parseTimeFormat(string $duration):?int
	{
		$parts = str_split($duration);
		$time_units = ['y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second']; //Array of replacement
		$time = '';
		$i = -1;
		foreach($parts as $part){
			++$i;
			if(!isset($time_units[$part])){
				if(ctype_alpha($part)) return null; //Ensure only valid characters should pass
				continue;
			}

			$unit = $time_units[$part];
			$n = implode('', array_slice($parts, 0, $i));
			$time .= "$n $unit "; //Join number and unit
			array_splice($parts, 0, $i + 1);
			$i = -1;
		}
		$time = trim($time);
		$epoch = strtotime($time, 0);
		if($epoch === false) return null;
		return $epoch;
	}

	/**
	 * parses duration into human readable format
	 * @param $seconds
	 *
	 * @return string|null
	 * @throws \Exception
	 * @internal Internal parsing, may change anytime
	 */
	static public function parseSecondToHuman($seconds):?string
	{
		$dt1 = new \DateTime("@0");
		$dt2 = new \DateTime("@$seconds");
		$diff = $dt1->diff($dt2);
		if($diff === false) return null;
		$str = [];
		if($diff->y > 0) $str[] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
		if($diff->m > 0) $str[] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
		if($diff->d > 0) $str[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
		if($diff->h > 0) $str[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
		if($diff->i > 0) $str[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
		if($diff->s > 0) $str[] = $diff->s . ' second' . ($diff->s > 1 ? 's' : '');
		if(count($str) != 0){
			$str = implode(', ', $str);
		} else {
			$str = $diff->s . ' second';
		}
		return $str;
	}

	/**
	 * Casts command input into int
	 * @param $time
	 *
	 * @return int|null
	 * @internal Internal parsing, may change anytime
	 */
	static public function castToInt($time):?int
	{
		if(ctype_digit($time)) $time = (int)$time;
		if(is_string($time)){
			$time = strtolower($time);
			if($time == 'forever') return MufflerTracker::mute_forever;
			elseif($time == 'unmute') return MufflerTracker::unmute;
			else $time = Muffler::parseTimeFormat($time);
		}
		return $time;
	}
}