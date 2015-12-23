inji.Chats = {};
inji.Chats.chats = [];
inji.Chats.get = function (id) {
  var chat = $('#' + id);
  if (chat.data('chats-index') === undefined) {
    inji.Chats.chats.push({
      element: chat,
      timer: null,
      chatId: chat.data('chat-id'),
      msgCount: chat.data('msg-count'),
      perPage: 20,
      lastEventDate: chat.data('last-event-date'),
      members: {},
      checkoutMembers: function () {
        var members = this.members;
        $.each($('[data-chat_member_status'), function () {
          var el = $(this);
          if (members[el.data('chat_member_status')]) {
            if (el.data('ontext')) {
              el.html(el.data('ontext'));
            }
            if (el.data('onclass')) {
              if (el.data('offclass') && el.hasClass(el.data('offclass'))) {
                el.removeClass(el.data('offclass'));
              }
              el.addClass(el.data('onclass'));
            }
          } else {
            if (el.data('offtext')) {
              el.html(el.data('offtext'));
            }
            if (el.data('offclass')) {
              if (el.data('onclass') && el.hasClass(el.data('onclass'))) {
                el.removeClass(el.data('onclass'));
              }
              el.addClass(el.data('offclass'));
            }
          }
        })
      }
    });
    var index = inji.Chats.chats.length - 1
    chat.data('chats-index', index);
    inji.Chats.init(index);
  }
  return chat;
}
inji.Chats.init = function (chatIndex) {
  inji.Chats.chats[chatIndex].timer = setInterval(function () {
    inji.Server.request({
      url: 'chats/events/' + inji.Chats.chats[chatIndex].chatId,
      data: {
        lastEventDate: inji.Chats.chats[chatIndex].lastEventDate
      },
      success: function (data) {
        inji.Chats.chats[chatIndex].members = data.members;
        inji.Chats.chats[chatIndex].checkoutMembers();
        for (key in data.messages) {
          var msg = data.messages[key];
          inji.Chats.chats[chatIndex].lastEventDate = msg.message.chat_message_date_create;
          var template = inji.Chats.chats[chatIndex].element.find('.chats-chat-message-template').html();
          template = template.replace('message-date_create', msg.message.chat_message_date_create);
          template = template.replace('user-firstName', msg.userFirstName);
          template = template.replace('user-fullName', msg.fullUserName);
          template = template.replace('message-text', msg.message.chat_message_text);
          inji.Chats.chats[chatIndex].element.find('.chats-chat-messageList').prepend(template);
        }
        var i = 0;
        var allMsgs = inji.Chats.chats[chatIndex].element.find('.chats-chat-messageList>*');
        while (allMsgs[i]) {
          if (i > 20) {
            $(allMsgs[i]).remove();
          }
          i++;
        }
      }
    });
  }, 3000);
}
inji.Chats.sendForm = function (form, id) {
  var chat = inji.Chats.get(id);
  var formData = new FormData(form);
  inji.Server.request({
    url: '/chats/sendForm/' + chat.data('chat-id'),
    type: 'POST',
    data: formData,
    processData: false,
  });
}