<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Leave;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function getAttendanceToday()
    {
        $userId = auth()->user()->id;
        $today = now()->toDateString();
        $currentMonth = now()->month;

        $attendanceToday = Attendance::select('start_time','end_time')
                            ->where('user_id', $userId)
                            ->whereDate('created_at',$today)
                            ->first();
        $attendanceThisMonth = Attendance::select('start_time','end_time','created_at')
                            ->where('user_id',$userId)
                            ->whereMonth('created_at',$currentMonth)
                            ->get()
                            ->map(function ($attendance){
                                return [
                                    'start_time' => $attendance->start_time,
                                    'end_time' => $attendance->end_time,
                                    'date' => $attendance->created_at->toString(),
                                ];
                            });
        return response()->json([
            'success' => true,
            'data' => [
                'today' => $attendanceToday,
                'this_month' => $attendanceThisMonth,
            ],
            'message' => 'Success get attendance today'
        ]);
    }

    public function getSchedule()
    {
        $schedule = Schedule::with('office','shift')
                    ->where('user_id',auth()->user()->id)
                    ->first();
        $today = Carbon::today()->format('Y-m-d');
        $approvedLeave = Leave::where('user_id',Auth ::user()->id)
                        ->where('status','approved')
                        ->whereDate('start_date', '<=',$today)
                        ->whereDate('end_date', '>=',$today)
                        ->exists();

        if($approvedLeave){
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Anda tidak dapat melakukan presensi karena sedang cuti'
            ]);
        }
        if($schedule->is_banned){
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'You are banned'
            ]);
        }else{
            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => 'Success get schedule'
            ]);
        }
        
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'data' => $validator->errors(),
                'message' => 'Validation error'
            ], 422);
        }

        $schedule = Schedule::where('user_id',Auth::user()->id)->first();

        $today = Carbon::today()->format('Y-m-d');
        $approvedLeave = Leave::where('user_id',Auth ::user()->id)
                        ->where('status','approved')
                        ->whereDate('start_date', '<=',$today)
                        ->whereDate('end_date', '>=',$today)
                        ->exists();

        if($approvedLeave){
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Anda tidak dapat melakukan presensi karena sedang cuti'
            ]);
        }
        if($schedule){
            $attedance = Attendance::where('user_id',Auth::user()->id)
                        ->whereDate('created_at',date('Y-m-d'))->first();
            if(!$attedance)
            {
                $attedance = Attendance::create([
                    'user_id' => Auth::user()->id,
                    'schedule_latitude' => $schedule->office->latitude,
                    'schedule_longitude' => $schedule->office->longitude,
                    'schedule_start_time' => $schedule->shift->start_time,
                    'schedule_end_time' => $schedule->shift->end_time,
                    'start_latitude' => $request->latitude,
                    'start_longitude' => $request->longitude,
                    'start_time' => Carbon::now()->toTimeString(),
                    'end_time' => Carbon::now()->toTimeString(),
                ]);
            }else{
                $attedance->update([
                    'end_latitude' => $request->latitude,
                    'end_longitude' => $request->longitude,
                    'end_time' => Carbon::now()->toTimeString(),
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $attedance,
                'message' => 'Success store attendance'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Schedule not found'
            ]);
        }
    }

    public function getAttendanceByMonthAndYear($month,$year)
    {
        $validator = Validator::make(['month' => $month,'year' => $year],[
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2023|max:'.date('Y'),
        ]);
        
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'data' => $validator->errors(),
                'message' => 'Validation error'
            ], 422);
        }

        $userId = auth()->user()->id;

        $attendanceList = Attendance::select('start_time','end_time','created_at')
                            ->where('user_id',$userId)
                            ->whereMonth('created_at',$month)
                            ->whereYear('created_at',$year)
                            ->get()
                            ->map(function ($attendance){
                                return [
                                    'start_time' => $attendance->start_time,
                                    'end_time' => $attendance->end_time,
                                    'date' => $attendance->created_at->toString(),
                                ];
                            });
        return response()->json([
            'success' => true,
            'data' => $attendanceList,
            'message' => 'Success get attendance by month and year'
        ]);
    }

    public function banned()
    {
        $schedule = Schedule::where('user_id',Auth::user()->id)->first();
        if($schedule){
            $schedule->update([
                'is_banned' => true
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $schedule,
            'message' => 'Success banned schedule'
        ]);
    }

    public function getImage()
    {
        $user = auth()->user();
        return response()->json([
            'success' => true,
            'data' => $user->image_url,
            'message' => 'Success get image'
        ]);
    }

}
