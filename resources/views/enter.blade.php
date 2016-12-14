<?php
header('Access-Control-Allow-Origin: *');
?>
        <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>JS Frontend</title>
    <link rel="stylesheet" href="{{ elixir('css/app.css') }}">
    <style>
        .panel-heading {
            cursor: pointer;
        }
        .spacer {
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="container" id="app">
    <div class="row spacer">
        <div class="col-md-12">
            <h3>Enter your URL</h3>
            {!! Form::open(['route' => 'requestReport']) !!}
            <div class="row">
                <div class="col-md-11">
                    <input class="form-control" name="url" placeholder="https://yoururl.com" value="https://youtube.com">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary form-control">SCAN!</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="ignoreTLS" value="1">
                            SSL/TLS Zertifikatsfehler ignorieren
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="proxy" value="1" v-model="proxy">
                            Proxy verwenden
                        </label>
                        <span v-show="proxy">
                                <br><input type="text" class="form-control" name="proxyAddress" value="http://{{ $hostIp }}:8888">
                            </span>
                    </div>
                </div>
            </div>
            <div class="row">

            </div>
            {!! Form::close() !!}
        </div>
    </div>

</div>
<script src="{{ elixir('js/app.js') }}"></script>
</body>
</html>
