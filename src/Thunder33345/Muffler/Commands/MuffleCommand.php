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

class MuffleCommand extends PluginCommand implements CommandExecutor
{
	public function __construct(Muffler $owner)
	{
		parent::__construct('muffle', $owner);
		$this->setDescription('Muffler User Command');
		$this->setUsage('/muffle <username> <seconds> or timeformat ex: 1h2i3s, 0 for unmute and -1 for forever');
		$this->setAliases(['mute', 'silence']);
		$this->setPermission('chatmuffler.muffleuser');
		$this->setPermissionMessage('Insufficient permissions.');
		$this->setExecutor($this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		if(count($args) !== 2) return false;
		$playerName = array_shift($args);
		$timeT = array_shift($args);

		$time = Muffler::castToInt($timeT);
		if($time == null){
			$sender->sendMessage('Failed to parse (' . $timeT . '), Time must be in seconds or timeformat ex 1h2i3s');
			return true;
		}

		/** @var Muffler $muffler */
		$muffler = $this->getPlugin();
		$player = $muffler->getServer()->getPlayer($playerName);
		if(!$player instanceof Player){
			$sender->sendMessage("Player (" . $playerName . ") not found, Taking input literally.");
			$player = $playerName;
		} else {
			$playerName = $player->getName();
		}

		$muffler->getMuffleTracker()->muffle($player, $time, true);

		if($time == MufflerTracker::unmute) self::broadcastCommandMessage($sender, "Unmuted $playerName");
		if($time == MufflerTracker::mute_forever) self::broadcastCommandMessage($sender, "Muted muted $playerName for forever");
		else self::broadcastCommandMessage($sender, "Muted " . $playerName . " for " . Muffler::parseSecondToHuman($time) . " seconds");
		return true;
	}
}