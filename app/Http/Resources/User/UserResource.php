<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'branch_id'     => $this->branch_id,
            'first_name'    => $this->first_name,
            'last_name'      => $this->last_name,
            'username'      => $this->username,
            'code'          => $this->code,
            'staff_code'    => $this->staff_code,
            'email'         => $this->email,
            'gender'        => $this->gender,
            'phone'         => $this->phone,
            'address'       => $this->address,
            'city'          => $this->city,
            'state'         => $this->state,
            'country'       => $this->country,
            'notes'         => $this->notes,
            'enable_2fa'    => $this->enable_2fa,
            'status'        => $this->status,
            'last_login_at' => $this->last_login_at,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
