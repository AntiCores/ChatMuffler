<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\Muffler\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use Thunder33345\Muffler\Muffler;

class MuffleChatCommand extends PluginCommand implements CommandExecutor
{
	public function __construct(Muffler $owner)
	{
		parent::__construct('mufflechat', $owner);
		$this->setDescription('Chat Muffler Command');
		$this->setUsage('/mufflechat <seconds> or 0 for unmute, -1 for forever');
		$this->setAliases(['mutechat', 'silencechat']);
		$this->setPermission('chatmuffler.mufflechat');
		$this->setPermissionMessage('Insufficient permissions.');
		$this->setExecutor($this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		var_dump($args);
		if(count($args) !== 1) return false;
		$time = array_shift($args);
		var_dump($time);
		if(!is_numeric($time)){
			$sender->sendMessage('time must be a number');
			return true;
		}
		/** @var Muffler $muffler */
		$muffler = $this->getPlugin();
		$time = (int)$time;
		if($time == 0 OR $time == -1) $muffler->getMuffleTracker()->muffleChat($time);
		else $muffler->getMuffleTracker()->muffleChat($time, true);
		self::broadcastCommandMessage($sender, "Muted the chat for " . $time . "seconds");
		return true;
	}
}