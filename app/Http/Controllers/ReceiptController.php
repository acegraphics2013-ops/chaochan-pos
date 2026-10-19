<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
class ReceiptController {
 public function show(int $bill){
   $record=DB::table('bills')->where('id',$bill)->firstOrFail();
   $orders=DB::table('orders')->where('bill_id',$bill)->where('status','!=','cancelled')->orderBy('id')->get();
   $items=DB::table('order_items')->whereIn('order_id',$orders->pluck('id'))->orderBy('id')->get();
   $payments=DB::table('payments')->where('bill_id',$bill)->orderBy('id')->get();
   $visit=$record->service_session_id?DB::table('service_sessions')->leftJoin('restaurant_tables','restaurant_tables.id','=','service_sessions.table_id')->where('service_sessions.id',$record->service_session_id)->select('service_sessions.*','restaurant_tables.number as table_number')->first():null;
   return response()->view('sales.receipt',compact('record','orders','items','payments','visit'))->header('Cache-Control','private, no-store');
 }
}
