<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\OrderService;
class KitchenController {
 public function index(){return view('kitchen.index');}
 public function feed(){
  $tickets=DB::table('kitchen_tickets')->join('orders','orders.id','=','kitchen_tickets.order_id')->leftJoin('service_sessions','service_sessions.id','=','orders.service_session_id')->leftJoin('restaurant_tables','restaurant_tables.id','=','service_sessions.table_id')->select('kitchen_tickets.*','orders.number','orders.notes','orders.service_type','restaurant_tables.number as table_number')->whereIn('kitchen_tickets.status',['new','preparing','ready'])->orderBy('kitchen_tickets.received_at')->get();
  $items=DB::table('order_items')->whereIn('order_id',$tickets->pluck('order_id'))->get()->groupBy('order_id');
  return response()->json($tickets->map(fn($t)=>(array)$t+['items'=>$items->get($t->order_id,collect())]))->header('Cache-Control','no-store');
 }
 public function advance(Request $request,int $id){
   $data=$request->validate(['action'=>'required|in:start,ready,served']);
   DB::transaction(function() use($data,$id){
     $ticket=DB::table('kitchen_tickets')->where('id',$id)->lockForUpdate()->first();abort_unless($ticket,404);
     $transition=['new'=>['start','preparing','preparing_at'],'preparing'=>['ready','ready','ready_at'],'ready'=>['served','served','served_at']][$ticket->status]??null;
     abort_unless($transition && $transition[0]===$data['action'],409,'Invalid or outdated kitchen action.');
     DB::table('kitchen_tickets')->where('id',$id)->update(['status'=>$transition[1],$transition[2]=>now(),'updated_at'=>now()]);
     DB::table('orders')->where('id',$ticket->order_id)->update(['kitchen_status'=>$transition[1],'status'=>$transition[1]==='served'?'completed':'accepted','updated_at'=>now()]);
     OrderService::audit(auth()->id(),'kitchen_'.$data['action'],'orders',$ticket->order_id);
   },5);
   return response()->json(['message'=>'Kitchen status updated.']);
 }
}
