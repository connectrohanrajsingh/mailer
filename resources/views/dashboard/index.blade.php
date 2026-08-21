@extends('layout.base')
@section('title', 'Overview')

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

    $chartLabels = $detailStats->map(fn ($s) => \Carbon\Carbon::parse($s->monthGroup)->format('d M'))->values();
    $chartTotals = $detailStats->pluck('total')->values();
    $chartAttach = $detailStats->pluck('attachment')->values();
@endphp

@section('content')
    <div class="app-content">
        <div class="ml-page">

            {{-- Hero --}}
            <div class="ml-hero mb-4">
                <div class="ml-hero-text">
                    <h2 class="ml-hero-title">{{ $greeting }}</h2>
                    <p class="ml-hero-sub">
                        @if ($unreadCount)
                            You have <strong>{{ $unreadCount }}</strong> unread message{{ $unreadCount > 1 ? 's' : '' }}
                            and <strong>{{ $sentCount }}</strong> sent.
                        @else
                            All caught up &mdash; <strong>{{ $sentCount }}</strong> message{{ $sentCount == 1 ? '' : 's' }} sent so far.
                        @endif
                    </p>
                    <div class="ml-hero-actions">
                        <a href="{{ route('inbox.index') }}" class="ml-btn-white">
                            <i class="fa-solid fa-inbox"></i> Open Inbox
                        </a>
                        <a href="{{ route('compose.index') }}" class="ml-btn-ghost">
                            <i class="fa-solid fa-pen-to-square"></i> Compose
                        </a>
                    </div>
                </div>
                <i class="fa-regular fa-envelope ml-hero-art" aria-hidden="true"></i>
            </div>

            {{-- Stat cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="ml-stat-card">
                        <span class="ml-stat-icon ml-gi-blue"><i class="fa-solid fa-inbox"></i></span>
                        <div class="ml-stat-figure">{{ number_format($inboxStats->inboxEmails ?? 0) }}</div>
                        <div class="ml-stat-label">Emails synced</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="ml-stat-card">
                        <span class="ml-stat-icon ml-gi-purple"><i class="fa-solid fa-users"></i></span>
                        <div class="ml-stat-figure">{{ number_format($inboxStats->inboxDistinctEmails ?? 0) }}</div>
                        <div class="ml-stat-label">Unique senders</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="ml-stat-card">
                        <span class="ml-stat-icon ml-gi-green"><i class="fa-solid fa-paperclip"></i></span>
                        <div class="ml-stat-figure">{{ number_format($inboxAttachments ?? 0) }}</div>
                        <div class="ml-stat-label">Attachments stored</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="ml-stat-card">
                        <span class="ml-stat-icon ml-gi-orange"><i class="fa-regular fa-calendar"></i></span>
                        <div class="ml-stat-figure ml-stat-figure-sm">
                            {{ $inboxStats->minDate }} &rarr; {{ $inboxStats->maxDate }}
                        </div>
                        <div class="ml-stat-label">Sync window</div>
                    </div>
                </div>
            </div>

            {{-- Chart + top senders --}}
            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-7">
                    <div class="ml-panel h-100">
                        <div class="ml-panel-head">
                            <h5 class="ml-panel-title">Mail volume</h5>
                            <span class="ml-panel-sub">Last {{ $detailStats->count() }} active days</span>
                        </div>
                        <div class="ml-panel-body">
                            @if ($detailStats->count())
                                <canvas id="mlVolumeChart" height="150"></canvas>
                            @else
                                <div class="ml-empty">
                                    <i class="fa-solid fa-chart-simple"></i>
                                    No activity yet
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="ml-panel h-100">
                        <div class="ml-panel-head">
                            <h5 class="ml-panel-title">Top senders</h5>
                            <span class="ml-panel-sub">Most frequent contacts</span>
                        </div>
                        <div class="ml-panel-body">
                            @forelse ($latesEmailstats as $stat)
                                <div class="ml-sender-row">
                                    @include('partials.avatar', ['name' => null, 'email' => $stat->sender_email])
                                    <div class="ml-sender-main">
                                        <div class="d-flex justify-content-between align-items-baseline gap-2">
                                            <span class="ml-sender-name">{{ $stat->sender_email }}</span>
                                            <span class="ml-count">{{ $stat->email_count }}</span>
                                        </div>
                                        <div class="ml-bar">
                                            <span style="width: {{ min(100, $stat->bar_percentage) }}%"></span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="ml-empty">
                                    <i class="fa-regular fa-user"></i>
                                    No senders yet
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daily breakdown --}}
            <div class="ml-list-card">
                <div class="ml-panel-head px-3 pt-3 pb-2">
                    <h5 class="ml-panel-title mb-0">Daily breakdown</h5>
                    <span class="ml-panel-sub">Messages received per day</span>
                </div>
                <div class="table-responsive">
                    <table class="table ml-table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Emails</th>
                                <th>Unique senders</th>
                                <th>Attachments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($detailStats as $stat)
                                <tr>
                                    <td class="fw-semibold">{{ \Carbon\Carbon::parse($stat->monthGroup)->format('d M Y') }}</td>
                                    <td><span class="ml-pill">{{ $stat->total }}</span></td>
                                    <td><span class="ml-pill">{{ $stat->distinctEmail }}</span></td>
                                    <td><span class="ml-pill">{{ $stat->attachment }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="ml-empty">
                                            <i class="fa-regular fa-calendar-days"></i>
                                            Nothing to show yet
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('after-scripts')
    @if ($detailStats->count())
        <script src="{{ asset('assets/plugins/chart.js/chart.min.js') }}"></script>
        <script>
            (function () {
                var el = document.getElementById('mlVolumeChart');
                if (!el) return;

                new Chart(el.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            {
                                label: 'Emails',
                                backgroundColor: 'rgba(37, 99, 235, .85)',
                                hoverBackgroundColor: 'rgba(37, 99, 235, 1)',
                                data: @json($chartTotals),
                                maxBarThickness: 44
                            },
                            {
                                label: 'Attachments',
                                backgroundColor: 'rgba(124, 58, 237, .8)',
                                hoverBackgroundColor: 'rgba(124, 58, 237, 1)',
                                data: @json($chartAttach),
                                maxBarThickness: 44
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        aspectRatio: 1.9,
                        legend: {
                            position: 'bottom',
                            align: 'end',
                            labels: { usePointStyle: true, boxWidth: 8, fontColor: '#69718a' }
                        },
                        scales: {
                            xAxes: [{
                                gridLines: { display: false },
                                ticks: { fontColor: '#69718a' }
                            }],
                            yAxes: [{
                                gridLines: { color: '#eef1f7', drawBorder: false, zeroLineColor: '#eef1f7' },
                                ticks: { beginAtZero: true, precision: 0, fontColor: '#69718a' }
                            }]
                        },
                        tooltips: {
                            backgroundColor: '#1f2430',
                            titleFontColor: '#fff',
                            bodyFontColor: '#fff',
                            xPadding: 12,
                            yPadding: 10,
                            displayColors: false
                        }
                    }
                });
            })();
        </script>
    @endif

    @include('partials/sweetalert')
@endpush
