@extends('layout.base')
@section('title', 'Email Details')

@section('content')
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="container-fluid">

            <div class="col-auto mb-2">
                <div class="d-flex justify-content-end">
                    <h1 class="app-page-title mb-0  ">{{ $email->sender_email }}</h1>
                    <a href="{{ route("compose.index",[$email->id]) }}" class="mx-4 btn app-btn-secondary"><i class="fa fa-paper-plane"></i></a>
                </div>
            </div>

            <div class="app-card shadow-sm mb-4">
                <div class="app-card-body p-4">

                    {{-- SUBJECT --}}
                    <h3 class="mb-3">{{ $email->subject }}</h3>

                    {{-- SENDER --}}
                    <div class="mb-2">
                        <strong>From:</strong> {{ $email->sender_name }} ({{ $email->sender_email }})
                    </div>

                    {{-- ADDRESSES --}}
                    <div class="mb-2">
                        <strong>To / CC / BCC:</strong><br>
                        @foreach ($email->addresses as $addr)
                            <span class="badge bg-light text-dark">
                                {{ strtoupper($addr->type) }} :
                                {{ $addr->name }} ({{ $addr->email }})
                            </span>
                        @endforeach
                    </div>

                    {{-- DATES --}}
                    <div class="mb-2">
                        <strong>Email Date:</strong> {{ $email->date }}
                    </div>

                    <div class="mb-2">
                        <strong>Received:</strong> {{ $email->created_at }}
                    </div>

                    {{-- FLAGS --}}
                    <div class="mb-3">
                        @if($email->seen)
                            <span class="badge bg-success">Seen</span>
                        @endif
                        @if($email->answered)
                            <span class="badge bg-info">Answered</span>
                        @endif
                        @if($email->flagged)
                            <span class="badge bg-warning">Flagged</span>
                        @endif
                    </div>

                    <hr>

                    {{-- BODY --}}
                    <div class="mt-3">
                        <strong>Message:</strong>

                        <div class="mt-2 border p-3 rounded bg-light">

                            @if($email->rendered_body)
                                {!! $email->rendered_body !!}
                            @elseif($email->body?->body_text)
                                <pre>{{ $email->body->body_text }}</pre>
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

                                        <a href="{{ $file->getUrl() }}" target="_blank" class="btn btn-sm btn-primary text-white">Download</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- BACK --}}
                    <div class="mt-4">
                        <a href="{{ route('inbox.index') }}" class="btn btn-secondary">Back</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection