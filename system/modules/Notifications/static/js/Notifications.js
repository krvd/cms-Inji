/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
inji.Notifications = {
  showNotification: function (title, text, action) {
    var notification = new Notification(title, {
      icon: '/static/system/images/logo-dark.png',
      body: text,
    });
    if (action) {
      notification.onclick = action;
    }
  }
}
inji.onLoad(function () {
  setInterval(function () {
    inji.Server.request({
      url: '/notifications/check',
      success: function (result) {
        for (var key in result) {
          inji.Notifications.showNotification(result[key].notification_name, result[key].notification_text);
        }
      }
    });
  }, 30 * 1000);
})