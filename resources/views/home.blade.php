@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Gmail Email Simulator & PDF Generator</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @elseif(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <x-section title="Step 1: Authenticate Emails" note="Click each button to authenticate Gmail accounts in new tabs.">
        <x-auth-input type="from" />
        <x-auth-input type="to" />
    </x-section>

    <x-section title="Step 2: Simulate Conversation" note="Only proceeds if both emails are authenticated.">
        <form method="POST" action="{{ route('simulate.conversation') }}" onsubmit="return syncEmails()">
            @csrf
            <input type="email" name="from_email" required>
            <input type="email" name="to_email" required>
            <button type="submit" class="btn-submit">Simulate 35x2 Emails</button>
        </form>
    </x-section>

    <x-section title="Step 3: Generate PDF" note="Creates a large PDF (~100MB) from the conversation.">
        <form id="generatePdfForm">
            @csrf
            <label>From Email</label>
            <input type="email" name="from_email" id="pdf_from_email" required>

            <label>To Email</label>
            <input type="email" name="to_email" id="pdf_to_email" required>

            <button type="submit" class="btn-submit">Generate PDF</button>
        </form>

        <div id="pdf-status" style="margin-top: 20px;"></div>
    </x-section>
</div>
@endsection

@push('scripts')
<script>
    function authenticateEmail(type) {
        const email = document.getElementById(`${type}_email`).value;
        if (!email) return alert('Please enter an email address first.');
        window.open(`/auth/google?email=${encodeURIComponent(email)}`, '_blank', 'width=500,height=600');
    }

    function syncEmails() {
        const from = document.getElementById('from_email').value;
        const to = document.getElementById('to_email').value;
        if (!from || !to) return alert("Please fill both emails."), false;

        document.getElementById('form_from_email').value = from;
        document.getElementById('form_to_email').value = to;
        return true;
    }

    function checkAuth(email, el) {
        if (!email) return el.textContent = '❌';
        fetch(`/check-auth-status?email=${encodeURIComponent(email)}`)
            .then(res => res.json())
            .then(data => el.textContent = data.authenticated ? '✅' : '❌')
            .catch(() => el.textContent = '❌');
    }

    function setupStatus(inputId, statusId) {
        const input = document.getElementById(inputId);
        const status = document.getElementById(statusId);
        if (!input || !status) return;

        input.addEventListener('input', () => checkAuth(input.value, status));
        checkAuth(input.value, status);
    }

    document.addEventListener('DOMContentLoaded', () => {
        setupStatus('from_email', 'from_status');
        setupStatus('to_email', 'to_status');
    });

    document.getElementById('generatePdfForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const fromEmail = document.getElementById('pdf_from_email').value;
    const toEmail = document.getElementById('pdf_to_email').value;
    const statusDiv = document.getElementById('pdf-status');
    statusDiv.innerHTML = '📤 Starting PDF generation...';

    fetch("{{ route('generate.pdf') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ from_email: fromEmail, to_email: toEmail })
    })
    .then(res => res.json())
        .then(data => {
            if (data.job_id) {
                statusDiv.innerHTML = '🕒 PDF is being generated...';
                pollStatus(data.job_id);
            } else {
                statusDiv.innerHTML = '❌ Failed to initiate PDF job.';
            }
        })
        .catch(err => {
            statusDiv.innerHTML = '❌ Error initiating request.';
        });
    });

    function pollStatus(jobId) {
        const statusDiv = document.getElementById('pdf-status');
        const interval = setInterval(() => {
            fetch(`/check-pdf-status/${jobId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'completed') {
                        clearInterval(interval);
                        statusDiv.innerHTML = `✅ PDF ready: <a href="/download-pdf/${jobId}" target="_blank">Download PDF</a>`;
                    } else if (data.status === 'failed') {
                        clearInterval(interval);
                        statusDiv.innerHTML = `❌ PDF generation failed: ${data.error}`;
                    } else {
                        statusDiv.innerHTML = '🕒 Still generating...';
                    }
                })
                .catch(err => {
                    clearInterval(interval);
                    statusDiv.innerHTML = '❌ Error checking PDF status.';
                });
        }, 5000); // check every 5 seconds
    }
</script>
@endpush
