<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\OrderService;
class PaymentController {
 public function store(Request $request,int $billId){
  $data=$request->validate(['method'=>'required|in:cash,gcash','amount'=>'required|numeric|gt:0|max:999999','tendered'=>'nullable|numeric|gte:0','reference_number'=>'nullable|string|max:120','verified'=>'nullable|boolean','idempotency_key'=>'required|string|max:100','proof'=>'nullable|image|max:4096']);
  if($data['method']==='gcash'&&(empty($data['reference_number'])||empty($data['verified'])))throw ValidationException::withMessages(['method'=>'GCash reference and cashier verification are required.']);
  $proof=$request->hasFile('proof')?$request->file('proof')->store('payment-proofs','private'):null;
  $result=DB::transaction(function() use($data,$billId,$proof){
   $prior=DB::table('payments')->where('idempotency_key',$data['idempotency_key'])->first();
   if($prior)return ['message'=>'Payment already recorded.','replayed'=>true];
   $bill=DB::table('bills')->where('id',$billId)->lockForUpdate()->first();abort_unless($bill,404);
   // Lock-serialized idempotency check for concurrent payment retries.
   $retried=DB::table('payments')->where('idempotency_key',$data['idempotency_key'])->first();
   if($retried)return ['message'=>'Payment already recorded.','replayed'=>true];
   abort_unless($bill->status==='open',409,'Bill is already fully paid.');
   $due=(int)round(((float)$bill->total-(float)$bill->paid_amount)*100);
   $cents=(int)round(((float)$data['amount'])*100);
   if($cents>$due)throw ValidationException::withMessages(['amount'=>'Payment cannot exceed remaining balance.']);
   if($cents<=0)throw ValidationException::withMessages(['amount'=>'Amount must be positive.']);
   $tendered=(int)round(((float)($data['tendered']??$data['amount']))*100);
   if($data['method']==='cash' && $tendered<$cents)throw ValidationException::withMessages(['tendered'=>'Cash received is less than payment.']);
   $shift=DB::table('cashier_shifts')->where('user_id',auth()->id())->where('status','open')->lockForUpdate()->first();
   if(!$shift)throw ValidationException::withMessages(['shift'=>'Open your cashier shift before processing payment.']);
   DB::table('payments')->insert(['bill_id'=>$billId,'cashier_id'=>auth()->id(),'shift_id'=>$shift->id,'method'=>$data['method'],'amount'=>OrderService::money($cents),'tendered'=>$data['method']==='cash'?OrderService::money($tendered):null,'change_given'=>$data['method']==='cash'?OrderService::money($tendered-$cents):0,'reference_number'=>$data['reference_number']??null,'proof_path'=>$proof,'idempotency_key'=>$data['idempotency_key'],'verified_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
   $newPaid=(int)round(((float)$bill->paid_amount)*100)+$cents;
   $paid=$newPaid>=$due+(int)round(((float)$bill->paid_amount)*100);
   DB::table('bills')->where('id',$billId)->update(['paid_amount'=>OrderService::money($newPaid),'status'=>$paid?'paid':'open','updated_at'=>now()]);
   if($paid){
    $orders=DB::table('orders')->where('bill_id',$billId)->where('status','awaiting_payment')->pluck('id');
    foreach($orders as $id)OrderService::ticket($id);
   }
   OrderService::audit(auth()->id(),'payment_recorded','bills',$billId,['method'=>$data['method'],'amount'=>OrderService::money($cents)]);
    return ['message'=>$paid?'Paid in full; kitchen notified for confirmed orders.':'Partial payment recorded.','paid'=>$paid,'balance'=>OrderService::money(max(0,$due-$cents)),'change'=>$data['method']==='cash'?OrderService::money($tendered-$cents):null];
  },5);
  return response()->json($result);
 }
}
