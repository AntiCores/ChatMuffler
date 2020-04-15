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
		if(count($args) !== 1) return false;
		$playerName = strtolower(array_shift($args));

		$player = $muffler->getServer()->getPlayer($playerName);
		if(!$player instanceof Player){
			$sender->sendMessage("Player (" . $playerName . ") not found, Taking input literally.");
		} else {
			$playerName = $player->getName();
		}

		if($muffleTracker->isMuffled($playerName)){
			$till = $muffleTracker->getMuffledExpiry($playerName, true);
			if($till == MufflerTracker::mute_forever)
				$till = 'forever';
			else
				$till = "for " . Muffler::parseSecondToHuman($till);
			$sender->sendMessage("[ChatMuffler] $playerName have been muted " . $till . ".");
		} else
			$sender->sendMessage("[ChatMuffler] $playerName is not muted.");

		return true;
	}
}