@extends('layout.base')
@section('title', 'Inbox')

@section('content')
    <div class="app-content">
        <div class="ml-page">

            <div class="ml-page-head">
                <div>
                    <h1 class="ml-page-title"><i class="fa-solid fa-inbox" style="color:var(--ml-accent)"></i> Inbox</h1>
                    <div class="ml-page-sub">{{ $emails->total() }} messages</div>
                </div>

                <div class="ml-toolbar">
                    <a href="{{ route('inbox.index') }}" class="ml-chip-btn {{ !request('search_value') ? 'active' : '' }}">
                        <i class="fa-solid fa-inbox"></i> All
                    </a>
                    <button type="button" class="ml-chip-btn" data-bs-toggle="collapse" data-bs-target="#inboxFilter" aria-expanded="false">
                        <i class="fa-solid fa-sliders"></i> Filter
                    </button>
                    <a href="{{ route('inbox.index') }}" class="ml-chip-btn" title="Refresh">
                        <i class="fa-solid fa-rotate"></i>
                    </a>
                </div>
            </div>

            <div class="collapse {{ request('search_value') ? 'show' : '' }}" id="inboxFilter">
                <form class="ml-filter-panel row g-2 align-items-end" action="{{ route('inbox.filter') }}" method="POST">
                    @csrf
                    <div class="col-12 col-md-5">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" name="search_value" class="form-control" placeholder="Value to search..." value="{{ request('search_value') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Field</label>
                        <select name="fetch_option" class="form-select">
                            @foreach ($filterOptions as $filterOption)
                                <option value="{{ $filterOption['value'] }}" @selected($filterOption['value'] == request('fetch_option'))>{{ $filterOption['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Match</label>
                        <select name="fetch_criteria" class="form-select">
                            @foreach ($filterCondition as $condition)
                                <option value="{{ $condition['value'] }}" @selected($condition['value'] == request('fetch_criteria'))>{{ $condition['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Go</button>
                    </div>
                </form>
            </div>

            <div class="ml-list-card mb-3">
                @forelse ($emails as $email)
                    @php
                        $snippetSource = $email->body?->body_text ?: strip_tags($email->body?->body_html ?? '');
                        $snippet = trim(preg_replace('/\s+/', ' ', (string) $snippetSource));
                        $displayDate = $email->date ?: $email->created_at;
                    @endphp
                    <a class="ml-row {{ $email->seen ? '' : 'is-unread' }}" href="{{ route('inbox.show', $email->id) }}">
                        @unless ($email->seen)
                            <span class="ml-unread-dot" title="Unread"></span>
                        @endunless

                        @include('partials.avatar', ['name' => $email->sender_name, 'email' => $email->sender_email])

                        <div class="ml-row-main">
                            <div class="ml-row-top">
                                <span class="ml-row-sender">{{ $email->sender_name ?: $email->sender_email }}</span>
                                <span class="ml-row-email">{{ $email->sender_email }}</span>
                            </div>
                            <div class="ml-row-subject-snippet">
                                <strong>{{ Str::limit($email->subject ?? '(no subject)', 70) }}</strong>
                                @if ($snippet)
                                    &mdash; {{ Str::limit($snippet, 90) }}
                                @endif
                            </div>
                        </div>

                        <div class="ml-row-side">
                            @if ($email->have_attachments)
                                <i class="fa-solid fa-paperclip ml-clip" title="Has attachments"></i>
                            @endif
                            @if ($email->flagged)
                                <i class="fa-solid fa-star" style="color:var(--ml-star)" title="Flagged"></i>
                            @endif
                            <span class="ml-row-date">
                                {{ $displayDate->isToday() ? $displayDate->format('H:i') : $displayDate->format('d M Y') }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="ml-empty">
                        <i class="fa-regular fa-envelope-open"></i>
                        Your inbox is empty
                        <div class="small mt-1">New mail will appear here once synced.</div>
                    </div>
                @endforelse
            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="ml-list-meta">
                    @if ($emails->count())
                        Showing <strong>{{ $emails->firstItem() }}</strong>&ndash;<strong>{{ $emails->lastItem() }}</strong> of <strong>{{ $emails->total() }}</strong>
                    @else
                        No messages
                    @endif
                </div>

                <nav class="app-pagination">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item {{ $emails->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $emails->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a>
                        </li>

                        @if ($emails->currentPage() > 3)
                            <li class="page-item"><a class="page-link" href="{{ $emails->url(1) }}">1</a></li>
                            @if ($emails->currentPage() > 4)
                                <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                            @endif
                        @endif

                        @for ($i = max(1, $emails->currentPage() - 2); $i <= min($emails->lastPage(), $emails->currentPage() + 2); $i++)
                            <li class="page-item {{ $emails->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $emails->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if ($emails->currentPage() < $emails->lastPage() - 2)
                            @if ($emails->currentPage() < $emails->lastPage() - 3)
                                <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                            @endif
                            <li class="page-item"><a class="page-link" href="{{ $emails->url($emails->lastPage()) }}">{{ $emails->lastPage() }}</a></li>
                        @endif

                        <li class="page-item {{ !$emails->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $emails->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>

        </div>
    </div>
@endsection

@push('after-scripts')
    @include('partials/sweetalert')
@endpush
