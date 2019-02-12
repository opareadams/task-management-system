<?php

namespace App\Http\Controllers\Member;

use App\Currency;
use App\Estimate;
use App\Helper\Reply;
use App\Http\Requests\Invoices\StoreInvoice;
use App\Invoice;
use App\InvoiceItems;
use App\InvoiceSetting;
use App\ModuleSetting;
use App\Notifications\NewInvoice;
use App\Project;
use App\Setting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;

class MemberAllInvoicesController extends MemberBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.invoices');
        $this->pageIcon = 'ti-receipt';

        if(!ModuleSetting::checkModule('invoices')){
            abort(403);
        }
    }

    public function index() {
        if(!$this->user->can('view_invoices')){
            abort(403);
        }
        return view('member.invoices.index', $this->data);
    }

    public function data() {
        $invoices = Invoice::join('projects', 'projects.id', '=', 'invoices.project_id')
            ->join('currencies', 'currencies.id', '=', 'invoices.currency_id')
            ->select('invoices.id', 'invoices.project_id', 'invoices.invoice_number', 'projects.project_name', 'invoices.total', 'currencies.currency_symbol', 'currencies.currency_code', 'invoices.status', 'invoices.issue_date')
            ->orderBy('invoices.id', 'desc')
            ->get();

        return Datatables::of($invoices)
            ->addColumn('action', function ($row) {
                $action = '<div class="btn-group m-r-10">
                <button aria-expanded="false" data-toggle="dropdown" class="btn btn-info btn-outline  dropdown-toggle waves-effect waves-light" type="button">Action <span class="caret"></span></button>
                <ul role="menu" class="dropdown-menu">';

                if($this->user->can('view_invoices')){
                    $action.= '<li><a href="' . route("member.all-invoices.download", $row->id) . '"><i class="fa fa-download"></i> Download</a></li>';
                }

                if($row->status == 'unpaid' && $this->user->can('edit_invoices'))
                {
                    $action .= '<li><a href="' . route("member.all-invoices.edit", $row->id) . '"><i class="fa fa-pencil"></i> Edit</a></li>';
                }

                if($row->status == 'unpaid'){
                    $action.= '<li><a href="'.route("member.payments.payInvoice", [$row->id]).'" data-toggle="tooltip" ><i class="fa fa-plus"></i> '.__('modules.payments.addPayment').'</a></li>';
                }

                if($this->user->can('delete_invoices')){
                    $action.= '<li><a href="javascript:;" data-toggle="tooltip"  data-invoice-id="' . $row->id . '" class="sa-params"><i class="fa fa-times"></i> Delete</a></li>';
                }
                $action.= '</ul>
              </div>
              ';

                return $action;
            })
            ->editColumn('project_name', function ($row) {
                return '<a href="' . route('member.projects.show', $row->project_id) . '">' . ucfirst($row->project_name) . '</a>';
            })
            ->editColumn('invoice_number', function ($row) {
                return '<a href="' . route('member.all-invoices.show', $row->id) . '">' . ucfirst($row->invoice_number) . '</a>';
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'unpaid'){
                    return '<label class="label label-danger">'.strtoupper($row->status).'</label>';
                }else{
                    return '<label class="label label-success">'.strtoupper($row->status).'</label>';
                }
            })
            ->editColumn('total', function ($row) {
                return $row->currency_symbol . $row->total. ' ('.$row->currency_code.')';
            })
            ->editColumn(
                'issue_date',
                function ($row) {
                    return $row->issue_date->timezone($this->global->timezone)->format('d F, Y');
                }
            )
            ->rawColumns(['project_name', 'action', 'status', 'invoice_number'])
            ->removeColumn('currency_symbol')
            ->removeColumn('currency_code')
            ->removeColumn('project_id')
            ->make(true);
    }

    public function download($id) {
//        header('Content-type: application/pdf');

        $this->invoice = Invoice::findOrFail($id);
        $this->discount = InvoiceItems::where('type', 'discount')
                            ->where('invoice_id', $this->invoice->id)
                            ->sum('amount');
        $this->taxes = InvoiceItems::where('type', 'tax')
                            ->where('invoice_id', $this->invoice->id)
                            ->get();

//        return $this->invoice->project->client->client[0]->address;
        $this->settings = Setting::findOrFail(1);
        $this->invoiceSetting = InvoiceSetting::first();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('invoices.'.$this->invoiceSetting->template, $this->data);
        $filename = $this->invoice->invoice_number;
//        return $pdf->stream();
        return $pdf->download($filename . '.pdf');
    }

    public function destroy($id) {
        Invoice::destroy($id);
        return Reply::success(__('messages.invoiceDeleted'));
    }

    public function create() {
        if(!$this->user->can('add_invoices')){
            abort(403);
        }

        $this->projects = Project::all();
        $this->currencies = Currency::all();
        $this->lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $this->invoiceSetting = InvoiceSetting::first();
        return view('member.invoices.create', $this->data);
    }

    public function store(StoreInvoice $request)
    {
        $items = $request->input('item_name');
        $cost_per_item = $request->input('cost_per_item');
        $quantity = $request->input('quantity');
        $amount = $request->input('amount');
        $type = $request->input('type');

        if (trim($items[0]) == '' || trim($items[0]) == '' || trim($cost_per_item[0]) == '') {
            return Reply::error(__('messages.addItem'));
        }

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && (intval($qty) < 1)) {
                return Reply::error(__('messages.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('messages.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('messages.amountNumber'));
            }
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('messages.itemBlank'));
            }
        }


        $invoice = new Invoice();
        $invoice->project_id = $request->project_id;
        $invoice->invoice_number = $request->invoice_number;
        $invoice->issue_date = Carbon::parse($request->issue_date)->format('Y-m-d');
        $invoice->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $invoice->sub_total = $request->sub_total;
        $invoice->total = $request->total;
        $invoice->currency_id = $request->currency_id;
        $invoice->recurring = $request->recurring_payment;
        $invoice->billing_frequency = $request->recurring_payment == 'yes' ? $request->billing_frequency : null;
        $invoice->billing_interval = $request->recurring_payment == 'yes' ? $request->billing_interval : null;
        $invoice->billing_cycle = $request->recurring_payment == 'yes' ? $request->billing_cycle : null;
        $invoice->save();

        // Notify client
        $notifyUser = User::findOrFail($invoice->project->client_id);
        $notifyUser->notify(new NewInvoice($invoice));

        foreach ($items as $key => $item):
            if(!is_null($item)){
                InvoiceItems::create(['invoice_id' => $invoice->id, 'item_name' => $item, 'type' => $type[$key], 'quantity' => $quantity[$key], 'unit_price' => $cost_per_item[$key], 'amount' => $amount[$key]]);
            }
        endforeach;

        //log search
        $this->logSearchEntry($invoice->id, 'Invoice '.$invoice->invoice_number, 'admin.all-invoices.show');

        return Reply::redirect(route('member.all-invoices.index'), __('messages.invoiceCreated'));

    }

    public function edit($id) {
        if(!$this->user->can('edit_invoices')){
            abort(403);
        }

        $this->invoice = Invoice::findOrFail($id);
        $this->projects = Project::all();
        $this->currencies = Currency::all();

        if($this->invoice->status == 'paid')
        {
            abort(403);
        }

        return view('member.invoices.edit', $this->data);
    }

    public function update(StoreInvoice $request, $id)
    {
        $items = $request->input('item_name');
        $cost_per_item = $request->input('cost_per_item');
        $quantity = $request->input('quantity');
        $amount = $request->input('amount');
        $type = $request->input('type');

        if (trim($items[0]) == '' || trim($items[0]) == '' || trim($cost_per_item[0]) == '') {
            return Reply::error(__('messages.addItem'));
        }

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && $qty < 1) {
                return Reply::error(__('messages.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('messages.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('messages.amountNumber'));
            }
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('messages.itemBlank'));
            }
        }


        $invoice = Invoice::findOrFail($id);

        if($invoice->status == 'paid')
        {
            return Reply::error(__('messages.invalidRequest'));
        }

        $invoice->project_id = $request->project_id;
        $invoice->invoice_number = $request->invoice_number;
        $invoice->issue_date = Carbon::parse($request->issue_date)->format('Y-m-d');
        $invoice->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $invoice->sub_total = $request->sub_total;
        $invoice->total = $request->total;
        $invoice->currency_id = $request->currency_id;
        $invoice->status = $request->status;
        $invoice->recurring = $request->recurring_payment;
        $invoice->billing_frequency = $request->recurring_payment == 'yes' ? $request->billing_frequency : null;
        $invoice->billing_interval = $request->recurring_payment == 'yes' ? $request->billing_interval : null;
        $invoice->billing_cycle = $request->recurring_payment == 'yes' ? $request->billing_cycle : null;
        $invoice->save();

        // Notify client
        $notifyUser = User::findOrFail($invoice->project->client_id);
        $notifyUser->notify(new NewInvoice($invoice));

        // delete and create new
        InvoiceItems::where('invoice_id', $invoice->id)->delete();

        foreach ($items as $key => $item):
            InvoiceItems::create(['invoice_id' => $invoice->id, 'item_name' => $item, 'type' => $type[$key], 'quantity' => $quantity[$key], 'unit_price' => $cost_per_item[$key], 'amount' => $amount[$key]]);
        endforeach;

        return Reply::success(__('messages.invoiceUpdated'));

    }

    public function show($id){
        $this->invoice = Invoice::findOrFail($id);
        $this->discount = InvoiceItems::where('type', 'discount')
            ->where('invoice_id', $this->invoice->id)
            ->sum('amount');
        $this->taxes = InvoiceItems::where('type', 'tax')
            ->where('invoice_id', $this->invoice->id)
            ->get();

        $this->settings = Setting::findOrFail(1);

        return view('member.invoices.show', $this->data);
    }

    public function convertEstimate($id) {
        $this->invoice = Estimate::findOrFail($id);
        $this->lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $this->invoiceSetting = InvoiceSetting::first();
        $this->projects = Project::all();
        $this->currencies = Currency::all();
        return view('member.invoices.convert_estimate', $this->data);
    }


}
