<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $table = 'photos';
    protected  $fillable = ['photo', 'user_id'];
    public $timestamps = false;
    public function user(){
        return $this->belongsTo('App\User');
    }
}
