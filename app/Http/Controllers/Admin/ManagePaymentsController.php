<?php

namespace App\Http\Controllers\Admin;

use App\Currency;
use App\Helper\Reply;
use App\Http\Requests\Payments\ImportPayment;
use App\Http\Requests\Payments\StorePayment;
use App\Http\Requests\Payments\UpdatePayments;
use App\Invoice;
use App\ModuleSetting;
use App\Payment;
use App\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Facades\Datatables;

class ManagePaymentsController extends AdminBaseController
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
        return view('admin.payments.index', $this->data);
    }

    public function data() {
        $payments = Payment::leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->select('payments.id', 'payments.amount', 'currencies.currency_symbol', 'currencies.currency_code', 'payments.status', 'payments.paid_on')
            ->orderBy('payments.id', 'desc')
            ->get();

        return Datatables::of($payments)
            ->addColumn('action', function ($row) {
                return '<a href="' . route("admin.payments.edit", $row->id) . '" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a>
                        &nbsp;&nbsp;<a href="javascript:;" data-toggle="tooltip" data-original-title="Delete" data-payment-id="' . $row->id . '" class="btn btn-danger btn-circle sa-params"><i class="fa fa-times"></i></a>';
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
        $this->projects = Project::all();
        $this->currencies = Currency::all();
        return view('admin.payments.create', $this->data);
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

        return Reply::redirect(route('admin.payments.index'), __('messages.paymentSuccess'));
    }

    public function destroy($id) {
        Payment::destroy($id);
        return Reply::success(__('messages.paymentDeleted'));
    }

    public function edit($id){
        $this->projects = Project::all();
        $this->currencies = Currency::all();
        $this->payment = Payment::findOrFail($id);
        return view('admin.payments.edit', $this->data);
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

        return Reply::redirect(route('admin.payments.index'), __('messages.paymentSuccess'));
    }

    public function payInvoice($invoiceId){
        $this->invoice = Invoice::findOrFail($invoiceId);

        if($this->invoice->status == 'paid'){
            return "Invoice already paid";
        }

        return view('admin.payments.pay-invoice', $this->data);
    }

    public function importExcel(ImportPayment $request){
        if($request->hasFile('import_file')){
            $path = $request->file('import_file')->getRealPath();
            $data = Excel::load($path)->get();

            if($data->count()){

                foreach ($data as $key => $value) {

                    if($request->currency_character){
                        $amount = substr($value->amount, 1);
                    }
                    else{
                        $amount = substr($value->amount, 0);
                    }

                    $amount = str_replace( ',', '', $amount );
                    $amount = str_replace( ' ', '', $amount );

                    $arr[] = [
                        'paid_on' => Carbon::parse($value->date)->format('Y-m-d'),
                        'amount' => $amount,
                        'currency_id' => $this->global->currency_id,
                        'status' => 'complete'
                    ];
                }

                if(!empty($arr)){
                    DB::table('payments')->insert($arr);
                }
            }
        }

        return Reply::redirect(route('admin.payments.index'), __('messages.importSuccess'));
    }

    public function downloadSample(){
        return response()->download(public_path().'/payment-sample.csv');
    }

}
