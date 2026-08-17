<?php

namespace App\Livewire\Settings;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tag settings')]
class Tags extends Component
{
    public string $name = '';

    public ?int $editingTagId = null;

    public string $editingName = '';

    #[Computed]
    public function tags(): Collection
    {
        return $this->user()->tags()->withCount(['collectionItems', 'wishlistItems'])->get();
    }

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:40', Rule::unique(Tag::class)->where('user_id', $this->user()->id)],
        ]);

        $this->user()->tags()->create($validated);
        $this->reset('name');
        unset($this->tags);

        $this->dispatch('tag-created');
    }

    public function edit(int $tagId): void
    {
        $tag = $this->findTag($tagId);

        $this->editingTagId = $tag->id;
        $this->editingName = $tag->name;
        $this->resetValidation();
    }

    public function update(): void
    {
        $tag = $this->findTag($this->editingTagId);

        $validated = $this->validate([
            'editingName' => ['required', 'string', 'max:40', Rule::unique(Tag::class, 'name')->where('user_id', $this->user()->id)->ignore($tag)],
        ]);

        $tag->update(['name' => $validated['editingName']]);
        $this->cancelEditing();
        unset($this->tags);
    }

    public function delete(int $tagId): void
    {
        $this->findTag($tagId)->delete();
        $this->cancelEditing();
        unset($this->tags);
    }

    public function cancelEditing(): void
    {
        $this->reset(['editingTagId', 'editingName']);
        $this->resetValidation('editingName');
    }

    public function render(): View
    {
        return view('livewire.settings.tags');
    }

    private function findTag(?int $tagId): Tag
    {
        return Tag::query()
            ->whereBelongsTo($this->user())
            ->findOrFail($tagId);
    }

    private function user(): User
    {
        $user = Auth::user();
        assert($user instanceof User);

        return $user;
    }
}
