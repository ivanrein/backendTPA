<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class School extends Model
{
    protected $table = 'schools';

    protected $fillable = ['name', 'longitude', 'latitude'];

    public function users(){
        return $this->hasMany('App\User');
    }


    // get users in this schools that had not been voted by $id, and profile picture
    public function usersNotVoted($id){
        $user = User::find($id);
        $yespls = DB::select('select users.id, users.name, users.email, users.gender, users.school_id, b.id as photo_id, b.photo from users left join photos b on b.id = (select photos.id from photos where user_id = users.id limit 1) where not exists(select * from votes where subject_id = :idsatu and object_id = users.id) and users.id != :iddua and school_id = :schoolid', ['idsatu' => $id, 'iddua' => $id, 'schoolid' => $this->id]);
        return $yespls;


    }
}
