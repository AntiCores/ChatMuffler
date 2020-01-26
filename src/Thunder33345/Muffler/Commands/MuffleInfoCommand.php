<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\Muffler\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Thunder33345\Muffler\Muffler;
use Thunder33345\Muffler\MufflerTracker;

class MuffleInfoCommand extends PluginCommand implements CommandExecutor
{
	public function __construct(Muffler $owner)
	{
		parent::__construct('muffleinfo', $owner);
		$this->setDescription('Muffler Info Command');
		$this->setUsage('/muffleinfo [username]');
		$this->setAliases(['muteinfo']);
		$this->setPermission('chatmuffler.muffleinfo');
		$this->setPermissionMessage('Insufficient permissions.');
		$this->setExecutor($this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		/** @var Muffler $muffler */
		$muffler = $this->getPlugin();
		$muffleTracker = $muffler->getMuffleTracker();
		if(count($args) == 0){
			if(!$sender instanceof Player)
				return false;//console cant look them self up

			if(!$sender->hasPermission('chatmuffler.muffleinfo.self')){
				$sender->sendMessage("Insufficient permissions.");
				return true;
			}

			if($muffleTracker->isChatMuffled()){
				$chat = $muffleTracker->getChatMuffle(true);
				if($chat == MufflerTracker::mute_forever)
					$chat = 'forever';
				else
					$chat = "for " . Muffler::parseSecondToHuman($chat);
				$sender->sendMessage("[ChatMuffler] Chat have been muted " . $chat . ".");
			}
			if($muffleTracker->isMuffled($sender)){
				$self = $muffleTracker->getMuffledExpiry($sender, true);
				if($self == MufflerTracker::mute_forever)
					$self = 'forever';
				else
					$self = "for " . Muffler::parseSecondToHuman($self);
				$sender->sendMessage("[ChatMuffler] You have been muted " . $self . ".");
			} else
				$sender->sendMessage("[ChatMuffler] You are not muted.");
			return true;
		}

		if(count($args) !== 1) return false;

		if(!$sender->hasPermission('chatmuffler.muffleinfo.other')){
			$sender->sendMessage("Insufficient permissions.");
			return true;
		}

		$playerName = array_shift($args);

		$player = $muffler->getServer()->getPlayer($playerName);
		if(!$player instanceof Player){
			$sender->sendMessage("Player (" . $playerName . ") not found, Taking input literally.");
		} else {
			$playerName = $player->getName();
		}

		if($muffleTracker->isMuffled($playerName)){
			$time = $muffleTracker->getMuffledExpiry($playerName, true);
			if($time == MufflerTracker::mute_forever)
				$time = 'forever';
			else
				$time = "for " . Muffler::parseSecondToHuman($time);
			$sender->sendMessage("[ChatMuffler] $playerName have been muted " . $time . ".");
		} else
			$sender->sendMessage("[ChatMuffler] $playerName is not muted.");

		return true;
	}
}