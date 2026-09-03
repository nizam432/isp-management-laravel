<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerSmsTemplate extends Model
{
    protected $fillable = ['mac_reseller_id', 'title', 'body', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    const VARIABLES = [
        '{name}'           => 'Customer Name',
        '{mobile}'         => 'Mobile Number',
        '{amount}'         => 'Bill Amount',
        '{date}'           => 'Date',
        '{month}'          => 'Month',
        '{package}'        => 'Package Name',
        '{company}'        => 'Company Name',
        '{pppoe_username}' => 'PPPoE Username',
        '{pppoe_password}' => 'PPPoE Password',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForReseller($query, $macResellerId)
    {
        return $query->where('mac_reseller_id', $macResellerId);
    }

    public function render(array $data = []): string
    {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }
        return $body;
    }
}
