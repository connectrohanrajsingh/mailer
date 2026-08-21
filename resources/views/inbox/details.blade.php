@extends('layout.base')
@section('title', 'Email Details')

@section('content')
    <div class="app-content">
        <div class="ml-page">

            <div class="ml-page-head">
                <a href="{{ route('inbox.index') }}" class="ml-chip-btn">
                    <i class="fa-solid fa-arrow-left"></i> Back to Inbox
                </a>

                <div class="ml-reader-actions">
                    @if ($email->in_reply_to)
                        <span class="ml-chip-btn" title="Part of a thread"><i class="fa-solid fa-comments"></i> Threaded</span>
                    @endif
                    <a href="{{ route('compose.index', $email->id) }}" class="ml-action-btn ml-action-btn-primary">
                        <i class="fa-solid fa-reply"></i> Reply
                    </a>
                </div>
            </div>

            <div class="ml-reader">

                <div class="ml-reader-head">
                    <h2 class="ml-reader-subject">{{ $email->subject ?? '(no subject)' }}</h2>

                    <div class="ml-reader-meta">
                        <div class="ml-from-line">
                            @include('partials.avatar', ['name' => $email->sender_name, 'email' => $email->sender_email, 'size' => 'lg'])
                            <div>
                                <div class="ml-from-name">
                                    {{ $email->sender_name ?: 'Unknown sender' }}
                                    &lt;<span class="ml-from-email">{{ $email->sender_email }}</span>&gt;
                                </div>
                                <div class="ml-to-line">
                                    to
                                    @forelse ($email->addresses->where('type', 'to') as $addr)
                                        <span class="ml-pill">{{ $addr->name ?: $addr->email }}</span>
                                    @empty
                                        me
                                    @endforelse
                                    @if ($email->addresses->where('type', 'cc')->count())
                                        &middot; cc
                                        @foreach ($email->addresses->where('type', 'cc') as $addr)
                                            <span class="ml-pill">{{ $addr->name ?: $addr->email }}</span>
                                        @endforeach
                                    @endif
                                    @if ($email->addresses->where('type', 'bcc')->count())
                                        &middot; bcc
                                        @foreach ($email->addresses->where('type', 'bcc') as $addr)
                                            <span class="ml-pill">{{ $addr->name ?: $addr->email }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="text-md-end">
                            <div class="ml-row-date" style="font-size:.85rem">
                                {{ $email->date?->format('d M Y, H:i') }}
                            </div>
                            <div class="mt-1">
                                @if ($email->seen)
                                    <span class="ml-flag ml-flag-seen"><i class="fa-solid fa-eye"></i> Seen</span>
                                @endif
                                @if ($email->answered)
                                    <span class="ml-flag ml-flag-answered"><i class="fa-solid fa-reply"></i> Answered</span>
                                @endif
                                @if ($email->flagged)
                                    <span class="ml-flag ml-flag-flagged"><i class="fa-solid fa-star"></i> Flagged</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ml-reader-body">
                    @if ($email->rendered_body)
                        <div class="ml-body-content">{!! $email->rendered_body !!}</div>
                    @elseif ($email->body?->body_text)
                        <pre class="ml-body-plain">{{ $email->body->body_text }}</pre>
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
                                <a class="ml-attachment" href="{{ $file->getUrl() }}" target="_blank" rel="noopener">
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
