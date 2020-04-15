<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\Muffler;

class CommandTracker
{
	public const ALL = 0x0;
	public const PLAYER = 0x1;
	public const CHAT = 0x2;
	private $tracked = [];

	public function addAll(string ...$commandNames):void
	{
		$this->addCommand(self::ALL, ...$commandNames);
	}

	public function addPlayer(string ...$commandNames):void
	{
		$this->addCommand(self::PLAYER, ...$commandNames);
	}

	public function addChat(string ...$commandNames):void
	{
		$this->addCommand(self::CHAT, ...$commandNames);
	}

	public function allBlocked(string $commandName):bool
	{
		return $this->isCommandIn(self::ALL, $commandName);
	}

	public function playerBlocked(string $commandName):bool
	{
		$all = $this->isCommandIn(self::ALL, $commandName);
		if($all) return true;
		return $this->isCommandIn(self::PLAYER, $commandName);
	}

	public function chatBlocked(string $commandName):bool
	{
		$all = $this->isCommandIn(self::ALL, $commandName);
		if($all) return true;
		return $this->isCommandIn(self::CHAT, $commandName);
	}

	protected function addCommand(int $where, string ...$commandNames):void
	{
		foreach($commandNames as $commandName){
			$this->tracked[$where][] = strtolower($commandName);
		}
	}

	protected function isCommandIn(int $where, string $commandName):bool
	{
		if(!isset($this->tracked[$where])) return false;
		$commandName = strtolower($commandName);
		foreach($this->tracked[$where] as $trackedName){
			if($commandName === $trackedName) return true;
		}
		return false;
	}
}