<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class
BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'authors' => $this->authors->pluck('name'),
            'publisher' => $this->publisher?->name,
            'year' => $this->published_date ? date('Y', strtotime($this->published_date)) : null,

            $this->mergeWhen($request->routeIs('books.show'), [
                'description' => $this->description,
                'edition' => $this->edition,
                'pages' => $this->pages,
                'format' => $this->format,
                'country' => $this->country,
                'isbn' => $this->isbn,
                'genres' => $this->genres->pluck('name'),
            ]),
        ];
    }
}
