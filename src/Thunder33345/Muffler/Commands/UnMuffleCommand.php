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

class UnMuffleCommand extends PluginCommand implements CommandExecutor
{
	public function __construct(Muffler $owner)
	{
		parent::__construct('unmuffle', $owner);
		$this->setDescription('UnMuffler User Command');
		$this->setUsage('/unmuffle <username>');
		$this->setAliases(['unmute', 'unsilence']);
		$this->setPermission('chatmuffler.muffleuser');
		$this->setPermissionMessage('Insufficient permissions.');
		$this->setExecutor($this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		if(count($args) !== 1) return false;
		$playerName = array_shift($args);

		/** @var Muffler $muffler */
		$muffler = $this->getPlugin();
		$player = $muffler->getServer()->getPlayer($playerName);
		if(!$player instanceof Player){
			$sender->sendMessage("Player (" . $playerName . ") not found, Taking input literally.");
			$player = $playerName;
		} else {
			$playerName = $player->getName();
		}

		$muffler->getMuffleTracker()->muffle($player, MufflerTracker::unmute);
		self::broadcastCommandMessage($sender, "Unmuted $playerName.");
		return true;
	}
}