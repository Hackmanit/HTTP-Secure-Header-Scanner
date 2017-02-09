<template>
    <div id="panelRoot">
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" :id="'heading' + header">
                <h4 class="panel-title">
                    <!-- TODO: label styling in relation to the rating -->
                    <span class="label label-default">{{ rating[header] }}</span>
                    <a role="button" data-toggle="collapse" data-parent="#accordion" :href="'#collapse' + header" aria-expanded="false" :aria-controls="'collapse' + header">
                        {{ header }}
                    </a>
                </h4>
            </div>
            <div :id="'collapse' + header" class="panel-collapse collapse" role="tabpanel" :aria-labelledby="'heading' + header">
                <div class="panel-body">

                    <table class="table table-hover">
                        <tr>
                            <th>Rating</th>
                            <th>Site</th>
                        </tr>
                        <!-- TODO: label styling in relation to the rating -->
                        <tr v-for='site in fullreport[ header ]'>
                            <td><span class="label label-default">{{ site.rating }}</span></td>
                            <td><a :href="'/single?url=' + site.url">{{ site.url }}</a></td>
                        </tr>
                    </table>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'panelRoot',
        props: [ 'header', 'fullreport', 'rating' ],
    }
</script>