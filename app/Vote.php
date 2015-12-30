<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $table = 'votes';
    protected $fillable =['subject_id', 'object_id', 'rate'];

    public function subject(){
        return $this->belongsTo('App\User', 'subject_id');
    }

    public function object(){
        return $this->belongsTo('App\User', 'object_id');
    }



}
