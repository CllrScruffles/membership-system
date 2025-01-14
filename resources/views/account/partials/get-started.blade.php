<div class="row">
    <div class="col-xs-12 col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Helpful hints to get going</h3>
            </div>
            <div class="panel-body">
                <h4>🚀 Membership checklist and quickstart</h4>
                <ul style="list-style-type: none; padding-left: 20px">
                    <li>
                        @if ($user->keyFobs()->count() > 0)✅@else🟠@endif 
                        <b>Get an access method</b> - You can <a href="/account/0/edit#access">set up a fob or access code</a> on your account page. 
                    </li>
                    <li>
                        @if ($user->induction_completed)✅@else🟠@endif 
                        <b>Do the general membership induction</b> - all members need to have read the general <a href="/account/0/induction">member induction</a>
                    </li>
                    <li>
                        💬
                        <b>Join in the chat</b> - we have a <a href="https://list.hacman.org.uk" target="_blank">forum</a> and <a href="https://t.me/hacmanchester" target="_blank">Telegram group chat</a>
                    </li>
                    <li>➡️<b>Find our common resources</b> - we have a page of <a href="/resources">resources</a> outlining the basics</li>
                    <li>➡️<b>Get trained on equipment</b> - Some equipment requires training, check out the <a href="/equipment">equipment</a> page and request inductions.</li>
                    <li>➡️<b>Get a storage location</b> - every member can <a href="/storage_boxes">claim a storage location</a> subject to the rules.</li>
                </ul>

                <h4>Handy links</h4>
                <a href="/resources" class="btn btn-primary">Resources</a>
                <a href="https://list.hacman.org.uk/t/member-handbook/2890/1" class="btn btn-primary">Read the Handbook</a>
                <a href="https://docs.hacman.org.uk" class="btn btn-primary">Documentation</a>
            </div>
        </div>
    </div>
</div>