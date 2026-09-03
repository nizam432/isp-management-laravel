<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps an internal notification `type` (e.g. 'bill_due') to the ResellerSmsTemplate
 * `title` that should be used for it, scoped to one reseller. Mirrors the admin-side
 * SmsTemplateMapping, but since each reseller's mapping/template pair must be looked
 * up together with mac_reseller_id (a compound key), `template` here is a plain
 * accessor rather than a standard belongsTo relation.
 */
class ResellerSmsTemplateMapping extends Model
{
    protected $fillable = ['mac_reseller_id', 'type', 'title', 'label'];

    public function scopeForReseller($query, $macResellerId)
    {
        return $query->where('mac_reseller_id', $macResellerId);
    }

    /** Lets blade views use $mapping->template->body just like the admin version. */
    public function getTemplateAttribute()
    {
        return ResellerSmsTemplate::where('mac_reseller_id', $this->mac_reseller_id)
            ->where('title', $this->title)
            ->first();
    }

    /**
     * Make sure the 5 standard notification types have a mapping row for this
     * reseller. Since resellers don't exist at migration time, this can't be
     * seeded once centrally — it's ensured lazily whenever the reseller opens
     * their Templates page.
     */
    public static function ensureDefaultsFor(int $macResellerId): void
    {
        $defaults = [
            ['type' => 'bill_due',        'title' => 'Bill Due Reminder',    'label' => 'Bill Due Reminder'],
            ['type' => 'payment_confirm', 'title' => 'Payment Confirmation', 'label' => 'Payment Confirmation'],
            ['type' => 'suspend',         'title' => 'Suspension Notice',    'label' => 'Suspension Notice'],
            ['type' => 'restore',         'title' => 'Restore Notice',       'label' => 'Restore Notice'],
            ['type' => 'welcome',         'title' => 'Welcome Message',      'label' => 'Welcome Message'],
        ];

        foreach ($defaults as $d) {
            static::firstOrCreate(
                ['mac_reseller_id' => $macResellerId, 'type' => $d['type']],
                ['title' => $d['title'], 'label' => $d['label']]
            );
        }
    }
}
