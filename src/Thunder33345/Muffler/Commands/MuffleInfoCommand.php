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
			$muffleTracker->getChatMuffle(true);
			$muffleTracker->getMuffledExpiry($sender);
		}
		if(count($args) !== 1) return false;
		$playerName = array_shift($args);

		return true;
	}
}