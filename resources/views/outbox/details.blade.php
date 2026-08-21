@extends('layout.base')
@section('title', 'Sent Email Details')

@section('content')
    <div class="app-content">
        <div class="ml-page">

            <div class="ml-page-head">
                <a href="{{ route('outbox.index') }}" class="ml-chip-btn">
                    <i class="fa-solid fa-arrow-left"></i> Back to Sent
                </a>

                <div class="ml-reader-actions">
                    <span class="ml-status ml-status-{{ strtolower($email->status ?? 'queued') }}">{{ strtolower($email->status ?? 'queued') }}</span>
                    <a href="{{ route('compose.index') }}" class="ml-action-btn ml-action-btn-primary">
                        <i class="fa-solid fa-pen-to-square"></i> New Message
                    </a>
                </div>
            </div>

            <div class="ml-reader">

                <div class="ml-reader-head">
                    <h2 class="ml-reader-subject">{{ $email->subject ?? '(no subject)' }}</h2>

                    <div class="ml-reader-meta">
                        <div class="ml-from-line">
                            @include('partials.avatar', ['name' => $email->from_name, 'email' => $email->from_email, 'size' => 'lg'])
                            <div>
                                <div class="ml-from-name">
                                    {{ $email->from_name ?: 'Me' }}
                                    &lt;<span class="ml-from-email">{{ $email->from_email }}</span>&gt;
                                </div>
                                <div class="ml-to-line">
                                    to
                                    @forelse ($email->to_emails ?? [] as $mail)
                                        <span class="ml-pill">{{ $mail }}</span>
                                    @empty
                                        &mdash;
                                    @endforelse
                                    @if (!empty($email->cc_emails))
                                        &middot; cc
                                        @foreach ($email->cc_emails as $mail)
                                            <span class="ml-pill">{{ $mail }}</span>
                                        @endforeach
                                    @endif
                                    @if (!empty($email->bcc_emails))
                                        &middot; bcc
                                        @foreach ($email->bcc_emails as $mail)
                                            <span class="ml-pill">{{ $mail }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="text-md-end">
                            <div class="ml-row-date" style="font-size:.85rem">
                                {{ ($email->sent_at ?: $email->created_at)?->format('d M Y, H:i') }}
                            </div>
                            @if ($email->reply_to)
                                <div class="mt-1">
                                    <span class="ml-flag ml-flag-answered"><i class="fa-solid fa-reply"></i> Reply</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($email->remark && strtolower($email->status) === 'failed')
                        <div class="alert alert-danger mt-3 mb-0 py-2 px-3 small rounded-3">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $email->remark }}
                        </div>
                    @endif
                </div>

                <div class="ml-reader-body">
                    @if ($email->body)
                        <div class="ml-body-content">{!! $email->body !!}</div>
                    @else
                        <div class="ml-empty">
                            <i class="fa-regular fa-file-lines"></i>
                            No message content
                        </div>
                    @endif
                </div>

                @if ($email->attachments->count())
                    <div class="ml-attachments">
                        <div class="ml-attachments-title">
                            <i class="fa-solid fa-paperclip me-1"></i> {{ $email->attachments->count() }} Attachment{{ $email->attachments->count() > 1 ? 's' : '' }}
                        </div>
                        <div class="ml-attachment-grid">
                            @foreach ($email->attachments as $file)
                                <a class="ml-attachment" href="{{ $file->getDownloadUrl() }}">
                                    <span class="ml-file-icon"><i class="fa-solid fa-file"></i></span>
                                    <span>
                                        <span class="ml-file-name d-block">{{ $file->name }}</span>
                                        <span class="ml-file-meta">{{ number_format($file->size / 1024, 1) }} KB</span>
                                    </span>
                                    <i class="fa-solid fa-download ms-auto" style="color:var(--ml-muted)"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

        </div>
    </div>
@endsection

@push('after-scripts')
    @include('partials/sweetalert')
@endpush
