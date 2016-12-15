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
                    <input class="form-control" name="url" placeholder="https://yoururl.com" value="https://hackmanit.de">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary form-control">SCAN!</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="checkbox">
                        <textarea name="whitelist" rows="6" placeholder="sub1.example.com
sub2.example.com" class="form-control"></textarea>
                    </div>

                </div>
                <div class="col-md-3">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="scan[]" value="images">
                            Include <b>img</b>-Tags
                        </label>
                        <label>
                            <input type="checkbox" name="scan[]" value="scripts">
                            Include <b>script</b>-Tags
                        </label>
                        <label>
                            <input type="checkbox" name="scan[]" value="links">
                            Include <b>link</b>-Tags
                        </label>
                        <label>
                            <input type="checkbox" name="scan[]" value="media">
                            Include <b>audio</b>- and <b>video</b>-Tags
                        </label>
                        <label>
                            <input type="checkbox" name="scan[]" value="area">
                            Include <b>area</b>-Tags
                        </label>
                        <label>
                            <input type="checkbox" name="scan[]" value="frames">
                            Include <b>iframe</b>- and <b>frame</b>-Tags
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="doNotCrawl" value="1">
                            Do NOT crawl
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="limitOn" value="1" v-model="limitOn">
                            Limit scan
                        </label>
                        <span v-show="limitOn">
                                <br><input type="text" class="form-control" name="limit" value="{{ env('LIMIT', 100) }}">
                            </span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="ignoreTLS" value="1" checked>
                            Ignore SSL/TLS certificate errors
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="proxy" value="1" v-model="proxy">
                            Use a proxy
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
