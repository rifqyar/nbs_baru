<header class="topbar">
    <nav class="navbar top-navbar navbar-expand-md navbar-light">
        <div class="navbar-header hidden-sm-down">
            <a class="navbar-brand" href="{{ asset('') }}">
                <b>
                    <img src="{{ asset('assets/images/MTI-Logo.png') }}" width="175" alt="homepage" class="dark-logo" />
                    <img src="{{ asset('assets/images/MTI-Logo.png') }}" width="175" alt="homepage"
                        class="light-logo" />
                </b>
                <span>
                </span>
            </a>
        </div>
        <!-- ============================================================== -->
        <!-- End Logo -->
        <!-- ============================================================== -->
        <div class="navbar-collapse">
            <!-- ============================================================== -->
            <!-- toggle and nav items -->
            <!-- ============================================================== -->
            <ul class="navbar-nav mr-auto mt-md-0">
                <!-- This is  -->
                <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark"
                        href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
            </ul>

            <ul class="navbar-nav my-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href=""
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-history" aria-hidden="true"></i>
                        <div class="notify">
                            <span class="heartbit"></span>
                            <span class="point"></span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right mailbox scale-up history-container">
                        <ul>
                            <li>
                                <div class="drop-title text-center">History Container</div>
                            </li>
                            <li>
                                @php
                                    $history = getHistoryNotification();
                                @endphp

                                <div class="message-center">
                                    @foreach ($history as $record)
                                        <a href="{{ route('uster.monitoring.history_container') }}?container={{ $record->no_container }}"
                                            class="dropdown-item d-flex align-items-center border-bottom">
                                            <span
                                                class="icon-container bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center text-primary">
                                                <i class="fa fa-ship"></i>
                                            </span>
                                            <div class="w-100 ml-3">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="mb-1 item-title">
                                                        {{ $record->no_container }}
                                                        ({{ $record->nama_lengkap ?? 'SYSTEM' }})
                                                    </h6>
                                                    <span class="text-muted item-subtitle"
                                                        style="font-size:10px">{{ $record->tgl_update }}</span>
                                                </div>
                                                <span
                                                    class="d-block text-truncate text-muted item-subtitle">{{ $record->kegiatan }}</span>
                                            </div>
                                        </a>
                                    @endforeach

                                </div>

                            </li>
                            <li>
                                <a class="nav-link text-center"
                                    href="{{ route('uster.monitoring.history_container') }}"> <strong>Lihat Semua
                                        Histori Container</strong> <i class="fa fa-angle-right"></i> </a>
                            </li>
                        </ul>
                    </div>
                </li>


                <!-- ============================================================== -->
                <!-- Profile -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href=""
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img
                            src="https://ui-avatars.com/api/?name={{ Session::get('name') }}" alt="user"
                            class="profile-pic" /></a>

                    <div class="dropdown-menu dropdown-menu-right scale-up">
                        <ul class="dropdown-user">
                            <li>
                                <div class="dw-user-box">
                                    <div class="u-img"><img
                                            src="https://ui-avatars.com/api/?name={{ Session::get('name') }}"
                                            alt="user"></div>
                                    <div class="u-text">
                                        <h4>{{ Str::length(Session::get('name')) > 15 ? Str::substr(Session::get('name'), 0, 12) . '...' : Session::get('name') }}
                                        </h4>
                                    </div>
                                </div>
                            </li>
                            <li><a href="{{ route('logout') }}"><i class="fa fa-power-off"></i> Logout</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>
