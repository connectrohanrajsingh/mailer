<div class="app-header-inner">
    <div class="container-fluid py-2">
        <div class="ml-header-content">

            <div class="d-flex align-items-center gap-2">
                <a id="sidepanel-toggler" class="sidepanel-toggler d-inline-block d-xl-none" href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" role="img">
                        <title>Menu</title>
                        <path stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2" d="M4 7h22M4 15h22M4 23h22"></path>
                    </svg>
                </a>

                <a href="{{ route('dashboard.index') }}" class="ml-brand d-xl-none">
                    <img src="{{ asset('assets/images/app-logo.svg') }}" alt="Mailer"> Mailer
                </a>
            </div>

            <div class="ml-header-actions ms-auto">
                <div class="app-utility-item app-user-dropdown dropdown">
                    <a class="dropdown-toggle" id="user-dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" style="text-decoration:none">
                        <img src="{{ asset('assets/images/user.png') }}" alt="user profile">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="user-dropdown-toggle">
                        <li><a class="dropdown-item" href="{{ route('inbox.index') }}"><i class="fa-solid fa-inbox me-2"></i>Inbox</a></li>
                        <li><a class="dropdown-item" href="{{ route('outbox.index') }}"><i class="fa-solid fa-paper-plane me-2"></i>Sent</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal" href="#"><i class="fa-solid fa-right-from-bracket me-2"></i>Log Out</a></li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
