<?php
namespace Tests\Feature;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;
class OrderFlowTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void { parent::setUp(); $this->seed(DatabaseSeeder::class); }
 private function cashier():User {return User::create(['name'=>'Test Cashier','username'=>'cashier1','password'=>'safe-example-pass-123','role'=>'cashier','active'=>true]);}
 public function test_pay_first_waits_for_payment_before_kitchen_receives_it():void {
  $cashier=$this->cashier();
  DB::table('cashier_shifts')->insert(['user_id'=>$cashier->id,'opening_cash'=>500,'status'=>'open','opened_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
  $productId=DB::table('products')->where('sku','H1')->value('id');
  $order=$this->actingAs($cashier)->postJson('/pos/orders',['service_type'=>'take_out','payment_mode'=>'pay_first','idempotency_key'=>'test-first-order-key','items'=>[['product_id'=>$productId,'qty'=>1]]])->assertOk()->json();
  $this->assertDatabaseHas('orders',['id'=>$order['id'],'status'=>'awaiting_payment']);
  $this->assertDatabaseMissing('kitchen_tickets',['order_id'=>$order['id']]);
  $this->actingAs($cashier)->postJson('/bills/'.$order['bill_id'].'/payments',['method'=>'cash','amount'=>120,'tendered'=>150,'idempotency_key'=>'test-first-payment-key'])->assertOk();
  $this->assertDatabaseHas('orders',['id'=>$order['id'],'status'=>'accepted']);
  $this->assertDatabaseHas('kitchen_tickets',['order_id'=>$order['id'],'status'=>'new']);
  $this->actingAs($cashier)->postJson('/bills/'.$order['bill_id'].'/payments',['method'=>'cash','amount'=>120,'tendered'=>150,'idempotency_key'=>'test-first-payment-key'])->assertOk();
  $this->assertSame(1,DB::table('payments')->where('bill_id',$order['bill_id'])->count());
 }
 public function test_qr_pay_later_needs_cashier_acceptance_before_kitchen():void {
  $table=DB::table('restaurant_tables')->where('number','01')->first();
  $token=Crypt::decryptString($table->qr_token_encrypted);
  $pid=DB::table('products')->where('sku','L1')->value('id');
  $res=$this->postJson('/q/'.$token.'/orders',['payment_mode'=>'pay_later','items'=>[['product_id'=>$pid,'qty'=>1]],'idempotency_key'=>'guest-test-order-one'])->assertOk()->json();
  $this->assertDatabaseHas('orders',['id'=>$res['id'],'status'=>'pending_confirmation']);
  $this->assertDatabaseMissing('kitchen_tickets',['order_id'=>$res['id']]);
  $this->actingAs($this->cashier())->postJson('/orders/'.$res['id'].'/accept',[])->assertOk();
  $this->assertDatabaseHas('orders',['id'=>$res['id'],'status'=>'accepted']);
  $this->assertDatabaseHas('kitchen_tickets',['order_id'=>$res['id'],'status'=>'new']);
 }
 public function test_customer_supplied_price_is_ignored():void {
  $cashier=$this->cashier();$id=DB::table('products')->where('sku','S1')->value('id');
  $res=$this->actingAs($cashier)->postJson('/pos/orders',['service_type'=>'take_out','payment_mode'=>'pay_first','idempotency_key'=>'tamper-test','items'=>[['product_id'=>$id,'qty'=>1,'unit_price'=>0.01]]])->assertOk()->json();
  $this->assertSame('120.00',number_format((float)DB::table('orders')->where('id',$res['id'])->value('total'),2,'.',''));
 }
}
