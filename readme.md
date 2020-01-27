# Chat Muffler
[<img src="https://img.shields.io/badge/Poggit-view-brightgreen.svg" width="110" height="30"/>](https://poggit.pmmp.io/ci/AntiCores/ChatMuffler)
[<img src="https://img.shields.io/badge/Discord-join-697EC4.svg" width="110" height="30"/>](https://discord.gg/uBghvNp)

A Simple Plugin For Managing Mutes

## Features
- Bloat free
- Mutes players
- Mutes chat
- Data saves past reboots
- Advanced mute time parser

## Commands
| Command    	| Description     	| Usage                     	| Aliases               	|
|------------	|-----------------	|---------------------------	|-----------------------	|
| muffle     	| muffle someone  	| /muffle <username> <time> 	| mute, silence         	|
| mufflechat 	| muffle the chat 	| /mufflechat <time>        	| mutechat, silencechat 	|
| muffleinfo 	| muffle info     	| /muffleinfo [username]    	| muteinfo              	|

## Permission

| Permission Node              	| Description                       	| Default 	|
|------------------------------	|-----------------------------------	|---------	|
| chatmuffler.bypass           	| Bypass All Muffled Restriction    	| OP      	|
| chatmuffler.bypass.chat      	| Bypass Muffled Chat Restriction   	| OP      	|
| chatmuffler.bypass.user      	| Bypass User Muffled Restriction   	| OP      	|
| chatmuffler.muffleuser       	| Allow Muting Other Users          	| OP      	|
| chatmuffler.mufflechat       	| Allow Muting Chat                 	| OP      	|
| chatmuffler.muffleinfo       	| Allow Using Muffle Info           	| Anyone  	|
| chatmuffler.muffleinfo.self  	| Allow Using Muffle Info On Self   	| Anyone  	|
| chatmuffler.muffleinfo.other 	| Allow Using Muffle Info On Others 	| OP      	|

## Time parser supports
\<time\> can be second which is assumed by default, or a time formatted input

Legend: (y)ear, (m)onth, (w)eek, (d)ay, (h)our, m(i)nute, (s)econd

/mute bob `12h30i50s` will mute bob for `12 hours, 30 minutes, 50 seconds`

muting using `0` or `unmute` will unmute, and `-1` or `forever` will mute forever 

## API
API functions are located in `MufflerTracker` which can be accessed via `Muffler::getMuffleTracker` when the plugin is enabled

The details of the functions can be accessed in `MufflerTracker` which is documented with PHP doc and all you need


## Maybe?
- mute ui for more advanced/easy operations
