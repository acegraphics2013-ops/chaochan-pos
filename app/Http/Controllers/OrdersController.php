<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Services\OrderService;
class OrdersController {
 public function index(){return view('orders.index');}
 public function feed(){
  $orders=DB::table('orders')->leftJoin('service_sessions','service_sessions.id','=','orders.service_session_id')->leftJoin('restaurant_tables','restaurant_tables.id','=','service_sessions.table_id')->join('bills','bills.id','=','orders.bill_id')->select('orders.*','restaurant_tables.number as table_number','bills.number as bill_number','bills.total as bill_total','bills.paid_amount','bills.status as bill_status')->whereIn('orders.status',['pending_confirmation','awaiting_payment','accepted','completed'])->orderByDesc('orders.id')->limit(120)->get();
  $items=DB::table('order_items')->whereIn('order_id',$orders->pluck('id'))->get()->groupBy('order_id');
  return response()->json($orders->map(fn($o)=>(array)$o+['items'=>$items->get($o->id,collect())]))->header('Cache-Control','no-store');
 }
 public function accept(int $id){
  DB::transaction(function() use($id){
   $order=DB::table('orders')->where('id',$id)->lockForUpdate()->first();abort_unless($order,404);
   abort_unless($order->status==='pending_confirmation',409,'Order has already been processed.');
   $bill=DB::table('bills')->where('id',$order->bill_id)->first();
   if($order->payment_mode==='pay_later'||$bill->status==='paid')OrderService::ticket($id);
   else DB::table('orders')->where('id',$id)->update(['status'=>'awaiting_payment','updated_at'=>now()]);
   OrderService::audit(auth()->id(),'order_accepted','orders',$id);
  },5);
  return response()->json(['message'=>'Order confirmed.']);
 }
 public function cancel(int $id){
  DB::transaction(function() use($id){
   $order=DB::table('orders')->where('id',$id)->lockForUpdate()->first();abort_unless($order,404);
   abort_unless(in_array($order->status,['pending_confirmation','awaiting_payment'],true),409,'Prepared or paid orders require manager-controlled reversal.');
   $bill=DB::table('bills')->where('id',$order->bill_id)->lockForUpdate()->first();
   abort_if((float)$bill->paid_amount>0,409,'Paid bills cannot be cancelled here.');
   DB::table('orders')->where('id',$id)->update(['status'=>'cancelled','updated_at'=>now()]);
   DB::table('bills')->where('id',$bill->id)->update(['total'=>OrderService::money((int)round(((float)$bill->total-(float)$order->total)*100)),'updated_at'=>now()]);
   OrderService::audit(auth()->id(),'order_cancelled','orders',$id);
  },5);
  return response()->json(['message'=>'Order cancelled.']);
 }
}
