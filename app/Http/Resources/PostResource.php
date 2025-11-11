<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'keywords' => $this->keywords,
            'description' => $this->description,
            'image' => $this->image,
            'body' => $this->body,
            'project_url' => $this->project_url,
            'video_url' => $this->video_url,
            'code_url' => $this->code_url,
            'is_active' => $this->is_active,

            // Creator & updater info
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),

            'updater' => $this->whenLoaded('updater', function () {
                return $this->updater
                    ? [
                        'id' => $this->updater->id,
                        'name' => $this->updater->name,
                        'email' => $this->updater->email,
                    ]
                    : null;
            }),

            // Categories
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                });
            }),

            // Comments
            'comments' => $this->whenLoaded('comments', function () {
                return $this->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'user_id' => $comment->user_id,
                        'body' => $comment->body,
                        'status' => $comment->status,
                        'created_at' => $comment->created_at->toDateTimeString(),
                    ];
                });
            }),

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
