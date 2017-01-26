<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>JS Frontend</title>
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
</head>
<body>
<div class="container" id="app">

    <div class="vertical-center full-height" v-show="show.load">
        <div class="sk-folding-cube">
            <div class="sk-cube1 sk-cube"></div>
            <div class="sk-cube2 sk-cube"></div>
            <div class="sk-cube4 sk-cube"></div>
            <div class="sk-cube3 sk-cube"></div>
        </div>
    </div>

    <div class="vertical-center full-height" :class="{ 'hidden': show.form === null, 'animated zoomIn': show.form, 'animated zoomOut': !show.form }">
        <div class="col-md-12">
            <h3>Enter your URL</h3>
            <form action="{{ route("requestReport") }}" v-on:submit="sendRequest">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-11">
                    <input class="form-control" placeholder="https://yoururl.com" v-model="formRequest.url">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary form-control">SCAN!</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="checkbox">
                        <textarea v-model="formRequest.whitelist" rows="6" placeholder="sub1.example.com
sub2.example.com" class="form-control"></textarea>
                    </div>

                </div>
                <div class="col-md-3">
                    <div v-show="!formRequest.scan.custom">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" v-model="toggleScans">
                                Select all
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" v-model="formRequest.scan.anchor">
                                Include <b>a</b>-Tags
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" v-model="formRequest.scan.images">
                                Include <b>img</b>-Tags
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" v-model="formRequest.scan.scripts">
                                Include <b>script</b>-Tags
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" v-model="formRequest.scan.links">
                                Include <b>link</b>-Tags
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" v-model="formRequest.scan.media">
                                Include <b>audio</b>- and <b>video</b>-Tags
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" v-model="formRequest.scan.area">
                                Include <b>area</b>-Tags
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" v-model="formRequest.scan.frames">
                                Include <b>iframe</b>- and <b>frame</b>-Tags
                            </label>
                        </div>
                    </div>
                    <div class="checkbox">
                        <div class="checkbox full-width" v-show="formRequest.scan.custom">
                            <textarea name="customJson" rows="6" class="form-control" v-model="formRequest.scan.customJson"></textarea>
                        </div>
                        <label class="text-primary">
                            <input type="checkbox" v-model="formRequest.scan.custom">Custom configuration</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" v-model="formRequest.doNotCrawl">
                            Do NOT crawl
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" v-model="formRequest.limitOn">
                            Limit scan
                        </label>
                        <span v-show="formRequest.limitOn">
                                <br><input class="form-control" v-model="formRequest.limit">
                            </span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" v-model="formRequest.ignoreTLS">
                            Ignore SSL/TLS certificate errors
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" v-model="formRequest.proxy">
                            Use a proxy
                        </label>
                        <span v-show="formRequest.proxy">
                            <br><input type="text" class="form-control" v-model="formRequest.proxyAddress">
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">

            </div>
            </form>
        </div>
    </div>

    <div class="vertical-center full-height" :class="{ 'hidden': show.report === null, 'animated zoomIn': show.report, 'animated zoomOut': !show.report }">
        @{{ report }}
    </div>

</div>
<script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>
