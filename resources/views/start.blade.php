<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HTTP Secure Header Scanner</title>
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
</head>
<body>

<div class="container vertical-center full-height" id="app">

    {{-- Loading animation --}}
    <div v-show="show.load">
        <div class="wrapper">
            <div class="sk-folding-cube">
                <div class="sk-cube1 sk-cube"></div>
                <div class="sk-cube2 sk-cube"></div>
                <div class="sk-cube4 sk-cube"></div>
                <div class="sk-cube3 sk-cube"></div>
            </div>
            <br>
            <p class="text-muted text-center" v-html="loadingMessage"></p>
        </div>
    </div>

    {{-- Form --}}
    <div class="full-width" :class="{ 'hidden': show.form === null, 'animated zoomIn': show.form, 'animated zoomOut hidden': !show.form }">
        <div class="col-md-12">
            <h1>HTTP Secure Header Scanner</h1>
            <hr>
            <h3>Select your preferred mode:</h3>
            <ul class="nav nav-pills">
                <li class="active"><a data-toggle="pill" href="#single">Single URL Scan</a></li>
                <li><a data-toggle="pill" href="#multiple">Multiple URL Scan</a></li>
                <li><a data-toggle="pill" href="#crawler">Multiple URL Scan via Crawler</a></li>
            </ul>

            <div class="tab-content">
                <div id="single" class="tab-pane fade in active">
                    <h3>Enter your URL</h3>
                    <div class="row">
                        <div class="col-md-10">
                            <input class="form-control" placeholder="https://yoururl.com" v-model="singleRequest.url">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary form-control" @click="getSingleReport()">Scan</button>
                        </div>
                    </div>
                </div>

                <div id="multiple" class="tab-pane fade">
                    <h3>Enter your list of URLs to check</h3>
                    <div class="row vertical-center">
                        <div class="col-sm-10">
                            <textarea class="form-control" rows="5" placeholder="Insert each URL in a seperated line." v-model="multipleRequest.urls"></textarea>
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-primary form-control" @click="getMultipleReport()">Scan</button>
                        </div>
                    </div>
                </div>

                <div id="crawler" class="tab-pane fade">
                    <h3>Configure your crawler settings</h3>
                    <div class="row vertical-center">
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-sm-12">
                                    <label>Enter a URL to start</label>
                                    <input class="form-control" placeholder="https://yoururl.com" v-model="crawlRequest.url">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Whitelist</strong><br>
                                    <textarea class="form-control" rows="5" placeholder="Insert each URL in a seperated line." v-model="crawlRequest.whitelist"></textarea>
                                </div>
                                <div class="col-sm-6">
                                    <div class="row scan-elements">
                                        <div class="col-sm-4 col-md-3">
                                            <strong>Anchor-Tags</strong><br>
                                            <div class="btn-group btn-toggle">
                                                <button class="btn btn-sm" @click="crawlRequest.scan.anchor = true" :class="{'btn-primary active': crawlRequest.scan.anchor, 'btn-default': !crawlRequest.scan.anchor }">ON</button>
                                                <button class="btn btn-sm" @click="crawlRequest.scan.anchor = false" :class="{'btn-primary active': !crawlRequest.scan.anchor, 'btn-default': crawlRequest.scan.anchor }">OFF</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-md-3">
                                            <strong>Image-Tags</strong><br>
                                            <div class="btn-group btn-toggle">
                                                <button class="btn btn-sm" @click="crawlRequest.scan.image = true" :class="{'btn-primary active': crawlRequest.scan.image, 'btn-default': !crawlRequest.scan.image }">ON</button>
                                                <button class="btn btn-sm" @click="crawlRequest.scan.image = false" :class="{'btn-primary active': !crawlRequest.scan.image, 'btn-default': crawlRequest.scan.image }">OFF</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-md-3">
                                            <strong>Script-Tags</strong><br>
                                            <div class="btn-group btn-toggle">
                                                <button class="btn btn-sm" @click="crawlRequest.scan.script = true" :class="{'btn-primary active': crawlRequest.scan.script, 'btn-default': !crawlRequest.scan.script }">ON</button>
                                                <button class="btn btn-sm" @click="crawlRequest.scan.script = false" :class="{'btn-primary active': !crawlRequest.scan.script, 'btn-default': crawlRequest.scan.script }">OFF</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-md-3">
                                            <strong>Link-Tags</strong><br>
                                            <div class="btn-group btn-toggle">
                                                <button class="btn btn-sm" @click="crawlRequest.scan.link = true" :class="{'btn-primary active': crawlRequest.scan.link, 'btn-default': !crawlRequest.scan.link }">ON</button>
                                                <button class="btn btn-sm" @click="crawlRequest.scan.link = false" :class="{'btn-primary active': !crawlRequest.scan.link, 'btn-default': crawlRequest.scan.link }">OFF</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-md-3">
                                            <strong>Media-Tags</strong><br>
                                            <div class="btn-group btn-toggle">
                                                <button class="btn btn-sm" @click="crawlRequest.scan.media = true" :class="{'btn-primary active': crawlRequest.scan.media, 'btn-default': !crawlRequest.scan.media }">ON</button>
                                                <button class="btn btn-sm" @click="crawlRequest.scan.media = false" :class="{'btn-primary active': !crawlRequest.scan.media, 'btn-default': crawlRequest.scan.media }">OFF</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-md-3">
                                            <strong>Area-Tags</strong><br>
                                            <div class="btn-group btn-toggle">
                                                <button class="btn btn-sm" @click="crawlRequest.scan.area = true" :class="{'btn-primary active': crawlRequest.scan.area, 'btn-default': !crawlRequest.scan.area }">ON</button>
                                                <button class="btn btn-sm" @click="crawlRequest.scan.area = false" :class="{'btn-primary active': !crawlRequest.scan.area, 'btn-default': crawlRequest.scan.area }">OFF</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-md-3">
                                            <strong>Frame-Tags</strong><br>
                                            <div class="btn-group btn-toggle">
                                                <button class="btn btn-sm" @click="crawlRequest.scan.frame = true" :class="{'btn-primary active': crawlRequest.scan.frame, 'btn-default': !crawlRequest.scan.frame }">ON</button>
                                                <button class="btn btn-sm" @click="crawlRequest.scan.frame = false" :class="{'btn-primary active': !crawlRequest.scan.frame, 'btn-default': crawlRequest.scan.frame }">OFF</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" :class="{ 'hidden': !crawlRequest.expertMode }">
                                <div class="col-sm-12">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <strong>Proxy</strong><br>
                                                    <div class="btn-group btn-toggle">
                                                        <button class="btn btn-sm" @click="crawlRequest.proxy = true" :class="{'btn-primary active': crawlRequest.proxy, 'btn-default': !crawlRequest.proxy }">ON</button>
                                                        <button class="btn btn-sm" @click="crawlRequest.proxy = false" :class="{'btn-primary active': !crawlRequest.proxy, 'btn-default': crawlRequest.proxy }">OFF</button>
                                                    </div>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div :class="{ 'hidden': !crawlRequest.proxy }">
                                                        <strong>Proxy adress</strong>
                                                        <input class="form-control" placeholder="https://yourProxy.com:8080" v-model="crawlRequest.proxyAdress">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <strong>TLS-Errors</strong><br>
                                                    <div class="btn-group btn-toggle">
                                                        <button class="btn btn-sm" @click="crawlRequest.ignoreTLS = false" :class="{'btn-primary active': !crawlRequest.ignoreTLS, 'btn-default': crawlRequest.ignoreTLS }">ON</button>
                                                        <button class="btn btn-sm" @click="crawlRequest.ignoreTLS = true" :class="{'btn-primary active': crawlRequest.ignoreTLS, 'btn-default': !crawlRequest.ignoreTLS }">OFF</button>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <strong>Limit</strong><br>
                                                    <input class="form-control" placeholder="Limit to crawl" v-model="crawlRequest.limit">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-2">
                                            <strong>Custom</strong><br>
                                            <div class="btn-group btn-toggle">
                                                <button class="btn btn-sm" @click="crawlRequest.scan.custom = true" :class="{'btn-primary active': crawlRequest.scan.custom, 'btn-default': !crawlRequest.scan.custom }">ON</button>
                                                <button class="btn btn-sm" @click="crawlRequest.scan.custom = false" :class="{'btn-primary active': !crawlRequest.scan.custom, 'btn-default': crawlRequest.scan.custom }">OFF</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div :class="{ 'hidden': !crawlRequest.scan.custom}">
                                                <strong>Custom JSON</strong><br>
                                                <textarea class="form-control" rows="5" placeholder="Valid JSON" v-model="crawlRequest.scan.customJson"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row crawl-scan">
                                <div class="col-sm-12">
                                    <div>
                                        <p class="text-info text-center toggleExpertMode" @click="crawlRequest.expertMode = true" :class="{ 'hidden': crawlRequest.expertMode }">
                                            <i class="glyphicon glyphicon-cog"></i> Enable Expert-Mode
                                        </p>
                                        <p class="text-danger text-center toggleExpertMode" @click="crawlRequest.expertMode = false" :class="{ 'hidden': !crawlRequest.expertMode }">
                                            <i class="glyphicon glyphicon-cog"></i> Disable Expert-Mode
                                        </p>
                                        <div class="divider"></div>
                                    </div>
                                    <button class="btn btn-primary form-control" @click="getCrawledReport()">Crawl &amp; Scan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Single singleReport --}}
    <div :class="{ 'hidden': show.singleReport === null, 'animated zoomIn': show.singleReport, 'animated zoomOut hidden': !show.singleReport }">
        <div class="col-md-12">
            <span @click="newScan()" class="btn btn-default">New scan</span>
            <hr>
            <h2>HTTP Secure Header Checker - Your result</h2>
            <div class="row rating">
                <div class="col-md-2">
                    <span class="label report-label" :class="{ 'label-success': getFirst(singleReport.siteRating) == 'A', 'label-warning': getFirst(singleReport.siteRating) == 'B', 'label-danger': getFirst(singleReport.siteRating) == 'C'}">@{{ singleReport.siteRating }}</span>
                </div>
                <div class="col-md-10">
                    <table class="table table-striped">
                        <tr>
                            <th>Scanned:</th>
                            <td><a :href="singleReport.url" target="_blank">@{{ singleReport.url }}</a></td>
                        </tr>
                        <tr>
                            <th>Comment:</th>
                            <td>@{{ singleReport.comment }}</td>
                        </tr>
                        <tr>
                            <th><nobr>Rated headers:</nobr></th>
                            <td>
                                <ul class="tag-list">
                                    <li class="label label-info" v-for="(value, header) in singleReport.header">@{{ header }}</li>
                                </ul>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="panel panel-default" v-for="(value, header) in singleReport.header">
                <div class="panel-heading" role="tab" :id="'heading' + header">
                    <h4 class="panel-title">
                        <span class="label label-default" :class="{ 'label-success': getFirst(value.rating) == 'A', 'label-warning': getFirst(value.rating) == 'B', 'label-danger': getFirst(value.rating) == 'C'}">@{{ value.rating }}</span>
                        <a role="button" data-toggle="collapse" data-parent="#accordion" :href="'#collapse' + header" aria-expanded="false" :aria-controls="'collapse' + header">
                            @{{ header }}
                        </a>
                    </h4>
                </div>
                <div :id="'collapse' + header" class="panel-collapse collapse" role="tabpanel" :aria-labelledby="'heading' + header">
                    <div class="panel-body">

                        <table class="table table-hover">
                            <tr>
                                <th>Comment</th>
                                <td v-html="nl2br(value.comment)"></td>
                            </tr>
                            <tr>
                                <th>Best practice</th>
                                <td>@{{ value.bestPractice }}</td>
                            </tr>
                            <tr v-if="value.plain">
                                <th>Returned value</th>
                                <td><code>@{{ value.plain[0] }}</code></td>
                            </tr>
                        </table>

                        <p>@{{ value.description }}</p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FullReport --}}
    <div :class="{ 'hidden': show.fullReport === null, 'animated zoomIn': show.fullReport, 'animated zoomOut hidden': !show.fullReport }">
        <div class="col-md-12">
            <span @click="newScan()" class="btn btn-default">New scan</span>
            <hr>
            <h2>HTTP Secure Header Checker - Your result</h2>
            <div class="row rating">
                <div class="col-md-2">
                    <span class="label report-label" :class="{ 'label-success': getFirst(fullReport.fullRating) == 'A', 'label-warning': getFirst(fullReport.fullRating) == 'B', 'label-danger': getFirst(fullReport.fullRating) == 'C'}">@{{ fullReport.fullRating }}</span>
                </div>
                <div class="col-md-10">
                    <table class="table table-striped">
                        <tr>
                            <th><nobr>URLs scanned</nobr></th>
                            <td>@{{ fullReport.amountGeneratedReports }} of @{{ fullReport.amountUrlsTotal }}</td>
                        </tr>
                        <tr>
                            <th><nobr>Rated headers:</nobr></th>
                            <td>
                                <ul class="tag-list">
                                    <li class="label label-info" v-for="(value, header) in fullReport.header">@{{ header }}</li>
                                </ul>
                            </td>
                        </tr>

                    </table>
                </div>
            </div>
            <div class="panel panel-default" v-for="(value, header) in fullReport.header">
                <div class="panel-heading" role="tab" :id="'headingMultiple' + header">
                    <h4 class="panel-title">
                        <span class="label label-default" :class="{ 'label-success': getFirst(fullReport.worstHeaderRatings[header]) == 'A', 'label-warning': getFirst(fullReport.worstHeaderRatings[header]) == 'B', 'label-danger': getFirst(fullReport.worstHeaderRatings[header]) == 'C'}">@{{ fullReport.worstHeaderRatings[header] }}</span>
                        <a role="button" data-toggle="collapse" :href="'#collapseMultiple' + header" aria-expanded="false" :aria-controls="'collapseMultiple' + header">
                            @{{ header }}
                        </a>
                    </h4>
                </div>
                <div :id="'collapseMultiple' + header" class="panel-collapse collapse" role="tabpanel" :aria-labelledby="'headingMultiple' + header">
                    <div class="panel-body">

                        <table class="table table-hover">
                            <tr>
                                <th>Rating</th>
                                <th>Url</th>
                            </tr>
                            <tr v-for="entry in value">
                                <td><span class="label label-default" :class="{ 'label-success': getFirst(entry.rating) == 'A', 'label-warning': getFirst(entry.rating) == 'B', 'label-danger': getFirst(entry.rating) == 'C'}">@{{ entry.rating }}</span></td>
                                <td>@{{ entry.url }}</td>
                            </tr>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ mix('/js/start.js') }}"></script>
</body>
</html>
