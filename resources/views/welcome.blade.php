<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>GiveUp</title>

    <link rel="stylesheet" href="{{URL::asset('/assets/css/style.css')}}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css"
        integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Rokkitt" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"
        integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"
        integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous">
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

        let platform = $(".platform").attr('val')
        let postId = $(".post").attr('val')

        $('.toast').toast('show');

            if(platform == 'iOS'){
                setInterval(function () {
                    $(location).attr('href',`https://app-giveup.com`);
                }, 5000);
            }else if(platform == 'AndroidOS'){
                setInterval(function () {
                    $(location).attr('href',`intent://app-giveup.com#Intent;scheme=https;package=team.another.giveup;end`);
                }, 5000);
            }
    });
    </script>
</head>

<body>

    <div class="content">
        <div class="platform" val="{{$data['platform']}}"></div>
        <section class="hero">

            <div class="container">

                @if ($data['platform'] == 'iOS')

                <div class="toast" style="max-width:none;" role="alert" data-autohide="false">
                    <div class="toast-header">
                        <img src="{{URL::asset('/assets/icon/logo_long.svg')}}" height="10" width="80"
                            class="rounded mr-2" alt="...">
                        <strong class="mr-auto"> | Open in app</strong>
                        <small> <button type="button" style="float: right;margin-top: 5px;"
                                class="ml-2 mb-1 btn btn-outline-success"
                                onclick="location.href='https://app-giveup.com'" aria-label="Get Start"> Get
                                started</button></small>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                @endif

                @if ($data['platform'] == 'AndroidOS')

                <div class="toast" style="max-width:none;" role="alert" data-autohide="false">
                    <div class="toast-header">
                        <img src="{{URL::asset('/assets/icon/logo_long.svg')}}" height="10" width="80"
                            class="rounded mr-2" alt="...">
                        <strong class="mr-auto"> | Open in app</strong>
                        <small> <button type="button" style="float: right;margin-top: 5px;"
                                class="ml-2 mb-1 btn btn-outline-success"
                                onclick="location.href='intent://app-giveup.com#Intent;scheme=https;package=team.another.giveup;end'"
                                aria-label="Get Start"> Get
                                started</button></small>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                @endif
                <div class="row">

                    <div class="col-lg-6 offset-lg-3">

                        <img src="{{URL::asset('/assets/icon/logo-blue-ty.svg')}}" class="center" height="250"
                            width="250">
                    </div>
                    <div class="col-lg-3">
                    </div>
                </div>
            </div>
        </section>
    </div>
    </div>


</body>

</html>
