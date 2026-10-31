<aside class="left-sidebar" data-sidebarbg="skin5">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav" class="pt-4">
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)"
                        aria-expanded="false">
                        <i class="mdi mdi-home-city"></i>
                        <span class="hide-menu">Alquileres</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a href="{{ route('alquileres.dashboard') }}" class="sidebar-link">
                                <i class="mdi mdi-view-dashboard"></i><span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('alquileres.propiedades') }}" class="sidebar-link">
                                <i class="mdi mdi-chart-bar"></i><span class="hide-menu">Propiedades</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('alquileres.inquilinos') }}" class="sidebar-link">
                                <i class="mdi mdi-chart-bubble"></i><span class="hide-menu">Inquilinos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('alquileres.contratos') }}" class="sidebar-link">
                                <i class="mdi mdi-border-inside"></i><span class="hide-menu">Contratos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ route('alquileres.pagos') }}" class="sidebar-link">
                                <i class="mdi mdi-clipboard-check"></i><span class="hide-menu">Pagos</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>