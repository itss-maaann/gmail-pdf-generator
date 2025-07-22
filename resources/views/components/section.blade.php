@props(['title', 'note' => ''])

<div class="form-section">
    <div class="section-title">{{ $title }}</div>
    @if($note)
        <p class="note">{{ $note }}</p>
    @endif
    {{ $slot }}
</div>

