namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BwsInvoiceItem extends Model
{
    protected $table = 'bws_invoice_items';

    protected $fillable = [
        'bws_invoice_id', 'item_name', 'description',
        'unit', 'quantity', 'rate', 'vat_percent',
        'from_date', 'to_date', 'total', 'sort_order',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(BwsInvoice::class, 'bws_invoice_id');
    }
}
