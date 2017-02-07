<template>
    <div id="panelRoot">
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" :id="'heading' + header">
                <h4 class="panel-title">
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

                        <tr v-for='site in fullreport[ header ]'>
                            <td>{{ site.rating }}</td>
                            <td>{{ site.url }}</td>
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
        props: [ 'header', 'fullreport' ],

        data() {
            return {
                random: "string",
                randomLink: "#" + this.random,
            }
        }
    }
</script>