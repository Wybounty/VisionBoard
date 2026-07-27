<?php

namespace App\Http\Requests;

use App\Models\VisionBoard;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisionBoardBriefRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $visionBoard = $this->route('visionBoard');

        if (! $visionBoard instanceof VisionBoard) {
            return false;
        }

        return $this->user()?->visionBoards()->whereKey($visionBoard->getKey())->exists() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brief' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }
}
