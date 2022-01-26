<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Court;
use App\Models\UserAppointment;

class UserController extends Controller
{
    private $loggedUser;
    public function __construct(){
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function getUser(){
        $array = ['error'=> ''];

        $info = $this->loggedUser;
        $error['data'] = $info;
        return $array;
    }

    public function editUser(Request $request){
        $array = ['error' => ''];

        $rules = [
            'name' => 'min:2',
            'email' => 'email|unique:users',
            'password' => 'same:password_confirm',
            'password_confirm' => 'same:password'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            $array['error'] = $validator->messages();
            return $array;
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirm = $request->input('password_confirm');

        $user = User::find($this->loggedUser->id);

        if($name){
            $user->name = $name;
        }

        if($email){
            $user->email = $email;
        }

        if($password){
            $user->password = password_hash($password, PASSWORD_DEFAULT);
        }

        $user->save();
        return $array;
    }

    public function getAppointments(){
        $array = ['error' =>'', 'list'=>[]];

        $apps = UserAppointment::select()
        ->where('id_user', $this->loggedUser->id)
        ->orderBy('ap_datetime', 'DESC')
        ->get();

        if($apps){
            foreach($apps as $app){
                $court = Court::find($app['id_court']);

                $service = CourtServices::find($app['id_service']);

                $array['list'][]= [
                    'id'=> $app['id'],
                    'ap_datetime' => $app['ap_datetime'],
                    'court' => $court,
                    'service' => $service
                ];
            }
        }

        return $array;
    }
}
