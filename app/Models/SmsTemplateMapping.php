<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps an internal notification `type` (e.g. 'bill_due') to the SmsTemplate `title`
 * that should be used for it. Lets an admin re-point which template gets used for a
 * given notification without needing a code change/deploy.
 */
class SmsTemplateMapping extends Model
{
    protected $fillable = ['type', 'title', 'label'];

    /**
     * Get the SmsTemplate this type currently points to (if any, and if active).
     */
    public function template()
    {
        return $this->belongsTo(SmsTemplate::class, 'title', 'title');
    }
}
