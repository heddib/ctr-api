<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'type', 'src',
    ];

    public function bannedDrafts() {
        return $this->belongsToMany('App\Draft', 'ban_draft_map');
    }

    public function pickedDrafts() {
        return $this->belongsToMany('App\Draft', 'pick_draft_map');
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
