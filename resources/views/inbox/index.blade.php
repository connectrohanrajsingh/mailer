@extends('layout.base')
@section('title', 'Inbox')

@section('content')
    <div class="app-content pt-3 p-md-3 p-lg-4">
        <div class="container-xl">
            <div class="row g-3 mb-4 align-items-center justify-content-between">
                <div class="col-auto">
                    <h1 class="app-page-title mb-0">Orders</h1>
                </div>
                <div class="col-auto">
                    <div class="page-utilities">
                        <div class="row g-2 justify-content-start justify-content-md-end align-items-center">
                            <div class="col-auto bg-dark">
                                <form class="table-search-form row gx-1 align-items-center" action="{{ route('inbox.filter') }}" method="POST">
                                    @csrf
                                    <div class="col-auto">
                                        <input type="text" name="sv" id="search-orders" class="form-control search-orders" placeholder="Search" value="{{ request('sv') }}">
                                    </div>

                                    <div class="col-auto">
                                        <select name="fo" class="form-select w-auto">
                                            @foreach ($filterOptions as $filterOption)
                                                <option value="{{ $filterOption['value'] }}" @selected($filterOption['value'] == request('fo'))>{{ $filterOption['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-auto">
                                        <select name="fc" class="form-select w-auto">
                                            @foreach ($filterCondition as $condition)
                                                <option value="{{ $condition['value'] }}" @selected($condition['value'] == request('fc'))>{{ $condition['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-auto">
                                        <button type="submit" class="btn app-btn-secondary">Search</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="orders-all">
                <div class="app-card app-card-orders-table shadow-sm mb-5">
                    <div class="app-card-body">
                        <div class="table-responsive">
                            <table class="table app-table-hover mb-0 text-left">
                                <thead>
                                    <tr>
                                        <th class="cell">Sender Email</th>
                                        <th class="cell">Sender Name</th>
                                        <th class="cell">Subject</th>
                                        <th class="cell">Email Date</th>
                                        <th class="cell">Received Date</th>
                                        <th class="cell">Attachments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($emails as $email)
                                        <tr>
                                            <td class="cell">{{ $email->sender_email }}</td>
                                            <td class="cell">{{ $email->sender_name }}</td>
                                            <td class="cell">{{ $email->subject }}</td>
                                            <td class="cell">{{ $email->date }}</td>
                                            <td class="cell">{{ $email->created_at }}</td>
                                            <td class="cell">{{ $email->have_attachments }}</td>
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


                    <div class="col-auto">
                        <div class="page-utilities">
                            <div class="row g-2 justify-content-start justify-content-md-end align-items-center">
                                <div class="col-auto">
                                    <select class="form-select w-auto">
                                        <option selected value="option-1">All</option>
                                        <option value="option-2">This week</option>
                                        <option value="option-3">This month</option>
                                        <option value="option-4">Last 3 months</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <a class="btn app-btn-secondary" href="#">
                                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-download me-1" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                            <path fill-rule="evenodd" d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                        </svg>
                                        Download CSV
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                title: 'Error!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                title: 'Validation Error!',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                icon: 'error'
            });
        </script>
    @endif
@endpush