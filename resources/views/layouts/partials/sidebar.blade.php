<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">

    <a href="{{route('home')}}" class="logo">
      <span class="logo-lg">{{ Session::get('business.name') }}</span>
    </a>

    <!-- Sidebar Menu -->
    {!! Menu::admin_sidebar_menu() !!} <!-- /.sidebar-menu -->
  </section>
  <!-- /.sidebar -->
</aside>