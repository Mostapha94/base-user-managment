<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollectionResource extends ResourceCollection
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => UserResource::collection($this->data),
            'links' => [
                'self' => url()->current(),
            ],
            'meta' => [
                'pagination' => [
                    'total' => $this->data->total(),
                    'per_page' => $this->data->perPage(),
                    'current_page' => $this->data->currentPage(),
                    'last_page' => $this->data->lastPage(),
                    'from' => $this->data->firstItem(),
                    'to' => $this->data->lastItem(),
                ],
            ],
        ];
    }
}
