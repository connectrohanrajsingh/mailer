@extends('layout.base')
@section('title', 'Compose')

@push('after-styles')
    <link href="{{ asset("assets/plugins/summernote/summernote-lite.min.css") }}" rel="stylesheet">
@endpush

@section('content')
    <div class="app-content">
        <div class="ml-page">

            <div class="ml-compose-card">
                <div class="ml-compose-head">
                    <h5 class="ml-compose-title">
                        <i class="fa-solid fa-pen-to-square" style="color:var(--ml-accent)"></i>
                        {{ $email ? 'Reply' : 'New Message' }}
                    </h5>
                    <a href="{{ route('inbox.index') }}" class="ml-icon-btn" title="Close" style="text-decoration:none">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>

                <div class="ml-compose-body">

                    @if ($email)
                        <div class="ml-reply-banner">
                            <i class="fa-solid fa-reply" style="color:var(--ml-accent)"></i>
                            <span>
                                Replying to <strong>{{ $email->sender_name ?: $email->sender_email }}</strong>
                                &mdash; &ldquo;{{ Str::limit($email->subject, 60) }}&rdquo;
                            </span>
                        </div>
                    @endif

                    <form id="emailForm" action="{{ route('compose.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="reply_to" value="{{ old('reply_to', $email->reply_to ?? null) }}">

                        <div class="ml-field">
                            <label for="to">To</label>
                            <input type="text" name="to_emails" id="to" placeholder="recipient@example.com, another@example.com"
                                   value="{{ old('to_emails', $email->sender_email ?? null) }}" required>
                            <button type="button" class="ml-field-toggle" id="toggleCcBcc">Cc / Bcc</button>
                        </div>

                        <div id="ccBccWrap" class="{{ old('cc_emails') || old('bcc_emails') ? '' : 'd-none' }}">
                            <div class="ml-field">
                                <label for="cc">Cc</label>
                                <input type="text" name="cc_emails" id="cc" placeholder="cc@example.com" value="{{ old('cc_emails') }}">
                            </div>
                            <div class="ml-field">
                                <label for="bcc">Bcc</label>
                                <input type="text" name="bcc_emails" id="bcc" placeholder="bcc@example.com" value="{{ old('bcc_emails') }}">
                            </div>
                        </div>

                        <div class="ml-field">
                            <label for="subject">Subject</label>
                            <input type="text" name="subject" id="subject" placeholder="Subject"
                                   value="{{ old('subject', $email?->subject ? "Re: {$email->subject}" : null) }}" required>
                        </div>

                        <div class="ml-field d-none">
                            <label for="to_name">Name</label>
                            <input type="text" name="to_name" id="to_name" value="{{ old('to_name', $email->sender_name ?? null) }}">
                        </div>

                        <div class="ml-editor-shell mt-3">
                            <label class="form-label small text-muted mb-2">Message</label>
                            <textarea name="body" id="message" required>{{ old('body') }}</textarea>
                        </div>

                        <div class="ml-attach-zone">
                            <label class="form-label small text-muted mb-2">Attachments</label>
                            <input type="file" name="attachments[]" id="attachments" class="ml-attach-input" multiple>
                        </div>

                        <div class="ml-compose-foot">
                            <span class="ml-compose-hint">
                                <i class="fa-regular fa-clock me-1"></i> Queued and sent in the background via SMTP
                            </span>
                            <button type="submit" class="ml-send-btn">
                                Send <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('after-scripts')
    @include('partials/sweetalert')
    <script src="{{ asset("assets/plugins/summernote/summernote-lite.min.js") }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#message').summernote({
                placeholder: 'Write your email...',
                height: 240,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']]
                ]
            });

            var toggle = document.getElementById('toggleCcBcc');
            var wrap = document.getElementById('ccBccWrap');
            if (toggle && wrap) {
                toggle.addEventListener('click', function () {
                    wrap.classList.toggle('d-none');
                });
            }
        });
    </script>
@endpush
