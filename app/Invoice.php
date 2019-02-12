<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Invoice extends Model
{
    use Notifiable;

    protected $dates = ['issue_date', 'due_date'];

    public function project(){
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function items(){
        return $this->hasMany(InvoiceItems::class, 'invoice_id');
    }

    public function currency() {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public static function clientInvoices($clientId) {
        return Invoice::join('projects', 'projects.id', '=', 'invoices.project_id')
            ->select('projects.project_name', 'invoices.*')
            ->where('projects.client_id', $clientId)
            ->get();
    }
}
