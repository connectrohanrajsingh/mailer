@extends('layout.base')
@section('title', 'Inbox')

@push('after-styles')
    <link href="{{ asset("assets/plugins/summernote/summernote-lite.min.css") }}" rel="stylesheet">
@endpush

@section('content')
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="container-fluid">
            <div class="app-card shadow-sm mb-4">
                <div class="app-card-body p-3 p-lg-4">

                    <h4 class="mb-3">Compose Email</h4>

                    <form id="emailForm" action="{{ route('compose.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mt-3">
                            <label class="form-label">To</label>
                            <input type="text" name="to_emails" id="to" class="form-control" placeholder="comma separated emails" value="{{  old('to_emails', $email->sender_email ?? null) }}" required>
                        </div>

                        <div class="row g-2">
                            <div class="mt-3">
                                <label class="form-label">CC</label>
                                <input type="text" name="cc_emails" id="cc" class="form-control" value="{{ old('cc_emails') }}">
                            </div>

                            <div class="mt-3">
                                <label class="form-label">BCC</label>
                                <input type="text" name="bcc_emails" id="bcc" class="form-control" value="{{ old('bcc_emails') }}">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject', $email?->subject ? "Re: {$email->subject}" : null) }}">
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="to_name" id="to_name" class="form-control" value="{{ old('to_name', $email->sender_name ?? null) }}">
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Message</label>
                            <textarea name="body" id="message" class="form-control" style="min-height:180px;" required>{{ old('body') }}</textarea>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Attachments</label>
                            <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                        </div>

                        <div class="mt-3">
                            <input type="hidden" name="reply_to" id="reply_to" class="form-control" value="{{ old('reply_to', $email->reply_to ?? null) }}">
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn app-btn-primary">Send Mail</button>
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
                placeholder: 'Type your message here...',
                height: 200,
                placeholder: 'Write your email...',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']]
                ]
            });
        });
    </script>
@endpush