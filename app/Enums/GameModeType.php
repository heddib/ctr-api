<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Classic()
 * @method static static League()
 * @method static static Light()
 * @method static static NoBans()
 */
final class GameModeType extends Enum
{
    const Classic   =   "CLASSIC_6_BANS_10_PICKS";
    const League    =   "LEAGUE_6_BANS_8_PICKS";
    const Light     =   "LIGHT_4_BANS_6_PICKS";
    const NoBans    =   "NO_BANS_0_BANS_10_PICKS";
}
