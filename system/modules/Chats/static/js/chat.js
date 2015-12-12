inji.Chats = {};
inji.Chats.chats = [];
inji.Chats.get = function (id) {
  var chat = $('#' + id);
  if (!chat.data('chats-index')) {
    inji.Chats.chats.push({
      element: chat,
      timer: null,
      chatId: chat.data('chat-id'),
      msgCount: chat.data('msg-count'),
      perPage: 20,
      lastEventDate: chat.data('last-event-date')
    });
    var index = inji.Chats.chats.length - 1
    chat.data('chats-index', index);
    inji.Chats.init(index);
  }
}
inji.Chats.init = function (chatIndex) {
  inji.Chats.chats[chatIndex].timer = setInterval(function () {
    inji.Server.request({
      url: 'chats/events/' + inji.Chats.chats[chatIndex].chatId,
      data: {
        lastEventDate: inji.Chats.chats[chatIndex].lastEventDate
      },
      success: function (data) {
        console.log(data)
      }
    });
  }, 3000);
}