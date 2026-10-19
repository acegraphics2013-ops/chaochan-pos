<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use App\Services\OrderService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
class TableController {
 public function index(){
  $base=$this->customerBase();
  $tables=DB::table('restaurant_tables')->orderBy('number')->get()->map(function($t)use($base){$t->order_url=$base.route('qr.menu',Crypt::decryptString($t->qr_token_encrypted),false);return $t;});
  return view('tables.index',compact('tables','base'));
 }
 private function customerBase():string{
  $override=trim((string)config('app.public_url'));
  if($override!=='')return rtrim($override,'/');
  $request=request();
  $host=strtolower($request->getHost());
  if($this->hostNeedsLan($host)){
   $lan=$this->lanIp();
   if($lan)return $request->getScheme().'://'.$lan.$this->portSuffix();
   $appHost=strtolower((string)parse_url((string)config('app.url'),PHP_URL_HOST));
   if($appHost!==''&&!$this->hostNeedsLan($appHost))return $request->getScheme().'://'.$appHost.$this->portSuffix();
  }
  return $request->getSchemeAndHttpHost();
 }
 private function hostNeedsLan(string $host):bool{
  $host=strtolower($host);
  if(in_array($host,['localhost','127.0.0.1','::1'],true))return true;
  return str_ends_with($host,'.test')||str_ends_with($host,'.local');
 }
 private function portSuffix():string{
  $request=request();$port=(int)$request->getPort();$scheme=$request->getScheme();
  if(($scheme==='http'&&$port===80)||($scheme==='https'&&$port===443))return '';
  return ':'.$port;
 }
 private function lanIp():?string{
  $candidates=[];
  $server=(string)($_SERVER['SERVER_ADDR']??'');
  if($server!=='')$candidates[]=$server;
  $name=gethostname();
  if($name){$resolved=@gethostbynamel($name);if(is_array($resolved))$candidates=array_merge($candidates,$resolved);}
  foreach($candidates as $ip){
   if(filter_var($ip,FILTER_VALIDATE_IP)===false)continue;
   if(in_array($ip,['127.0.0.1','::1'],true))continue;
   if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)===false)return $ip;
  }
  return null;
 }
 public function store(Request $request){
  $data=$request->validate(['number'=>'required|string|max:20|unique:restaurant_tables,number','capacity'=>'required|integer|min:1|max:50']);
  $token=bin2hex(random_bytes(24));
  DB::table('restaurant_tables')->insert($data+['status'=>'available','active'=>true,'qr_token_hash'=>hash('sha256',$token),'qr_token_encrypted'=>Crypt::encryptString($token),'created_at'=>now(),'updated_at'=>now()]);
  return back()->with('success','Table and unique QR created.');
 }
 public function update(Request $request,int $id){
  abort_unless(DB::table('restaurant_tables')->where('id',$id)->exists(),404);
  $data=$request->validate(['number'=>['required','string','max:20',Rule::unique('restaurant_tables','number')->ignore($id)],'capacity'=>'required|integer|min:1|max:50']);
  $data['active']=$request->boolean('active');$data['updated_at']=now();
  DB::table('restaurant_tables')->where('id',$id)->update($data);
  return back()->with('success','Table updated.');
 }
 public function destroy(int $id){
  $table=DB::table('restaurant_tables')->where('id',$id)->first();abort_unless($table,404);
  if(DB::table('service_sessions')->where('table_id',$id)->exists())return back()->with('error','This table has visit history. Deactivate it instead of deleting.');
  DB::table('restaurant_tables')->where('id',$id)->delete();
  OrderService::audit(auth()->id(),'table_deleted','restaurant_tables',$id);
  return back()->with('success','Table deleted.');
 }
 public function qr(int $id){
  $table=DB::table('restaurant_tables')->where('id',$id)->firstOrFail();
   $url=$this->customerBase().route('qr.menu',Crypt::decryptString($table->qr_token_encrypted),false);
   $svg=(new SvgWriter())->write(new QrCode(data:$url,size:380,margin:16))->getString();
   return response($svg,200,['Content-Type'=>'image/svg+xml','Content-Disposition'=>'inline; filename="chaochan-table-'.preg_replace('/[^a-zA-Z0-9]/','',$table->number).'.svg"','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff']);
 }
 public function close(int $id){
   DB::transaction(function() use($id){
    $table=DB::table('restaurant_tables')->where('id',$id)->lockForUpdate()->first();abort_unless($table,404);
    $visits=DB::table('service_sessions')->where('table_id',$id)->where('status','open')->get();
    foreach($visits as $v){
       abort_if(DB::table('bills')->where('service_session_id',$v->id)->where('status','open')->where('total','>',0)->exists(),409,'Table has an unpaid balance.');
       abort_if(DB::table('orders')->where('service_session_id',$v->id)->whereIn('status',['pending_confirmation','awaiting_payment','accepted'])->exists(),409,'Table has active orders.');
       DB::table('service_sessions')->where('id',$v->id)->update(['status'=>'closed','closed_at'=>now(),'updated_at'=>now()]);
    }
    DB::table('restaurant_tables')->where('id',$id)->update(['status'=>'available','updated_at'=>now()]);
    OrderService::audit(auth()->id(),'table_closed','restaurant_tables',$id);
   });
   return back()->with('success','Table closed and ready for another visit.');
 }
}
