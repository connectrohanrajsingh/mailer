@extends('layout.base')
@section('title', 'Inbox')

@section('content')
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="container-fluid">
            <div class="row g-3 mb-4 align-items-center justify-content-between">
                <div class="col-auto">
                    <h1 class="app-page-title mb-0">Inbox</h1>
                </div>
                <div class="col-auto">
                    <div class="page-utilities">
                        <div class="row g-2 justify-content-start justify-content-md-end align-items-center">
                            <div class="col-auto bg-dark">
                                <form class="table-search-form row gx-1 align-items-center" action="{{ route('inbox.filter') }}" method="POST">
                                    @csrf
                                    <div class="col-auto">
                                        <input type="text" name="search_value" id="search-orders" class="form-control search-orders" placeholder="Search" value="{{ request('search_value') }}">
                                    </div>

                                    <div class="col-auto">
                                        <select name="fetch_option" class="form-select w-auto">
                                            @foreach ($filterOptions as $filterOption)
                                                <option value="{{ $filterOption['value'] }}" @selected($filterOption['value'] == request('fetch_option'))>{{ $filterOption['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-auto">
                                        <select name="fetch_criteria" class="form-select w-auto">
                                            @foreach ($filterCondition as $condition)
                                                <option value="{{ $condition['value'] }}" @selected($condition['value'] == request('fetch_criteria'))>{{ $condition['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-auto">
                                        <button type="submit" class="btn app-btn-secondary">Search</button>
                                    </div>
                                    <div class="col-auto">
                                        <a type="submit" class="btn app-btn-secondary" href="{{ route('inbox.index') }}">
                                            <i class="fa fa-refresh"></i>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="inbox">
                <div class="app-card app-card-orders-table shadow-sm mb-5">
                    <div class="app-card-body">
                        <div class="table-responsive">
                            <table class="table app-table-hover table-striped mb-0 text-left">
                                <thead>
                                    <tr>
                                        <th class="cell">Sender Email</th>
                                        <th class="cell">Sender Name</th>
                                        <th class="cell">Subject</th>
                                        <th class="cell">Email Date</th>
                                        <th class="cell">Received Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($emails as $email)
                                        <tr>
                                            <td class="cell">
                                                {{ $email->sender_email }} 
                                                 @if($email->have_attachments) 📎 @endif
                                                </td>
                                            <td class="cell">{{ $email->sender_name }}</td>
                                            <td class="cell">{{ Str::limit($email->subject, 50) }}</td>
                                            <td class="cell">{{ $email->date }}</td>
                                            <td class="cell">{{ $email->created_at }}</td>
                                            <td class="cell text-center">
                                                <a href="{{ route('inbox.show', $email->id) }}" class="text-success">
                                                    <i class="fa fa-eye w-100"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center justify-content-between ">
                    <div class="col-auto">
                        <div class="text-dark small text-center">
                            @if ($emails->count())
                                Showing
                                <strong>{{ $emails->firstItem() }}</strong>–<strong>{{ $emails->lastItem() }}</strong>
                                of <strong>{{ $emails->total() }}</strong> results
                            @else
                                No records found
                            @endif
                        </div>
                    </div>

                    <div class="col-auto">
                        <nav class="app-pagination">
                            <ul class="pagination justify-content-center">
                                {{-- Previous --}}
                                <li class="page-item {{ $emails->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $emails->previousPageUrl() }}">Previous</a>
                                </li>

                                {{-- First Page --}}
                                @if ($emails->currentPage() > 3)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $emails->url(1) }}">1</a>
                                    </li>

                                    @if ($emails->currentPage() > 4)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                @endif

                                {{-- Middle Pages (Current ±2) --}}
                                @for ($i = max(1, $emails->currentPage() - 2); $i <= min($emails->lastPage(), $emails->currentPage() + 2); $i++)
                                    <li class="page-item {{ $emails->currentPage() == $i ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $emails->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endfor

                                {{-- Last Page --}}
                                @if ($emails->currentPage() < $emails->lastPage() - 2)

                                    @if ($emails->currentPage() < $emails->lastPage() - 3)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif

                                    <li class="page-item">
                                        <a class="page-link" href="{{ $emails->url($emails->lastPage()) }}">
                                            {{ $emails->lastPage() }}
                                        </a>
                                    </li>
                                @endif

                                {{-- Next --}}
                                <li class="page-item {{ !$emails->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $emails->nextPageUrl() }}">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    @include('partials/sweetalert')
@endpush