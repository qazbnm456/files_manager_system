    var deleter = {

        linkSelector          : "a[data-delete], input[data-delete]",
        modalTitle            : "Are you sure?",
        modalMessage          : "You will not be able to recover this entry?",
        modalConfirmButtonText: "刪除",
        laravelToken          : null,
        url                   : "/",

        init: function() {
            $(this.linkSelector).on('click', {self:this}, this.handleClick);
        },

        handleClick: function(event) {
            event.preventDefault();
            
            var self = event.data.self;
            var link = $(this);

            self.modalTitle             = link.data('title') || self.modalTitle;
            self.modalMessage           = link.data('message') || self.modalMessage;
            self.modalConfirmButtonText = link.data('button-text') || self.modalConfirmButtonText;
            self.url                    = link.attr('href');
            self.laravelToken           = $("meta[name=token]").attr('content');

            self.confirmDelete();
        },

        confirmDelete: function() {
            swal({
                    title             : this.modalTitle,
                    text              : this.modalMessage,
                    type              : "warning",
                    showCancelButton  : true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText : this.modalConfirmButtonText,
                    cancelButtonText  : "取消",
                    closeOnConfirm    : false,
                    showLoaderOnConfirm: true,
                },
                function() {
                    var self = this;
                    setTimeout(function(){
                        self.makeDeleteRequest(function(output){
                            swal({
                                title: "處理完畢！",
                                text: output,
                                type: "success",
                                html: true
                            }, function(){
                                location.reload()
                            }, this.url);
                        });       
                    }, 1500);
                }.bind(this)
            );
        },

        makeDeleteRequest: function(handleData, url) {
            /*var form =
                $('<form>', {
                    'method': 'POST',
                    'action': this.url
                });

            var token =
                $('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': this.laravelToken
                });

            var hiddenInput =
                $('<input>', {
                    'name': '_method',
                    'type': 'hidden',
                    'value': 'DELETE'
                });

            return form.append(token, hiddenInput).appendTo('body').submit();
            return $('#bulk_submit').clone().attr({
                "name": "",
                "action": "?p=bulk_action"
            }).submit();*/
            if(this.url == undefined)
                return $.post("upload.php?p=bulk_action", $('#bulk_submit').clone().attr({
                    "name": "",
                    "action": "?p=bulk_action"
                }).serialize())
                .done(function(data) {
                    handleData(data);
                });
            else
                return $.get(this.url)
                .done(function(data) {
                    handleData(data);
                });
        },

        Destroy: function() {
            $(this.linkSelector).off('click');
        }
    };