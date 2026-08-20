<?php

namespace App\Livewire\Forms;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CreateTagForm extends Form
{
    public string $name = '';

    public function create(User $user): Tag
    {
        $this->name = (string) Str::of($this->name)->squish();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:40', Rule::unique(Tag::class, 'name')->where('user_id', $user->id)],
        ]);

        $tag = $user->tags()->create($validated);
        assert($tag instanceof Tag);

        $this->reset();

        return $tag;
    }
}
