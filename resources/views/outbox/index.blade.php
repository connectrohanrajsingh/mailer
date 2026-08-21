@extends('layout.base')
@section('title', 'Sent')

@section('content')
    <div class="app-content">
        <div class="ml-page">

            <div class="ml-page-head">
                <div>
                    <h1 class="ml-page-title"><i class="fa-solid fa-paper-plane" style="color:var(--ml-accent)"></i> Sent</h1>
                    <div class="ml-page-sub">{{ $emails->total() }} messages sent from this account</div>
                </div>

                <div class="ml-toolbar">
                    <a href="{{ route('outbox.index') }}" class="ml-chip-btn {{ !request('search_value') ? 'active' : '' }}">
                        <i class="fa-solid fa-paper-plane"></i> All
                    </a>
                    <button type="button" class="ml-chip-btn" data-bs-toggle="collapse" data-bs-target="#outboxFilter" aria-expanded="false">
                        <i class="fa-solid fa-sliders"></i> Filter
                    </button>
                    <a href="{{ route('compose.index') }}" class="ml-chip-btn" style="color:var(--ml-accent);border-color:var(--ml-accent)">
                        <i class="fa-solid fa-pen-to-square"></i> Compose
                    </a>
                </div>
            </div>

            <div class="collapse {{ request('search_value') ? 'show' : '' }}" id="outboxFilter">
                <form class="ml-filter-panel row g-2 align-items-end" action="{{ route('outbox.filter') }}" method="POST">
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
                        $displayDate = $email->sent_at ?: $email->created_at;
                    @endphp
                    <a class="ml-row" href="{{ route('outbox.show', $email->id) }}">
                        @include('partials.avatar', ['name' => $email->to_name, 'email' => $email->to_emails])

                        <div class="ml-row-main">
                            <div class="ml-row-top">
                                <span class="ml-row-sender">To: {{ $email->to_name ?: ($email->to_emails ?: 'Unknown') }}</span>
                                <span class="ml-status ml-status-{{ strtolower($email->status ?? 'queued') }}">{{ strtolower($email->status ?? 'queued') }}</span>
                            </div>
                            <div class="ml-row-subject-snippet">
                                <strong>{{ Str::limit($email->subject ?? '(no subject)', 70) }}</strong>
                            </div>
                        </div>

                        <div class="ml-row-side">
                            @if ($email->attachments_count)
                                <i class="fa-solid fa-paperclip ml-clip" title="Has attachments"></i>
                            @endif
                            <span class="ml-row-date">
                                {{ $displayDate->isToday() ? $displayDate->format('H:i') : $displayDate->format('d M Y') }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="ml-empty">
                        <i class="fa-regular fa-paper-plane"></i>
                        Nothing sent yet
                        <div class="small mt-1">Messages you compose will show up here.</div>
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
