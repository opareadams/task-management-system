<?php

namespace App\Http\Controllers\Member;

use App\EmployeeDetails;
use App\Expense;
use App\Helper\Reply;
use App\ModuleSetting;
use App\Http\Requests\Expenses\StoreExpense;
use App\Notifications\NewExpenseAdmin;
use App\Notifications\NewExpenseStatus;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Yajra\Datatables\Facades\Datatables;

/**
 * Class MemberProjectsController
 * @package App\Http\Controllers\Member
 */
class MemberExpensesController extends MemberBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.expenses');
        $this->pageIcon = 'ti-shopping-cart';

        if(!ModuleSetting::checkModule('expenses')){
            abort(403);
        }
    }

    public function index()
    {
        return view('member.expenses.index', $this->data);
    }

    public function data() {
        $this->userDetail = auth()->user();

        $payments = Expense::where('user_id', $this->userDetail->id)
            ->get();

        return Datatables::of($payments)
            ->addColumn('action', function ($row) {
                $html = '';

                if ($row->status == 'pending')
                {
                    $html .= '<a href="' . route("member.expenses.edit", $row->id) . '" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;';
                }
                $html .= '<a href="javascript:;" data-toggle="tooltip" data-original-title="Delete" data-expense-id="' . $row->id . '" class="btn btn-danger btn-circle sa-params"><i class="fa fa-times"></i></a>';

                return $html;
            })
            ->editColumn('price', function ($row) {
                return $row->currency->currency_symbol.$row->price;
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
            ->removeColumn('user_id')
            ->make(true);
    }

    public function create(){
        return view('member.expenses.create', $this->data);
    }

    public function store(StoreExpense $request){
        $expense = new Expense();
        $expense->item_name = $request->item_name;
        $expense->purchase_date = Carbon::parse($request->purchase_date)->format('Y-m-d');
        $expense->purchase_from = $request->purchase_from;
        $expense->price = $request->price;
        $expense->currency_id = $this->global->currency_id;
        $expense->user_id = auth()->user()->id;

        if ($request->hasFile('bill')) {
            $expense->bill = $request->bill->hashName();
            $request->bill->store('expense-invoice');
        }

        $expense->status = 'pending';
        $expense->save();

        Notification::send(User::allAdmins(), new NewExpenseAdmin($expense));

        return Reply::redirect(route('member.expenses.index'), __('messages.expenseSuccess'));
    }

    public function edit($id) {
        $this->expense = Expense::findOrFail($id);

        if($this->expense->status != 'pending')
        {
            abort(403);
        }

        return view('member.expenses.edit', $this->data);
    }

    public function update(StoreExpense $request, $id){
        $expense = Expense::findOrFail($id);

        if($expense->status != 'pending')
        {
            return Reply::error(__('messages.unAuthorisedUser'));
        }

        $expense->item_name = $request->item_name;
        $expense->purchase_date = Carbon::parse($request->purchase_date)->format('Y-m-d');
        $expense->purchase_from = $request->purchase_from;
        $expense->price = $request->price;
        $expense->currency_id = $this->global->currency_id;

        if ($request->hasFile('bill')) {
            File::delete('user-uploads/expense-invoice/'.$expense->bill);

            $expense->bill = $request->bill->hashName();
            $request->bill->store('user-uploads/expense-invoice');
        }

        $expense->save();

        return Reply::redirect(route('member.expenses.index'), __('messages.expenseUpdateSuccess'));
    }

    public function destroy($id) {
        Expense::destroy($id);
        return Reply::success(__('messages.expenseDeleted'));
    }

}
