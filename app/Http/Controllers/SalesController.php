<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SalesController {
 private function data(Request $request){
   $date=$request->validate(['from'=>'nullable|date','to'=>'nullable|date']);
   $from=$date['from']??now()->startOfMonth()->toDateString();$to=$date['to']??now()->toDateString();
   $items=DB::table('payments')->join('bills','bills.id','=','payments.bill_id')->join('users','users.id','=','payments.cashier_id')->select('payments.*','bills.number as bill_number','users.name as cashier')->whereDate('payments.created_at','>=',$from)->whereDate('payments.created_at','<=',$to)->orderByDesc('payments.id')->limit(1000)->get();
   return [$items,$from,$to];
 }
 public function index(Request $request){[$payments,$from,$to]=$this->data($request);$gross=$payments->sum('amount');return view('sales.index',compact('payments','from','to','gross'));}
 public function csv(Request $request){[$payments]=$this->data($request);
   return response()->streamDownload(function()use($payments){$o=fopen('php://output','w');fputcsv($o,['Payment ID','Bill','Date','Cashier','Method','Amount','Reference']);foreach($payments as $p){$safe=function($v){$s=(string)($v??'');return preg_match('/^[=+\-@\t\r]/',$s)?"'".$s:$s;};fputcsv($o,[$p->id,$safe($p->bill_number),$p->created_at,$safe($p->cashier),$p->method,$p->amount,$safe($p->reference_number)]);}fclose($o);},'chaochan-payments.csv',['Content-Type'=>'text/csv; charset=utf-8']);
 }
}
