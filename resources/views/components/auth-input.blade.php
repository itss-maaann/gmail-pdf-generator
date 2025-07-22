@props(['type'])

@php
    $label = ucfirst($type) . ' Email';
@endphp

<label>{{ $label }}</label>
<div class="input-group">
    <input type="email" id="{{ $type }}_email" name="{{ $type }}_email" required>
    <span id="{{ $type }}_status" class="status-icon">❌</span>
</div>
<div class="btn-auth">
    <button type="button" onclick="authenticateEmail('{{ $type }}')">Authenticate {{ ucfirst($type) }}</button>
</div>
