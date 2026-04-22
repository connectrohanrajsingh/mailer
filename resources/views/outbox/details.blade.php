@extends('layout.base')
@section('title', 'Sent Email Details')

@section('content')
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="container-fluid">

            <div class="app-card shadow-sm mb-4">
                <div class="app-card-body p-4">

                    {{-- SUBJECT --}}
                    <h3 class="mb-3">{{ $email->subject }}</h3>

                    {{-- FROM --}}
                    <div class="mb-2">
                        <strong>From:</strong>
                        {{ $email->from_name }} ({{ $email->from_email }})
                    </div>

                    {{-- TO --}}
                    <div class="mb-2">
                        <strong>To:</strong><br>
                        @foreach ($email->to_emails ?? [] as $mail)
                            <span class="badge bg-danger text-white">{{ $mail }}</span>
                        @endforeach
                    </div>

                    {{-- CC --}}
                    @if(!empty($email->cc_emails))
                        <div class="mb-2">
                            <strong>CC:</strong><br>
                            @foreach ($email->cc_emails as $mail)
                                <span class="badge bg-info text-dark">{{ $mail }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- BCC --}}
                    @if(!empty($email->bcc_emails))
                        <div class="mb-2">
                            <strong>BCC:</strong><br>
                            @foreach ($email->bcc_emails as $mail)
                                <span class="badge bg-warning text-dark">{{ $mail }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- REPLY TO --}}
                    @if(!empty($email->reply_to))
                        <div class="mb-2">
                            <strong>Reply To:</strong><br>
                            @foreach ($email->reply_to as $mail)
                                <span class="badge bg-secondary">{{ $mail }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- STATUS --}}
                    <div class="mb-2">
                        <strong>Status:</strong>
                        <span class="badge bg-success">{{ ucfirst($email->status) }}</span>
                    </div>

                    {{-- SENT DATE --}}
                    <div class="mb-2">
                        <strong>Sent At:</strong>
                        {{ $email->sent_at?->format('d M Y h:i A') }}
                    </div>

                    {{-- REMARK --}}
                    @if($email->remark)
                        <div class="mb-2">
                            <strong>Remark:</strong> {{ $email->remark }}
                        </div>
                    @endif

                    <hr>

                    {{-- BODY --}}
                    <div class="mt-3">
                        <strong>Message:</strong>

                        <div class="mt-2 border p-3 rounded bg-light">
                            @if($email->body)
                                {!! $email->body !!}
                            @else
                                <em>No content available</em>
                            @endif
                        </div>
                    </div>

                    {{-- ATTACHMENTS --}}
                    @if($email->attachments->count())
                        <div class="mt-4">
                            <strong>Attachments:</strong>

                            <ul class="list-group mt-2">
                                @foreach($email->attachments as $file)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">

                                        <div>
                                            📎 {{ $file->name }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $file->mime_type }} | {{ number_format($file->size / 1024, 2) }} KB
                                            </small>
                                        </div>

                                        <div>
                                            <a href="{{ $file->getDownloadUrl() }}" class="btn btn-sm btn-success text-white">Download</a>
                                        </div>

                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- BACK --}}
                    <div class="mt-4">
                        <a href="{{ route('outbox.index') }}" class="btn btn-secondary">
                            Back
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection