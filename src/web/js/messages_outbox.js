var MessagesOutbox = {
    loadOutbox: function(){
        MessageActions.getOutbox(function(messages)
        {
            console.log(messages);            
            var html = " <ul class='collection'>";
            
            $.each(messages, function(key, val)
            {
                html += "<li class='collection-item avatar enso-main-color-text' style='cursor:pointer' onclick='MessageModal.show(" + (val['externalKey'] != undefined ?
                            "true, true, \"" + val['idExternalMessage'] + "\"" :
                            "false, true, \"" + val['idMessages'] + "\"") + ")'>\
                            <i class='material-icons circle'>mail</i>\
                            <span class='title'>Partilha de credencial \"" + val['title'] + "\"</span>" + 
                            (val['receiverId'] != undefined ? "<p>Partilhada para " + val['receiverId'] + "</p>" :  "<p>Mensagem externa</p>") + "\
                        </li>";
            });
            
            html += "</ul>";
            
            $("#outbox").append(html);
            
        });
        
        
    }
};

LocalizationManager.applyLocaleSettings();
$(".tooltipped").tooltip();
MessagesOutbox.loadOutbox();

//# sourceURL=messages_outbox.js