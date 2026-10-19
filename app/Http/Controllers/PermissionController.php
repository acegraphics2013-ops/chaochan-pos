<?php
namespace App\Http\Controllers;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PermissionController {
 public function index(){
  return view('settings.permissions',['modules'=>Permissions::MODULES,'roles'=>Permissions::CONFIGURABLE_ROLES,'matrix'=>Permissions::matrix()]);
 }
 public function update(Request $request){
  $request->validate(['permissions'=>'nullable|array']);
  $now=now();
  $rows=[];
  foreach(Permissions::CONFIGURABLE_ROLES as $role){
   foreach(array_keys(Permissions::MODULES) as $module){
    $rows[]=['role'=>$role,'module'=>$module,'enabled'=>$request->boolean("permissions.$role.$module"),'created_at'=>$now,'updated_at'=>$now];
   }
  }
  DB::transaction(fn()=>DB::table('role_permissions')->upsert($rows,['role','module'],['enabled','updated_at']));
  Permissions::flush();
  return back()->with('success','Module access updated.');
 }
}
