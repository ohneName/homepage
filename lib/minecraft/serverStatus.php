<?php
/**
 * @author    Janek Ostendorf (ozzy) <ozzy2345de@gmail.com>
 * @copyright Copyright (c) 2013 Janek Ostendorf
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

require_once 'init.php';

/** @var $serverStatus MinecraftServerStatus */

if($serverStatus->Get('online')) {

	echo 'Server ist <strong>online</strong>, Version '.$serverStatus->Get('version').'.';

}
else {
	echo 'Server ist <strong>offline</strong>.';
}
