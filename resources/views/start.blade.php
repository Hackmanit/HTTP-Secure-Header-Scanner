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
                    <h3>@{{ message }}</h3>
                    <div class="row">
                        <div class="col-md-11">
                            <input class="form-control" v-model="url">
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-primary form-control" v-on:click="getReport">SCAN!</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <ul>
                        <li v-for="error in errors">
                            @{{ error }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                        <div class="col-md-12">
                            <div class="panel panel-default" v-for="report in reports">
                                <div class="panel-heading" role="tab" data-toggle="collapse" data-parent="#accordion" :href="'#' + report.nonce" aria-expanded="true" :aria-controls="report.nonce">
                                      <h2 class="panel-title">
                                          Report: <a :href="report.url" target="_blank">@{{ report.url }}</a> at @{{ report.date }}
                                          <i class="indicator glyphicon glyphicon-chevron-down  pull-right"></i>
                                      </h2>
                                </div>
                                <div :id="report.nonce" class="panel-collapse collapse in" role="tabpanel">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Security Headers and Score</h4>

                                                <table class="table table-striped">
                                                    <tr v-for="score in report.scores">
                                                        <td class="col-md-2">
                                                            @{{ score[0].name }}
                                                        </td>
                                                        <td class="col-md-10">
                                                            <div class="row" v-for="entry in score">
                                                                <div class="col-md-12">
                                                                    @{{ entry.description }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Raw headers</h4>

                                                <table class="table table-striped">
                                                    <tr v-for="(headerValue, headerIndex) in report.headers">
                                                        <td>
                                                            @{{ headerIndex }}
                                                        </td>
                                                        <td class="col-md-10">
                                                            <div class="row" v-for="value in headerValue">
                                                                <div class="col-md-12">
                                                                    @{{ value }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <a :href="report.url" download>Download</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ elixir('js/app.js') }}"></script>
    </body>
</html>
