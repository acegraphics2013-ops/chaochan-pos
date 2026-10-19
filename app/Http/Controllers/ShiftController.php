<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\OrderService;
class ShiftController {
 public function index(){
   $shift=DB::table('cashier_shifts')->where('user_id',auth()->id())->where('status','open')->first();
   $recent=DB::table('cashier_shifts')->where('user_id',auth()->id())->orderByDesc('id')->limit(20)->get();
   $received=$shift?DB::table('payments')->where('shift_id',$shift->id)->where('method','cash')->sum('amount'):0;
   return view('shifts.index',compact('shift','recent','received'));
 }
 public function open(Request $request){
   $data=$request->validate(['opening_cash'=>'required|numeric|min:0|max:1000000']);
   abort_if(DB::table('cashier_shifts')->where('user_id',auth()->id())->where('status','open')->exists(),409,'A shift is already open.');
   $id=DB::table('cashier_shifts')->insertGetId(['user_id'=>auth()->id(),'opening_cash'=>$data['opening_cash'],'status'=>'open','opened_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
   OrderService::audit(auth()->id(),'shift_opened','cashier_shifts',$id);return back()->with('success','Cashier shift opened.');
 }
 public function close(Request $request){
   $data=$request->validate(['closing_cash'=>'required|numeric|min:0|max:10000000']);
   DB::transaction(function() use($data){
    $shift=DB::table('cashier_shifts')->where('user_id',auth()->id())->where('status','open')->lockForUpdate()->first();abort_unless($shift,409,'No open shift.');
    DB::table('cashier_shifts')->where('id',$shift->id)->update(['closing_cash'=>$data['closing_cash'],'status'=>'closed','closed_at'=>now(),'updated_at'=>now()]);
    OrderService::audit(auth()->id(),'shift_closed','cashier_shifts',$shift->id,['closing_cash'=>$data['closing_cash']]);
   });
   return back()->with('success','Shift closed; review drawer variance in shift report.');
 }
}
