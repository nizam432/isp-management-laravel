<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = ['title', 'body', 'is_active'];

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

    public function render(array $data = []): string
    {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }
        return $body;
    }
}
