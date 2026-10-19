<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ExpenseController {
 public function index(){$expenses=DB::table('expenses')->join('users','users.id','=','expenses.user_id')->select('expenses.*','users.name as user_name')->orderByDesc('expenses.expense_date')->limit(250)->get();$total=$expenses->sum('amount');return view('expenses.index',compact('expenses','total'));}
 public function store(Request $request){$data=$request->validate(['category'=>'required|string|max:60','description'=>'required|string|max:255','amount'=>'required|numeric|gt:0|max:1000000','expense_date'=>'required|date']);DB::table('expenses')->insert($data+['user_id'=>auth()->id(),'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Expense recorded.');}
}
