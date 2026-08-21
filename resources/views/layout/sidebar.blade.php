<div id="app-sidepanel" class="app-sidepanel">
    <div id="sidepanel-drop" class="sidepanel-drop"></div>
    <div class="sidepanel-inner d-flex flex-column">
        <a href="#" id="sidepanel-close" class="sidepanel-close d-xl-none">&times;</a>

        <div class="app-branding">
            <a class="app-logo" href="{{ route('dashboard.index') }}">
                <img class="logo-icon me-2" src="{{ asset('assets/images/app-logo.svg') }}" alt="logo"><span class="logo-text">Mailer</span>
            </a>
        </div>

        <div class="ml-compose-slot">
            <a href="{{ route('compose.index') }}" class="ml-btn-compose-block">
                <i class="fa-solid fa-pen-to-square"></i> Compose
            </a>
            <hr>
        </div>

        <nav id="app-nav-main" class="app-nav app-nav-main flex-grow-1">
            <ul class="app-menu list-unstyled accordion" id="menu-accordion">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}" href="{{ route('dashboard.index') }}">
                        <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                        <span class="nav-link-text ml-nav-label">Overview</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('inbox.*') ? 'active' : '' }}" href="{{ route('inbox.index') }}">
                        <span class="nav-icon"><i class="fa-solid fa-inbox"></i></span>
                        <span class="nav-link-text ml-nav-label">Inbox</span>
                        @if ($mailCounts['inboxUnread'])
                            <span class="ml-count ml-count-unread">{{ $mailCounts['inboxUnread'] > 99 ? '99+' : $mailCounts['inboxUnread'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('outbox.*') ? 'active' : '' }}" href="{{ route('outbox.index') }}">
                        <span class="nav-icon"><i class="fa-solid fa-paper-plane"></i></span>
                        <span class="nav-link-text ml-nav-label">Sent</span>
                        @if ($mailCounts['outboxTotal'])
                            <span class="ml-count">{{ $mailCounts['outboxTotal'] > 99 ? '99+' : $mailCounts['outboxTotal'] }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </nav>

        <div class="app-sidepanel-footer">
            <nav class="app-nav app-nav-footer">
                <ul class="app-menu footer-menu list-unstyled">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="{{ route('dashboard.index') }}">
                            <span class="nav-icon"><i class="fa-solid fa-inbox"></i></span>
                            <span class="nav-link-text ml-nav-label">{{ $mailCounts['inboxTotal'] }} emails synced</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="https://github.com/connectrohanrajsingh/mailer.git" target="_blank" rel="noopener">
                            <span class="nav-icon"><i class="fa-brands fa-github"></i></span>
                            <span class="nav-link-text">Built by <strong>Rohan Singh</strong></span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>