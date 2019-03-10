<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\Muffler;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

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
	}

	public function onDisable()
	{
		$all = $this->muffleTracker->getAllMuffled();
		$chat = $this->muffleTracker->getChatMuffle();
		$config = new Config($this->getDataFolder() . '/muffle.yml');
		$config->set('chat', $chat);
		$config->set('players', $all);
	}

	public function onMuffleTest(PlayerChatEvent $chatEvent):void
	{
		$player = $chatEvent->getPlayer();
		$muffleTracker = $this->muffleTracker;
		if($muffleTracker->isChatMuffled()){
			if(!$player->hasPermission('chatmuffler.bypass')) return;
			$msg = $this->getConfig()->get('chatMuted', 'Chat have been muted!');
			$player->sendMessage($msg);
			$chatEvent->setCancelled(true);
			return;
		}

		if($muffleTracker->isMuffled($player)){
			if(!$player->hasPermission('chatmuffler.bypass')) return;
			$msg = $this->getConfig()->get('userMuted', 'You have been muted!');
			$player->sendMessage("You have been muted from chat!");
			$chatEvent->setCancelled(true);
			return;
		}
	}


	public function getMuffleTracker():MufflerTracker
	{
		return $this->muffleTracker;
	}
}