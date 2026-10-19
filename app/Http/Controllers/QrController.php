<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\OrderService;
class QrController {
 private function table(string $token){return DB::table('restaurant_tables')->where('qr_token_hash',hash('sha256',$token))->where('active',true)->firstOrFail();}
 public function menu(string $token){$table=$this->table($token);$categories=DB::table('categories')->orderBy('sort_order')->get();$products=DB::table('products')->where('available',true)->get();return view('qr.menu',compact('table','categories','products','token'));}
 public function submit(Request $request,string $token,OrderService $service){
  $table=$this->table($token);
  $data=$request->validate(['payment_mode'=>'required|in:pay_first,pay_later','notes'=>'nullable|string|max:500','idempotency_key'=>'required|string|max:100','items'=>'required|array|min:1|max:30','items.*.product_id'=>'required|integer|exists:products,id','items.*.qty'=>'required|integer|min:1|max:20']);
  return response()->json($service->create($data+['service_type'=>'dine_in'],null,$table->id));
 }
 public function orders(string $token){
  $table=$this->table($token);
  $session=DB::table('service_sessions')->where('table_id',$table->id)->where('status','open')->orderByDesc('id')->first();
  if(!$session)return response()->json(['orders'=>[]])->header('Cache-Control','no-store');
  $orders=DB::table('orders')->where('service_session_id',$session->id)->orderBy('id')->get(['id','number','status','kitchen_status','payment_mode','total','created_at']);
  $items=DB::table('order_items')->whereIn('order_id',$orders->pluck('id'))->get(['order_id','name_snapshot','qty','line_total'])->groupBy('order_id');
  $out=$orders->map(fn($o)=>[
   'id'=>$o->id,'number'=>$o->number,'status'=>$o->status,'kitchen_status'=>$o->kitchen_status,
   'payment_mode'=>$o->payment_mode,'total'=>(float)$o->total,'created_at'=>$o->created_at,
   'items'=>$items->get($o->id,collect())->map(fn($i)=>['name'=>$i->name_snapshot,'qty'=>$i->qty,'line_total'=>(float)$i->line_total])->values(),
  ]);
  return response()->json(['orders'=>$out])->header('Cache-Control','no-store');
 }
 public function tracking(string $token,int $order){
  $table=$this->table($token);
  $record=DB::table('orders')->join('service_sessions','service_sessions.id','=','orders.service_session_id')->where('orders.id',$order)->where('service_sessions.table_id',$table->id)->select('orders.number','orders.status','orders.kitchen_status','orders.created_at')->firstOrFail();
  return response()->json($record)->header('Cache-Control','no-store');
 }
}
