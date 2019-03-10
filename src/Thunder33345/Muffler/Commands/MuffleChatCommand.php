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
	public function __construct(string $name, Muffler $owner)
	{
		parent::__construct($name, $owner);
		$this->setDescription('Chat Muffler Command');
		$this->setUsage('/mufflechat <seconds> or 0 for unmute, -1 for forever');
		$this->setAliases(['mutechat', 'silencechat']);
		$this->setPermission('chatmuffler.mufflechat');
		$this->setPermissionMessage('Insufficient permissions.');
		$this->register($owner->getServer()->getCommandMap());
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		if(count($args) !== 1) return false;
		$time = array_shift($args);
		if(is_int($time)){
			$sender->sendMessage('time must be a number');
		}
		/** @var Muffler $muffler */
		$muffler = $this->getPlugin();
		$muffler->getMuffleTracker()->muffleChat($time);
		self::broadcastCommandMessage($sender, "[" . $sender->getName() . " Muted the chat for " . $time . "seconds]");
		return true;
	}
}