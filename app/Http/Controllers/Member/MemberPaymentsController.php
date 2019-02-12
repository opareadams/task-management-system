<?php

namespace App\Http\Controllers\Member;

use App\Currency;
use App\Helper\Reply;
use App\Http\Requests\Payments\StorePayment;
use App\Http\Requests\Payments\UpdatePayments;
use App\Invoice;
use App\ModuleSetting;
use App\Payment;
use App\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;

class MemberPaymentsController extends MemberBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.payments');
        $this->pageIcon = 'fa fa-money';

        if(!ModuleSetting::checkModule('payments')){
            abort(403);
        }
    }

    public function index() {
        if(!$this->user->can('view_payments')){
            abort(403);
        }
        return view('member.payments.index', $this->data);
    }

    public function data() {
        $payments = Payment::leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->select('payments.id', 'payments.amount', 'currencies.currency_symbol', 'currencies.currency_code', 'payments.status', 'payments.paid_on')
            ->orderBy('payments.id', 'desc')
            ->get();

        return Datatables::of($payments)
            ->addColumn('action', function ($row) {
                $action = '';
                if($this->user->can('edit_payments')){
                    $action.= '<a href="' . route("member.payments.edit", $row->id) . '" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a>';
                }
                if($this->user->can('delete_payments')) {
                    $action .= '&nbsp;&nbsp;<a href="javascript:;" data-toggle="tooltip" data-original-title="Delete" data-payment-id="' . $row->id . '" class="btn btn-danger btn-circle sa-params"><i class="fa fa-times"></i></a>';
                }
                return $action;
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'pending'){
                    return '<label class="label label-warning">'.strtoupper($row->status).'</label>';
                }else{
                    return '<label class="label label-success">'.strtoupper($row->status).'</label>';
                }
            })
            ->editColumn('amount', function ($row) {
                return $row->currency_symbol . $row->amount. ' ('.$row->currency_code.')';
            })
            ->editColumn(
                'paid_on',
                function ($row) {
                    if(!is_null($row->paid_on)){
                        return $row->paid_on->timezone($this->global->timezone)->format('d M, Y');
                    }
                }
            )
            ->rawColumns(['action', 'status'])
            ->removeColumn('invoice_id')
            ->removeColumn('currency_symbol')
            ->removeColumn('currency_code')
            ->make(true);
    }

    public function create(){
        if(!$this->user->can('add_payments')){
            abort(403);
        }
        $this->projects = Project::all();
        $this->currencies = Currency::all();
        return view('member.payments.create', $this->data);
    }

    public function store(StorePayment $request){
        $payment = new Payment();
        if($request->project_id != ''){
            $payment->project_id = $request->project_id;
        }
        $payment->currency_id = $request->currency_id;
        $payment->amount = $request->amount;
        $payment->gateway = $request->gateway;
        $payment->transaction_id = $request->transaction_id;
        $payment->paid_on = Carbon::parse($request->paid_on)->format('Y-m-d');
        $payment->status = 'complete';
        $payment->save();

        return Reply::redirect(route('member.payments.index'), __('messages.paymentSuccess'));
    }

    public function destroy($id) {
        Payment::destroy($id);
        return Reply::success(__('messages.paymentDeleted'));
    }

    public function edit($id){
        if(!$this->user->can('edit_payments')){
            abort(403);
        }
        $this->projects = Project::all();
        $this->currencies = Currency::all();
        $this->payment = Payment::findOrFail($id);
        return view('member.payments.edit', $this->data);
    }

    public function update(UpdatePayments $request, $id){
        $payment = Payment::findOrFail($id);
        if($request->project_id != ''){
            $payment->project_id = $request->project_id;
        }
        $payment->currency_id = $request->currency_id;
        $payment->amount = $request->amount;
        $payment->gateway = $request->gateway;
        $payment->transaction_id = $request->transaction_id;
        $payment->paid_on = Carbon::parse($request->paid_on)->format('Y-m-d');
        $payment->status = 'complete';
        $payment->save();

        return Reply::redirect(route('member.payments.index'), __('messages.paymentSuccess'));
    }


    public function payInvoice($invoiceId){
        $this->invoice = Invoice::findOrFail($invoiceId);

        if($this->invoice->status == 'paid'){
            return "Invoice already paid";
        }

        return view('member.payments.pay-invoice', $this->data);
    }

}
