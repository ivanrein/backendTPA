<?php

namespace App\Http\Controllers;

use App\Photo;
use App\School;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class ShineController extends Controller
{

    public function register(Request $request){
        $email = $request->json()->get('email');
        if(User::where('email','=', $email)->first() != null)
            return Response::json(['error' => 'Email already registered'], 400);

        $user = User::create($request->all());
        return Response::json(['user_info' => $request->json()->get('email')], 200);
//        return Response::json(['user' => $user], 200);
    }

    public function login(Request $request){
        $email = $request->json()->get('email');
        $pwd = $request->json()->get('password');
        $user = User::with('school')->where('email' ,'=',$email)->where('password', '=', $pwd)->first();
        if($user != null){
            $user->auth_token = str_random(60);
            $user->save();
            return Response::json(['result' => 'success', 'token' => $user->auth_token, 'user' => $user], 200);
        }else {
            return Response::json(['result' => 'failed'], 204);
        }
    }

    public function update(Request $request){
        $email = $request->json()->get('email');
        $bio = $request->json()->get('bio');
        $user = User::where('email', '=', $email);
        $user->bio = $bio;
        $user->save();
        return Response::json(['result' => 'success', 'user' => $user], 200);
    }

    public function deletePhoto(Request $request){
            $photoId = $request->json()->get('id');
            $photo = Photo::find($photoId);
            $photo->delete();
            return Response::json(['result' => 'success'], 200);
    }

    public function savePhoto(Request $request){
        $user = $request->get('CurrentUser');
        if($user != null) {
            $photo64 = $request->json()->get('encodedphoto');
            Photo::create(['user_id' => $user->id, 'photo' => $photo64]);
            return Response::json(['result' => 'success'], 200);
        }
        else{
            return Response::json(['result' => 'failed', 'message' => 'not authenticated']);
        }
    }



    public function facebookLogin(Request $request){
        //get fb access token
        // verify fb access token
            // generate token for user
            // update token in db
            // return token for user
        // else return false
        $user_ac_token = \Illuminate\Support\Facades\Request::header('fbtoken'); //facebook access token
         $userEmail = $request->json()->get('email');


        $info = json_decode(file_get_contents('https://graph.facebook.com/app/?access_token='.$user_ac_token), true);

        if($info['id'] == env('FB_APP_ID')){
            $user = User::with('school')->where('email', '=', $userEmail)->first();
            if($user != null){
                //login
                $user->auth_token = str_random(60);
                $user->save();
                return Response::json(['result' => 'success', 'token' => $user->auth_token, 'user' => $user], 200);
            }else{
                $user = User::with('school')->find(User::create($request->all())->id);
                $user->auth_token = str_random(60);
                $user->save();

                return Response::json(['result' => 'success', 'token' => $user->auth_token, 'user' => $user], 200);
            }
        }else{

            return Response::json(['result' => 'failed'], 400);
        }



    }

    public function checkUser(Request $request){
        $user = User::with('school')->where('email', '=', $request->json()->get('email'))->first();

        return Response::json(['user' => $user]);
    }
    public function getSchools(){
        $schools = \App\School::all();
        return Response::json(['schools' => $schools], 200);
    }

    public function getUsers(Request $request){
        $lat = $request->get('lat');
        $long = $request->get('long');
        $schools = School::all();
        $users = new \Illuminate\Database\Eloquent\Collection;
        for($i = 0; i < $schools->count(); $i++){
            $schoolLat = $schools->get($i)->latitude;
            $schoolLong = $schools->get($i)->longitude;
            if(haversine($lat, $long, $schoolLat, $schoolLong) < 10){
                $users->push($schools->get($i)->usersNotVoted($request->get('CurrentUser')->id));
            }
        }

        return Response::json($users->collapse(), 200);
    }

    public function getPhotos(Request $request){
        $user = User::where('email', '=', $request->json()->get('email'))->first();
        $photos = $user->photos()->get();
        return Response::json($photos, 200);
    }


    public function fetchCurrentUser(Request $request){
        $currUser = $request->get('CurrentUser'); // dari middleware
        return Response::json(['user' => $currUser, 'school' => $currUser->school()->get()[0]], 200);
    }

    public function test(Request $request){
//        $c = new \Illuminate\Database\Eloquent\Collection;
//        $schools = School::all();
//        $c->push($schools->get(0)->users()->get());
//        $c->push($schools->get(0)->users()->get());
//        $c->push($schools->get(0)->users()->get());
//        return Response::json([$c->collapse()], 200);

//        $request->attributes->add(['ad' => 'asd']);
//        return $request->get('ad');
//        $asd = DB::table('users')
//            ->whereNotExists(function ($query) {
//                $query->select(DB::raw(1))
//                    ->from('votes')
//                    ->whereRaw('object_id = users.id and subject_id = 1')->where('users.id', '!=', 1);
//            })
//            ->get();
        $lat = $request->get('lat');
        if($lat != null )return $lat;
        else return "asd";
    }

    function haversine($lat1,$lon1,$lat2,$lon2) {
        $R = 6371; // Radius of the earth in km
        $dLat = deg2rad($lat2-$lat1);  // deg2rad below
        $dLon = deg2rad($lon2-$lon1);
        $a =sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(Math.sqrt($a), sqrt(1-$a));
        $d = $R * $c;
        return $d;
    }


    // buat ngambil list top school... harusnya sih
    public function getTopSchools(Request $request){
        $topSchools = DB::select('select a.id, a.name, avg(yeah.oneuseraverage) as schoolaverage from schools a, ( select b.id, b.school_id, b.name, avg(c.rate) as oneuseraverage from users b join votes c on b.id = c.object_id group by b.id, b.name ) as yeah where yeah.school_id = a.id group by id, name order by schoolaverage desc');
        return Response::json(['topschools' => $topSchools], 200);
    }


    // nerima school id
    public function getTopStudents(Request $request){
        $schoolId = $request->get('id');
        $topStudents = DB::select('select a.name, avg(b.rate) as rata from users a join votes b on a.id = b.object_id where a.school_id = :schoolid group by a.id, a.name order by rata desc', ['schoolid' => $schoolId]);
        return Response::json(['topStudents' => $topStudents]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


}
