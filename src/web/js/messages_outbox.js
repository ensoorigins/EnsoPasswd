var MessagesOutbox = {
    loadOutbox: function(){
        MessageActions.getOutbox(function(messages)
        {            
            var html = " <ul class='collection'>";
            
            $.each(messages, function(key, val)
            {
                html += "<li class='collection-item avatar enso-main-color-text' style='cursor:pointer' onclick='MessageModal.show(" + (val['receiverId'] === "External" ? "true" : "false") + ", true, " + val['idMessages'] + ")'>\
                            <i class='material-icons circle'>mail</i>\
                            <span class='title'>Partilha de credencial \"" + val['title'] + "\"</span>\
                            <p>Partilhada para " + val['receiverId'] + "\
                            </p>\
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