<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\OrderService;
class PosController {
 public function index(){
  $categories=DB::table('categories')->orderBy('sort_order')->get();$products=DB::table('products')->where('available',true)->orderBy('id')->get();$tables=DB::table('restaurant_tables')->where('active',true)->orderBy('number')->get();
  $openShift=DB::table('cashier_shifts')->where('user_id',auth()->id())->where('status','open')->first();
  return view('pos.index',compact('categories','products','tables','openShift'));
 }
 public function store(Request $request,OrderService $service){
  $data=$request->validate(['service_type'=>'required|in:dine_in,take_out','table_id'=>'nullable|integer|exists:restaurant_tables,id','payment_mode'=>'required|in:pay_first,pay_later','notes'=>'nullable|string|max:500','idempotency_key'=>'required|string|max:100','items'=>'required|array|min:1|max:50','items.*.product_id'=>'required|integer|exists:products,id','items.*.qty'=>'required|integer|min:1|max:50']);
  if($data['service_type']==='dine_in'&&!isset($data['table_id']))return response()->json(['message'=>'Select a dine-in table.'],422);
  return response()->json($service->create($data,$request->user(),$data['service_type']==='dine_in'?(int)$data['table_id']:null));
 }
}
