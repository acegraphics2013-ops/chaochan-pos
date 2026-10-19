<?php
namespace App\Support;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class Permissions {
 public const MODULES=[
  'pos'=>'Point of Sale',
  'orders'=>'Live Orders',
  'shifts'=>'Cashier Shift',
  'kitchen'=>'Kitchen Display',
  'tables'=>'Tables & QR',
  'menu'=>'Menu Management',
  'sales'=>'Sales Monitoring',
  'expenses'=>'Expenses',
  'inventory'=>'Inventory',
 ];
 public const CONFIGURABLE_ROLES=['manager','cashier','kitchen'];
 private static ?array $rows=null;

 public static function defaults():array {
  return [
   'admin'=>array_keys(self::MODULES),
   'manager'=>['pos','orders','shifts','kitchen','tables','menu','sales','expenses','inventory'],
   'cashier'=>['pos','orders','shifts'],
   'kitchen'=>['kitchen'],
  ];
 }

 public static function matrix():array {
  $out=[];
  foreach(array_merge(['admin'],self::CONFIGURABLE_ROLES) as $role){
   foreach(array_keys(self::MODULES) as $module)$out[$role][$module]=in_array($module,self::defaults()[$role]??[],true);
  }
  foreach(self::rows() as $r){
   if(isset($out[$r->role][$r->module]))$out[$r->role][$r->module]=(bool)$r->enabled;
  }
  return $out;
 }

 public static function allows(?string $role,string $module):bool {
  if($role===null||$role==='')return false;
  if($role==='admin')return true;
  foreach(self::rows() as $r){
   if($r->role===$role&&$r->module===$module)return (bool)$r->enabled;
  }
  return in_array($module,self::defaults()[$role]??[],true);
 }

 public static function flush():void {self::$rows=null;}

 private static function rows():array {
  if(self::$rows!==null)return self::$rows;
  try{
   if(!Schema::hasTable('role_permissions'))return self::$rows=[];
   self::$rows=DB::table('role_permissions')->get()->all();
  }catch(\Throwable $e){
   self::$rows=[];
  }
  return self::$rows;
 }
}
