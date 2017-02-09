<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HTTP Secure Header Scanner</title>
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
</head>
<body>

<div class="container" id="app">
    @if($errors->any())
        @foreach($errors->all() as $error)
            <div class="alert alert-danger animated fadeInDown" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Error!</strong> {{ $error }}
            </div>
        @endforeach
    @endif

    <div class="vertical-center full-height" v-show="show.load">
        <div class="sk-folding-cube">
            <div class="sk-cube1 sk-cube"></div>
            <div class="sk-cube2 sk-cube"></div>
            <div class="sk-cube4 sk-cube"></div>
            <div class="sk-cube3 sk-cube"></div>
        </div>
    </div>

    <div v-show="show.report" class="animated zoomIn">

        <div class="row">
            <div class="col-md-push-2 col-md-8">
                <h2>Report for: {{ $url }}</h2>
                <div class="row">
                    <div class="col-md-2"><span class="label label-success report-label">B</span></div>
                    <div class="col-md-10">

                        <h4>This site is secure.</h4>

                        @{{ amountUrlsChecked  }} URLs checked
                    </div>
                </div>
            </div>
        </div>
        <div class="row">

        </div>
        <div class="row">
            <div class="col-md-8 col-md-push-2">
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    <ReportAccordionPanel :fullreport="fullreport" :rating="headerRatings" header="Content-Security-Policy"></ReportAccordionPanel>
                    <ReportAccordionPanel :fullreport="fullreport" :rating="headerRatings" header="Content-Type"></ReportAccordionPanel>
                    <ReportAccordionPanel :fullreport="fullreport" :rating="headerRatings" header="Public-Key-Pins"></ReportAccordionPanel>
                    <ReportAccordionPanel :fullreport="fullreport" :rating="headerRatings" header="Strict-Transport-Security"></ReportAccordionPanel>
                    <ReportAccordionPanel :fullreport="fullreport" :rating="headerRatings" header="X-Content-Type-Options"></ReportAccordionPanel>
                    <ReportAccordionPanel :fullreport="fullreport" :rating="headerRatings" header="X-Frame-Options"></ReportAccordionPanel>
                    <ReportAccordionPanel :fullreport="fullreport" :rating="headerRatings" header="X-Xss-Protection"></ReportAccordionPanel>

                </div>
            </div>
        </div>

    </div>

</div>
<script src="{{ mix('/js/report.js') }}"></script>
</body>
</html>
