<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\Muffler\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use Thunder33345\Muffler\Muffler;
use Thunder33345\Muffler\MufflerTracker;

class MuffleListCommand extends PluginCommand implements CommandExecutor
{
	public function __construct(Muffler $owner)
	{
		parent::__construct('mufflelist', $owner);
		$this->setDescription('Muffler List Command');
		$this->setUsage('/mufflelist');
		$this->setAliases(['mutelist']);
		$this->setPermission('chatmuffler.mufflelist');
		$this->setPermissionMessage('Insufficient permissions.');
		$this->setExecutor($this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		/** @var Muffler $muffler */
		$muffler = $this->getPlugin();
		$muffleTracker = $muffler->getMuffleTracker();
		if(!$sender->hasPermission('chatmuffler.muffleinfo.all')){
			$sender->sendMessage("Insufficient permissions.");
			return true;
		}
		$players = $muffleTracker->getAllMuffled();
		$sender->sendMessage("[ChatMuffler] Listing All Mutes (" . count($players) . ")");

		foreach($players as $player => $till){
			if($till == MufflerTracker::mute_forever){
				$sender->sendMessage("$player => forever");
				continue;
			}
			$remaining = $till - time();
			if($till == MufflerTracker::unmute OR $remaining <= 0) continue;

			$sender->sendMessage("$player => " . Muffler::parseSecondToHuman($remaining));
		}
		return true;
	}
}