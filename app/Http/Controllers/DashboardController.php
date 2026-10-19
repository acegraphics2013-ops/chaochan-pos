<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
class DashboardController {
 public function index(){
   $today=now()->toDateString();
   $payments=DB::table('payments')->whereDate('created_at',$today)->selectRaw('method, SUM(amount) as total')->groupBy('method')->pluck('total','method');
   $orderCount=DB::table('orders')->whereDate('created_at',$today)->count();
   $openBills=DB::table('bills')->where('status','open')->where('total','>',0)->count();
   $pending=DB::table('orders')->where('status','pending_confirmation')->count();
   $kitchen=DB::table('kitchen_tickets')->whereIn('status',['new','preparing'])->count();
   $lowStock=DB::table('inventory_items')->where('reorder_level','>',0)->whereColumn('quantity','<=','reorder_level')->count();
   $todaySales=DB::table('payments')->whereDate('created_at',$today)->sum('amount');
   $recent=DB::table('orders')->leftJoin('service_sessions','service_sessions.id','=','orders.service_session_id')->leftJoin('restaurant_tables','restaurant_tables.id','=','service_sessions.table_id')->select('orders.*','restaurant_tables.number as table_number')->latest('orders.id')->limit(8)->get();
   $top=DB::table('order_items')->join('orders','orders.id','=','order_items.order_id')->whereIn('orders.status',['accepted','completed'])->select('order_items.name_snapshot',DB::raw('SUM(order_items.qty) as qty'))->groupBy('order_items.name_snapshot')->orderByDesc('qty')->limit(6)->get();
   return view('dashboard',compact('payments','orderCount','openBills','pending','kitchen','todaySales','recent','top','lowStock'));
 }
}
