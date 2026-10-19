<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
class UserController {
 public function index(){$users=User::orderBy('name')->get();return view('users.index',compact('users'));}
 public function store(Request $request){
  $data=$request->validate(['name'=>'required|string|max:100','username'=>'required|string|max:50|unique:users,username','email'=>'nullable|email|unique:users,email','password'=>'required|string|min:12','role'=>'required|in:admin,manager,cashier,kitchen','active'=>'nullable|boolean']);
  $data['active']=$request->boolean('active');User::create($data);return back()->with('success','Staff account created.');
 }
 public function update(Request $request,int $id){
  $user=User::findOrFail($id);
  $data=$request->validate([
   'name'=>'required|string|max:100',
   'username'=>['required','string','max:50',Rule::unique('users','username')->ignore($user->id)],
   'email'=>['nullable','email','max:150',Rule::unique('users','email')->ignore($user->id)],
   'role'=>'required|in:admin,manager,cashier,kitchen',
   'active'=>'nullable|boolean',
   'password'=>'nullable|string|min:12',
  ]);
  if($user->id===auth()->id()&&($data['role']!=='admin'||!$request->boolean('active')))return back()->withErrors(['role'=>'You cannot revoke your own administrator access.']);
  if(empty($data['password']))unset($data['password']);
  $data['active']=$request->boolean('active');
  $user->update($data);
  return back()->with('success','Account updated.');
 }
 public function destroy(int $id){
  $user=User::findOrFail($id);
  if($user->id===auth()->id())return back()->withErrors(['delete'=>'You cannot delete your own account.']);
  if($user->role==='admin'&&User::where('role','admin')->where('active',true)->count()<=1)return back()->withErrors(['delete'=>'At least one active administrator must remain.']);
  try{$user->delete();}
  catch(QueryException $e){return back()->withErrors(['delete'=>'This account has linked sales, shifts or other records. Disable it instead of deleting.']);}
  return back()->with('success','Staff account deleted.');
 }
}
