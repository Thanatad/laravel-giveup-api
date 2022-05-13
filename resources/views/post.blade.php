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

    <!-- Meta Facebook -->
    <meta property="og:url" content="{{Request::url()}}?post={{$data['id']}}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{$data['name']}}" />
    <meta property="og:description" content="{{$data['content']}}" />
    @if ($data['fileType'] == 2 || $data['fileType'] == 4)
    <meta property="og:image" content="{{$data['file']}}" />
    @else
    <meta property="og:video" content="{{$data['file']}}" />
    @endif

    <script type="text/javascript">
        $(document).ready(function() {

        let platform = $(".platform").attr('val')
        let postId = $(".post").attr('val')

        $('.toast').toast('show');

            if(platform == 'iOS'){
                setInterval(function () {
                    $(location).attr('href',`https://app-giveup.com/post?post=${postId}`);
                }, 5000);
            }else if(platform == 'AndroidOS'){
                setInterval(function () {
                    $(location).attr('href',`intent://app-giveup.com/post?post=${postId}#Intent;scheme=https;package=team.another.giveup;end`);
                }, 5000);
            }
    });
    </script>
</head>

<body>

    <div class="content">
        <div class="post" val="{{$data['id']}}"></div>
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
                                onclick="location.href='https://app-giveup.com/post?post={{$data['id']}}'"
                                aria-label="Get Start"> Get
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
                                onclick="location.href='intent://app-giveup.com/post?post={{$data['id']}}#Intent;scheme=https;package=team.another.giveup;end'"
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

                        <div class="cardbox shadow-lg bg-white">

                            <div class="cardbox-heading">

                                <div class="dropdown float-right">
                                    <a class="like"><img src="{{URL::asset('/assets/icon/logo-blue-ty.svg')}}"
                                            alt="profile Pic" height="50" width="50"></a>
                                </div>

                                <div class="media m-0">
                                    <div class="d-flex mr-3">
                                        <a href=""><img class="img-fluid rounded-circle" src="{{$data['image']}}"
                                                alt="User"></a>
                                    </div>
                                    <div class="media-body">
                                        <p class="m-0">{{$data['name']}}</p>
                                        <small><span><i class="icon ion-md-pin"></i>
                                                {{$data['location']}}</a></span></small>
                                        <small><span><i class="icon ion-md-time"></i>
                                                {{$data['createAt']}}</span></small>
                                    </div>
                                </div>
                            </div>

                            <div class="cardbox-item">
                                @if ($data['fileType'] == 2 || $data['fileType'] == 4)
                                <img style="width: 100%;" class="img-fluid" src="{{$data['file']}}" alt="Image">
                                @else
                                <video class="video" style="width: 100%;" loop autoplay>
                                    <source src="{{$data['file']}}" type="video/mp4">
                                </video>
                                @endif
                            </div>

                            <div class="cardbox-base">
                                <ul class="float-right">
                                    <li><a><i class="fa fa-comments"></i></a></li>
                                    <li><a><em class="mr-5">{{$data['comment']}}</em></a></li>
                                    <li><a><i class="fa fa-share-alt"></i></a></li>
                                    <li><a><em class="mr-3">{{$data['share']}}</em></a></li>
                                </ul>
                                <ul>
                                    <a class="like"><img src="{{URL::asset('/assets/icon/Like-ac.svg')}}"
                                            alt="profile Pic" height="20" width="20"></a>

                                    <li><a href="#"><img
                                                src="{{$imgLikeI != null ? $imgLikeI : 'https://app-giveup.com/storage/avatar/default-profile.png'}}"
                                                class="img-fluid rounded-circle" alt="User"></a></li>
                                    <li><a href="#"><img
                                                src="{{$imgLikeII != null ? $imgLikeII : 'https://app-giveup.com/storage/avatar/default-profile.png'}}"
                                                class="img-fluid rounded-circle" alt="User"></a></li>
                                    <li><a href="#"><img
                                                src="{{$imgLikeIII != null ? $imgLikeIII : 'https://app-giveup.com/storage/avatar/default-profile.png'}}"
                                                class="img-fluid rounded-circle" alt="User"></a></li>
                                    <li><a href="#"><img
                                                src="{{$imgLikeIV != null ? $imgLikeIV : 'https://app-giveup.com/storage/avatar/default-profile.png'}}"
                                                class="img-fluid rounded-circle" alt="User"></a></li>
                                    <li><a><span>{{$data['like']}} Likes</span></a></li>
                                </ul>
                            </div>

                            <div class="cardbox-comments">
                                <span class="float-left">
                                    {{$data['content']}}
                                </span>
                            </div>
                        </div>
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
