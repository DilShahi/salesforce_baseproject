<div class="topnav" id="myTopnav">
    <a href="{{ route('home') }}" class="active">Home</a>
    <a href="{{ route('salesforce.userlist') }}">User List</a>
    @if (session('sf_access_token'))
        <form method="POST" action="{{ route('sf.logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn-link">Logout</button>
        </form>
    @else
        <a href="{{ route('sf.redirect') }}">Login</a>
    @endif
    <a href="javascript:void(0);" class="icon" onclick="myFunction()">
        <i class="fa fa-bars"></i>
    </a>
</div>
<script>
    function myFunction() {
        var x = document.getElementById("myTopnav");
        if (x.className === "topnav") {
            x.className += " responsive";
        } else {
            x.className = "topnav";
        }
    }
</script>
