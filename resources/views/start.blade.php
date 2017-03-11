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
                    <h3>Menu 2</h3>
                    <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.</p>
                </div>
            </div>

            {{-- <div class="row">
                 <div class="col-md-3">
                     <div class="checkbox">
                     <textarea v-model="formRequest.whitelist" name="whitelist" rows="6" placeholder="sub1.example.com
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
                                 <input type="checkbox" name="scan[]" value="anchor" v-model="formRequest.scan.anchor">
                                 Include <b>a</b>-Tags
                             </label>
                             <br>
                             <label>
                                 <input type="checkbox" name="scan[]" value="image" v-model="formRequest.scan.image">
                                 Include <b>img</b>-Tags
                             </label>
                             <br>
                             <label>
                                 <input type="checkbox" name="scan[]" value="script" v-model="formRequest.scan.script">
                                 Include <b>script</b>-Tags
                             </label>
                             <br>
                             <label>
                                 <input type="checkbox" name="scan[]" value="link" v-model="formRequest.scan.link">
                                 Include <b>link</b>-Tags
                             </label>
                             <br>
                             <label>
                                 <input type="checkbox" name="scan[]" value="media" v-model="formRequest.scan.media">
                                 Include <b>audio</b>- and <b>video</b>-Tags
                             </label>
                             <br>
                             <label>
                                 <input type="checkbox" name="scan[]" value="area" v-model="formRequest.scan.area">
                                 Include <b>area</b>-Tags
                             </label>
                             <br>
                             <label>
                                 <input type="checkbox" name="scan[]" value="frame" v-model="formRequest.scan.frame">
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
                             <input type="checkbox" name="doNotCrawl" v-model="formRequest.doNotCrawl">
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
             </div>--}}
            {{--<div class="row">

            </div>--}}
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
