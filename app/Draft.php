<?php

namespace App;

use App\Enums\GameModeType;
use BenSampo\Enum\Traits\CastsEnums;
use Emadadly\LaravelUuid\Uuids;
use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{

    /*use CastsEnums {
        CastsEnums::getAttributeValue as enumGetAttributeValue;
        CastsEnums::setAttribute as enumSetAttribute;
    }

    public function getAttributeValue($value) {
        $this->enumGetAttributeValue($value);
    }

    public function setAttribute($key, $value) {
        $this->enumSetAttribute($key, $value);
    }*/

    use CastsEnums;
    use Uuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'teama', 'teamb', 'gamemode_type',
    ];

    protected $enumCasts = [
        'gamemode_type' => GameModeType::class,
    ];

    /**
     * Existing casts are processed before $enumCasts which can be useful if you're
     * taking input from forms and your enum values are integers.
     */
    protected $casts = [
        'gamemode_type' => 'string',
    ];

    public function mapsBanned()
    {
        return $this->belongsToMany('App\Map', 'ban_draft_map');
    }

    public function mapsPicked()
    {
        return $this->belongsToMany('App\Map', 'pick_draft_map');
    }

    /**
     * Get the user that created the draft.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    // this is a recommended way to declare event handlers
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($draft) { // before delete() method call this
            $draft->mapsBanned()->sync([]);
            $draft->mapsPicked()->sync([]);
            // do the rest of the cleanup...
        });
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
}
