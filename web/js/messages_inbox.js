var MessagesInbox = {
    loadInbox: function(){
        MessageActions.getInbox(function(messages)
        {
            html = " <ul class='collection'>";
            
            $.each(messages, function(key, val)
            {
                html += "<li class='collection-item avatar enso-main-color-text' style='cursor:pointer' onclick='MessageModal.show(false, false, " + val['idMessages'] + ")'>\
                            <i class='material-icons circle'>mail</i>\
                            <span class='title'>Partilha de credencial \"" + val['title'] + "\"</span>\
                            <p>Partilhada por " + val['senderId'] + "\
                            </p>\
                        </li>";
            });
            
            html += "</ul>";
            
            $("#inbox").append(html);
            
        });
        
        
    }
};

LocalizationManager.applyLocaleSettings();
$(".tooltipped").tooltip();
MessagesInbox.loadInbox();
