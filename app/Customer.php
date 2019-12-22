<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        "id",
        "name",
        "address",
        "checked",
        "description",
        "interest",
        "date_of_birth",
        "email",
        "account",
        "credit_card_type",
        "credit_card_number",
        "credit_card_name",
        "credit_card_expiration_date",
    ];
}
