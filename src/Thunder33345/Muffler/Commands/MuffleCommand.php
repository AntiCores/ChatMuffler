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

class MuffleCommand extends PluginCommand implements CommandExecutor
{
	public function __construct(Muffler $owner)
	{
		parent::__construct('muffle', $owner);
		$this->setDescription('Muffler Command');
		$this->setUsage('/muffle <username> <seconds> or 0 for unmute, -1 for forever');
		$this->setAliases(['mute', 'silence']);
		$this->setPermission('chatmuffler.muffleuser');
		$this->setPermissionMessage('Insufficient permissions.');
		$this->setExecutor($this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		if(count($args) !== 2) return false;
		$playerName = array_shift($args);
		$time = array_shift($args);
		if(!is_numeric($time)){
			$sender->sendMessage('time must be a number');
			return true;
		}

		/** @var Muffler $muffler */
		$muffler = $this->getPlugin();
		$player = $muffler->getServer()->getPlayer($playerName);
		if(!$player instanceof Player){
			$sender->sendMessage("Player (" . $playerName . ") not found, Taking input literally.");
			$player = $playerName;
		}
		$time = (int)$time;
		if($time == 0 OR $time == -1) $muffler->getMuffleTracker()->muffle($player, (int)$time);
		else $muffler->getMuffleTracker()->muffle($player, (int)$time, true);
		self::broadcastCommandMessage($sender, "Muted " . $player . " for " . $time . " seconds");
		return true;
	}
}