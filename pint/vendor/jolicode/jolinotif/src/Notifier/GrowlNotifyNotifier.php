<?php

/*
 * This file is part of the JoliNotif project.
 *
 * (c) Loïck Piera <pyrech@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Joli\JoliNotif\Notifier;

use Joli\JoliNotif\Driver\GrowlNotifyDriver;
use Joli\JoliNotif\Notifier;

trigger_deprecation('jolicode/jolinotif', '2.7', 'The "%s" class is deprecated and will be removed in 3.0.', GrowlNotifyNotifier::class);

/**
 * This notifier can be used on Mac OS X when growlnotify command is available.
 *
 * @deprecated since 2.7, will be removed in 3.0
 */
class GrowlNotifyNotifier extends GrowlNotifyDriver implements Notifier
{
}
