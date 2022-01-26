<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Court;
use App\Models\CourtAvailability;
use App\Models\CourtServices;
use App\Models\UserAppointment;

class CourtController extends Controller
{
    private $loggedUser;
    public function __construct(){
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    } 

    public function getAll(Request $request){
        $array = ['error' => ''];

        $courts = Court::all();

        $array['data'] = $courts;

        return $array;
    }

    public function getOne($id){
        $array = ['error' => ''];

        $court = Court::find($id);

        if($court){
            $court['available'] = [];
        

        $availability = [];

        $avails = CourtAvailability::where('id_court',$court->id)->get();
        $availWeekdays = [];

        foreach($avails as $item){
            $availWeekdays[$item['weekday']] = explode(',', $item['hours']);
        }

        $appointments = [];

        $appQuery = UserAppointment::where('id_court', $court->id)->whereBetween('ap_datetime', [
            date('Y-m-d').'00:00:00',
            date('Y-m-d', strtotime('+20 days')).'23:59:59'
        ])->get();

        foreach($appQuery as $appItem){
            $appointments[] = $appItem['ap_datetime'];
        }

        for($q=0;$q<20;$q++){
            $timeItem = strtotime('+'.$q.' days');
            $weekday = date('w', $timeItem);

            if(in_array($weekday, array_keys($availWeekdays))){
                $hours = [];

                $dayItem = date('Y-m-d', $timeItem);

                foreach($availWeekdays[$weekday] as $hourItem){
                    $dayFormated = $dayItem.' '.$hourItem.'00:00:00';
                    if(!in_array($dayFormated, $appointments)){
                        $hours[] = $hourItem;
                    }
                }

                if(count($hours) > 0){
                    $availability[] = [
                        'date' => $dayItem,
                        'hours' => $hours
                    ];
                }
            }
        }

        $court['available'] = $availability;

        $array['data'] = $court;

      }else{
        $array['error'] = 'Quadra não existe';
        return $array;
      }

        return $array;
    }

    public function setAppointments($id, Request $request){
        $array = ['error' => ''];

        $service = $request->input('service');

        $year = intval($request->input('year'));
        $month = intval($request->input('month'));
        $day = intval($request->input('day'));
        $hour = intval($request->input('hour'));

        $month = ($month < 10) ? '0'.$month : $month;
        $day = ($day < 10) ? '0'.$day : $day;
        $hour = ($hour < 10) ? '0'.$hour : $hour;

        $courtservice = CourtServices::select()->where('id', $service)->where('id_court', $id)->first();

        if($courtservice){
            $apDate = $year.'-'.$month.'-'.$day.' '.$hour.':00:00';
            if(strtotime($apDate) > 0){
                $apps = UserAppointment::select()->where('id_court', $id)->where('ap_datetime', $apDate)->count();

                if($apps === 0){
                    $weekday = date('w', strtotime($apDate));
                    $avail = CourtAvailability::select()->where('id_court', $id)->where('weekday', $weekday)->first();

                    if($avail){
                        $hours = explode(',', $avail['hours']);

                        if(in_array($hour.':00', $hours)){
                            $newApp = new userAppointment();
                            $newApp->id_user = $this->loggedUser->id;
                            $newApp->id_court = $id;
                            $newApp->id_service = $service;
                            $newApp->ap_datetime = $apDate;
                            $newApp->save();
                        }else{
                            $array['error'] = 'Quadra não disponivel nesse horário';
                        }
                    }else{
                        $array['error'] = 'Quadra não disponivel nessa data';
                    }
                }else{
                    $array['error'] = 'Serviço indisponivel';
                }
            }
        }

        return $array;
    }
}
