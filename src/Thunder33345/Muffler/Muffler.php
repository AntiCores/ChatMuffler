<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\Muffler;

use DateTime;
use Exception;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Thunder33345\Muffler\Commands\MuffleChatCommand;
use Thunder33345\Muffler\Commands\MuffleCommand;
use Thunder33345\Muffler\Commands\MuffleInfoCommand;
use Thunder33345\Muffler\Commands\MuffleListCommand;
use Thunder33345\Muffler\Commands\UnMuffleCommand;

class Muffler extends PluginBase implements Listener
{
	/**
	 * @var MufflerTracker $muffleTracker
	 */
	private $muffleTracker;
	/** @var CommandTracker */
	private $commandTracker;
	/** @var Config $lang */
	private $lang;

	public function onEnable()
	{
		$this->saveDefaultConfig();
		$config = new Config($this->getDataFolder() . '/muffle.yml');
		$players = $config->get('players', []);

		$chat = $config->get('chat', 0);
		if(!is_int($chat)) $chat = 0;

		$this->muffleTracker = new MufflerTracker($players, $chat);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->saveResource("lang.yml", false);
		$this->lang = new Config($this->getDataFolder() . '/lang.yml');

		$this->initTracker();

		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->register($this->getName(), new MuffleCommand($this));
		$commandMap->register($this->getName(), new UnMuffleCommand($this));
		$commandMap->register($this->getName(), new MuffleChatCommand($this));
		$commandMap->register($this->getName(), new MuffleInfoCommand($this));
		$commandMap->register($this->getName(), new MuffleListCommand($this));
	}

	private function initTracker()
	{
		$commandTracker = $this->commandTracker = new CommandTracker();
		$all = $this->getConfig()->getNested('blocked-commands.all', []);
		$user = $this->getConfig()->getNested('blocked-commands.user', []);
		$chat = $this->getConfig()->getNested('blocked-commands.chat', []);

		$allBlocked = ['all' => $all, 'user' => $user, 'chat' => $chat];
		$allProcessed = ['all' => [], 'user' => [], 'chat' => []];
		$commandMap = $this->getServer()->getCommandMap();
		foreach($allBlocked as $type => $data){
			foreach($data as $commandName){
				$commandInstance = $commandMap->getCommand($commandName);
				if(!$commandInstance instanceof Command){
					$allProcessed[$type][] = $commandName;//ignore not found
					continue;
				}
				$allProcessed[$type][] = $commandInstance->getName();
			}
		}
		$this->getConfig()->setNested('blocked-commands.all', $allProcessed['all']);
		$this->getConfig()->setNested('blocked-commands.user', $allProcessed['user']);
		$this->getConfig()->setNested('blocked-commands.chat', $allProcessed['chat']);
		$this->getConfig()->save();

		$commandTracker->addAll(...$allProcessed['all']);
		$commandTracker->addChat(...$allProcessed['chat']);
		$commandTracker->addPlayer(...$allProcessed['user']);
	}

	public function onDisable()
	{
		$this->onSave();
	}

	public function onSave()
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

		if($this->isChatMuffled($player)){//chat muted
			$remain = $muffleTracker->getChatMuffle(true);
			if($remain == MufflerTracker::mute_forever)
				$remain = 'Forever';
			else
				$remain = self::parseSecondToHuman($remain);

			$msg = $this->getLang()->get('chat.muted.msg', 'The chat have been muted for {time}!');
			if($msg != false){
				$msg = str_replace('{time}', $remain, $msg);
				$player->sendMessage($msg);
				$chatEvent->setCancelled(true);
			}
			return;
		}

		if($this->isPlayerMuffled($player)){//player muted
			$remain = $muffleTracker->getMuffledExpiry($player, true);
			if($remain == MufflerTracker::mute_forever)
				$remain = 'Forever';
			else
				$remain = self::parseSecondToHuman($remain);

			$msg = $this->getLang()->get('user.muted.msg', 'You have been muted for {time}!');
			if($msg !== false){
				$msg = str_replace('{time}', $remain, $msg);
				$player->sendMessage($msg);
				$chatEvent->setCancelled(true);
			}
			return;
		}
	}

	public function onCommandProcess(CommandEvent $event)
	{
		$player = $event->getSender();
		if(!$player instanceof Player) return;

		$commandLine = $event->getCommand();
		$commandName = explode(' ', $commandLine, 2)[0];
		$selectedCommand = $this->getServer()->getCommandMap()->getCommand($commandName);
		if(!$selectedCommand instanceof Command) return;
		$commandName = $selectedCommand->getName();

		if($this->isChatMuffled($player)){
			if($this->commandTracker->chatBlocked($commandName)){
				$event->setCancelled();
				$lang = $this->lang->get('chat.muted.cmd', 'You cant use this command while the chat is muted');
				if($lang !== false) $player->sendMessage($lang);
			}
		}
		if($this->isPlayerMuffled($player)){
			if($this->commandTracker->playerBlocked($commandName)){
				$event->setCancelled();
				$lang = $this->lang->get('user.muted.cmd', 'You cant use this command while the chat is muted');
				if($lang !== false) $player->sendMessage($lang);
			}
		}
	}

	public function isMuffled(Player $player):bool
	{
		if($player->hasPermission('chatmuffler.bypass')) return false;
		return $this->isChatMuffled($player) === false AND $this->isPlayerMuffled($player) === false;
	}

	public function isChatMuffled(Player $player):bool
	{
		if($player->hasPermission('chatmuffler.bypass')) return false;
		if($player->hasPermission('chatmuffler.bypass.chat')) return false;
		return $this->muffleTracker->isChatMuffled();
	}

	public function isPlayerMuffled(Player $player):bool
	{
		if($player->hasPermission('chatmuffler.bypass')) return false;
		if($player->hasPermission('chatmuffler.bypass.user')) return false;
		return $this->muffleTracker->isMuffled($player);
	}

	public function getMuffleTracker():MufflerTracker
	{
		return $this->muffleTracker;
	}

	public function getLang(){ return $this->lang; }

	/**
	 * Parses time format into duration
	 *
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
	 *
	 * @param mixed $seconds
	 *
	 * @return string|null
	 * @throws Exception
	 * @internal Internal parsing, may change anytime
	 */
	static public function parseSecondToHuman($seconds):?string
	{
		if($seconds === MufflerTracker::mute_forever){
			return 'forever';
		}
		$dt1 = new DateTime("@0");
		$dt2 = new DateTime("@$seconds");
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
	 *
	 * @param mixed $time
	 *
	 * @return int|null
	 * @internal Internal parsing, may change anytime
	 */
	static public function castToInt($time):?int
	{
		if(is_numeric($time)) $time = (int)$time;
		if(is_string($time)){
			$time = strtolower($time);
			if($time == 'forever') return MufflerTracker::mute_forever;
			elseif($time == 'unmute') return MufflerTracker::unmute;
			else $time = Muffler::parseTimeFormat($time);
		}
		return $time;
	}
}