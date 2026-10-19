<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Models\User;
class OrderService {
 public function create(array $input,?User $user=null,?int $tableId=null):array {
  $key=$input['idempotency_key'];
  try { return DB::transaction(function() use($input,$user,$tableId,$key){
   $old=DB::table('orders')->where('idempotency_key',$key)->first();
   if($old) return ['id'=>$old->id,'number'=>$old->number,'status'=>$old->status,'bill_id'=>$old->bill_id,'replayed'=>true];
   $mode=$input['payment_mode'];$service=$input['service_type']??'dine_in';
   if($tableId!==null){
     $table=DB::table('restaurant_tables')->where('id',$tableId)->where('active',true)->lockForUpdate()->first();
     if(!$table) throw ValidationException::withMessages(['table'=>'Table unavailable.']);
     $visit=DB::table('service_sessions')->where('table_id',$tableId)->where('status','open')->first();
     if(!$visit){$visitId=DB::table('service_sessions')->insertGetId(['table_id'=>$tableId,'service_type'=>'dine_in','status'=>'open','opened_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);DB::table('restaurant_tables')->where('id',$tableId)->update(['status'=>'occupied','updated_at'=>now()]);}
     else $visitId=$visit->id;
     $service='dine_in';
   } else {$visitId=null;if($service==='dine_in')throw ValidationException::withMessages(['table_id'=>'Select a table for dine-in orders.']);}
   // Re-check once any table-row lock has been acquired.
   $existing=DB::table('orders')->where('idempotency_key',$key)->first();
   if($existing) return ['id'=>$existing->id,'number'=>$existing->number,'status'=>$existing->status,'bill_id'=>$existing->bill_id,'replayed'=>true];
   $ids=array_column($input['items'],'product_id');
   $products=DB::table('products')->whereIn('id',$ids)->where('available',true)->get()->keyBy('id');
   $rows=[];$totalCents=0;
   foreach($input['items'] as $line){
      $p=$products->get($line['product_id']);
      if(!$p)throw ValidationException::withMessages(['items'=>'A selected product is unavailable. Refresh the menu.']);
      $unitCents=(int)round(((float)$p->price)*100);$cents=$unitCents*(int)$line['qty'];$totalCents+=$cents;
      $rows[]=['product_id'=>$p->id,'name_snapshot'=>$p->name,'unit_price'=>self::money($unitCents),'qty'=>(int)$line['qty'],'line_total'=>self::money($cents),'created_at'=>now(),'updated_at'=>now()];
   }
   if($totalCents<=0)throw ValidationException::withMessages(['items'=>'Cart is empty.']);
   $bill=null;
   if($visitId && $mode==='pay_later')$bill=DB::table('bills')->where('service_session_id',$visitId)->where('payment_mode','pay_later')->where('status','open')->lockForUpdate()->first();
   if(!$bill){
     $billId=DB::table('bills')->insertGetId(['number'=>'TMP-'.Str::uuid(),'service_session_id'=>$visitId,'payment_mode'=>$mode,'total'=>0,'paid_amount'=>0,'status'=>'open','created_at'=>now(),'updated_at'=>now()]);
     DB::table('bills')->where('id',$billId)->update(['number'=>'BL-'.date('Ymd').'-'.str_pad($billId,6,'0',STR_PAD_LEFT)]);
   } else $billId=$bill->id;
   $existingTotalCents=(int)round(((float)(DB::table('bills')->where('id',$billId)->value('total')))*100);
   DB::table('bills')->where('id',$billId)->update(['total'=>self::money($existingTotalCents+$totalCents),'updated_at'=>now()]);
   $isDirect=$user!==null;
   $status=$isDirect?($mode==='pay_later'?'accepted':'awaiting_payment'):'pending_confirmation';
   $kitchen=$status==='accepted'?'new':'not_sent';
   $orderId=DB::table('orders')->insertGetId(['number'=>'TMP-'.Str::uuid(),'service_session_id'=>$visitId,'bill_id'=>$billId,'created_by'=>$user?->id,'source'=>$isDirect?'cashier':'qr','service_type'=>$service,'payment_mode'=>$mode,'status'=>$status,'kitchen_status'=>$kitchen,'total'=>self::money($totalCents),'notes'=>$input['notes']??null,'idempotency_key'=>$key,'accepted_at'=>$status==='accepted'?now():null,'created_at'=>now(),'updated_at'=>now()]);
   $number='CH-'.date('Ymd').'-'.str_pad($orderId,6,'0',STR_PAD_LEFT);
   DB::table('orders')->where('id',$orderId)->update(['number'=>$number]);
   foreach($rows as &$row){$row['order_id']=$orderId;}unset($row);
   DB::table('order_items')->insert($rows);
   if($status==='accepted')self::ticket($orderId);
   self::audit($user?->id,'order_created','orders',$orderId,['source'=>$isDirect?'cashier':'qr','mode'=>$mode]);
   return ['id'=>$orderId,'number'=>$number,'status'=>$status,'bill_id'=>$billId,'replayed'=>false];
  },5); } catch(QueryException $e) {
   // Concurrent retries can race on take-out orders, which have no table lock.
   $existing=DB::table('orders')->where('idempotency_key',$key)->first();
   if($existing) return ['id'=>$existing->id,'number'=>$existing->number,'status'=>$existing->status,'bill_id'=>$existing->bill_id,'replayed'=>true];
   throw $e;
  }
 }
 public static function ticket(int $orderId):void {
  DB::table('kitchen_tickets')->insertOrIgnore(['order_id'=>$orderId,'status'=>'new','received_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
  DB::table('orders')->where('id',$orderId)->update(['status'=>'accepted','kitchen_status'=>'new','accepted_at'=>now(),'updated_at'=>now()]);
 }
 public static function money(int $cents):string{return number_format($cents/100,2,'.','');}
 public static function audit(?int $userId,string $action,string $table,int $id,array $details=[]):void {
  DB::table('audit_logs')->insert(['user_id'=>$userId,'action'=>$action,'entity_type'=>$table,'entity_id'=>$id,'details'=>json_encode($details),'created_at'=>now(),'updated_at'=>now()]);
 }
}
