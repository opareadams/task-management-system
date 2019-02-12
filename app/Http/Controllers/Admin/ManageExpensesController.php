<?php

namespace App\Http\Controllers\Admin;

use App\EmployeeDetails;
use App\Expense;
use App\Helper\Reply;
use App\Http\Requests\Expenses\StoreExpense;
use App\ModuleSetting;
use App\Notifications\NewExpenseMember;
use App\Notifications\NewExpenseStatus;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Yajra\Datatables\Facades\Datatables;

class ManageExpensesController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.expenses');
        $this->pageIcon = 'ti-shopping-cart';

        if(!ModuleSetting::checkModule('expenses')){
            abort(403);
        }
    }

    public function index(){
        return view('admin.expenses.index', $this->data);
    }

    public function create(){
        $this->employees = EmployeeDetails::all();
        return view('admin.expenses.create', $this->data);
    }

    public function store(StoreExpense $request){
        $expense = new Expense();
        $expense->item_name = $request->item_name;
        $expense->purchase_date = Carbon::parse($request->purchase_date)->format('Y-m-d');
        $expense->purchase_from = $request->purchase_from;
        $expense->price = $request->price;
        $expense->currency_id = $this->global->currency_id;
        $expense->user_id = $request->user_id;

        if ($request->hasFile('bill')) {
            $expense->bill = $request->bill->hashName();
            $request->bill->store('expense-invoice');
        }

        $expense->status = 'approved';
        $expense->save();

        //send welcome email notification
        $user = User::findOrFail($expense->user_id);
        $user->notify(new NewExpenseMember($expense));

        return Reply::redirect(route('admin.expenses.index'), __('messages.expenseSuccess'));
    }

    public function data() {
        $payments = Expense::all();

        return Datatables::of($payments)
            ->addColumn('action', function ($row) {
                return '<a href="' . route("admin.expenses.edit", $row->id) . '" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a>
                        &nbsp;&nbsp;<a href="javascript:;" data-toggle="tooltip" data-original-title="Delete" data-expense-id="' . $row->id . '" class="btn btn-danger btn-circle sa-params"><i class="fa fa-times"></i></a>';
            })
            ->editColumn('price', function ($row) {
                return $row->currency->currency_symbol.$row->price;
            })
            ->editColumn('user_id', function ($row) {
                return '<a href="'.route('admin.employees.show', $row->user_id).'">'.ucwords($row->user->name).'</a>';
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'pending'){
                    return '<label class="label label-warning">'.strtoupper($row->status).'</label>';
                }
                else if($row->status == 'approved'){
                    return '<label class="label label-success">'.strtoupper($row->status).'</label>';
                }else{
                    return '<label class="label label-danger">'.strtoupper($row->status).'</label>';
                }
            })
            ->editColumn(
                'purchase_date',
                function ($row) {
                    if(!is_null($row->purchase_date)){
                        return $row->purchase_date->timezone($this->global->timezone)->format('d M, Y');
                    }
                }
            )
            ->rawColumns(['action', 'status', 'user_id'])
            ->removeColumn('currency_id')
            ->removeColumn('bill')
            ->removeColumn('purchase_from')
            ->removeColumn('updated_at')
            ->removeColumn('created_at')
            ->make(true);
    }

    public function edit($id) {
        $this->expense = Expense::findOrFail($id);
        $this->employees = EmployeeDetails::all();
        return view('admin.expenses.edit', $this->data);
    }

    public function update(StoreExpense $request, $id){
        $expense = Expense::findOrFail($id);
        $expense->item_name = $request->item_name;
        $expense->purchase_date = Carbon::parse($request->purchase_date)->format('Y-m-d');
        $expense->purchase_from = $request->purchase_from;
        $expense->price = $request->price;
        $expense->currency_id = $this->global->currency_id;
        $expense->user_id = $request->user_id;

        if ($request->hasFile('bill')) {
            File::delete('user-uploads/expense-invoice/'.$expense->bill);

            $expense->bill = $request->bill->hashName();
            $request->bill->store('user-uploads/expense-invoice');
        }

        $previousStatus = $expense->status;

        $expense->status = $request->status;
        $expense->save();

        //send welcome email notification
        $user = User::findOrFail($expense->user_id);
        $user->notify(new NewExpenseStatus($expense));

        return Reply::redirect(route('admin.expenses.index'), __('messages.expenseUpdateSuccess'));
    }

    public function destroy($id) {
        Expense::destroy($id);
        return Reply::success(__('messages.expenseDeleted'));
    }

}
